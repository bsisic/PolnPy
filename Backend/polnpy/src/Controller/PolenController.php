<?php
namespace App\Controller;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Document\PolenRecord;
use App\Document\PolenDocument;
use App\Loader\DataLoader;
use Monolog\Logger;
use App\Response\CrossJsonResponse;
use Swagger\Annotations as SWG;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use App\Purger\CachePurger;

class PolenController
{
    private $registry;
    
    /**
     * @var Logger
     */
    private $logger;
    
    private $cache;
    
    private $purger;
    
    public function __construct(ManagerRegistry $manager, Logger $logger, AdapterInterface $cache, CachePurger $purger)
    {
        $this->registry = $manager;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->purger = $purger;
    }
 
    /**
     * @SWG\Post(
     *  summary="Update a pollen definition",
     *  description="Will update a set of pollen. The given body must follow the format : [{pollen:name, warning: 10, alert: 20[, prediction: true]}, ...]",
     *  produces={"application/json"},
     *  @SWG\Response(
     *      response=200,
     *      description="Returns the updated elements"
     *  ),
     *  @SWG\Parameter(
     *      required=true,
     *      name="pollen",
     *      in="query",
     *      type="string",
     *      description="The pollen name what should be updated"
     *  ),
     *  @SWG\Parameter(
     *      required=false,
     *      name="warning",
     *      in="query",
     *      type="integer",
     *      description="The pollen warning level"
     *  ),
     *  @SWG\Parameter(
     *      required=false,
     *      name="alert",
     *      in="query",
     *      type="integer",
     *      description="The pollen alert level"
     *  ),
     *  @SWG\Parameter(
     *      required=false,
     *      name="prediction",
     *      in="query",
     *      type="boolean",
     *      description="The pollen prediction capability"
     *  ),
     *  @SWG\Parameter(
     *      required=false,
     *      name="image",
     *      in="query",
     *      type="integer",
     *      description="The pollen image url"
     *  ),
     *  @SWG\Parameter(
     *      required=false,
     *      name="predicate_args",
     *      in="query",
     *      type="array",
     *      items={"string"},
     *      description="The pollen image url"
     *  )
     * )
     * @return \App\Response\CrossJsonResponse
     */
    public function updateLevel(Request $request)
    {
        $this->purger->purge();

        $this->logger->debug('Starting update level');
        
        $datas = json_decode($request->getContent(), true);
        if (!$datas) {
            return new CrossJsonResponse(
                [
                    'message' => 'The data must follow the json format : [{"pollen":"name", "warning": 10, "alert": 20[, "prediction": true]}, ...]'
                ],
                400
            );
        }
        
        $result = [];
        foreach ($datas as $data) {
            $polen = $this->registry->getRepository(PolenDocument::class)->findOneByName($data['pollen']);
            
            if (!$polen) {
                return new CrossJsonResponse(
                    [
                        'message' => sprintf('Pollen "%s" not found', $data['pollen'])
                    ],
                    404
                );
            }
            
            if (array_key_exists('warning', $data)) {
                $polen->setWarning((int)$data['warning']);
            }
            if (array_key_exists('alert', $data)) {
                $polen->setWarning((int)$data['alert']);
            }
            if (array_key_exists('prediction', $data)) {
                $polen->setPredictive((bool)$data['prediction']);
            }
            if (array_key_exists('image', $data)) {
                $polen->setImageUrl((string)$data['image']);
            }
            if (array_key_exists('predicate_args', $data)) {
                $polen->setPredictionArguments($data['predicate_args']);
            }
            
            $result[] = [
                'id' => $polen->getId(),
                'name' => $polen->getName(),
                'isPredictive' => $polen->getPredictive(),
                'warning' => $polen->getWarning(),
                'alert' => $polen->getAlert(),
                'image' => $polen->getImageUrl(),
                'predicate_args' => $polen->getPredictionArguments()
            ];
        }
        
        $this->registry->getManager()->flush();
        
        return new CrossJsonResponse($result, 200);
    }
    
    /**
     * @SWG\Post(
     *  summary="Insert data",
     *  produces={"application/json"},
     *  @SWG\Response(
     *      response=200,
     *      description="Insert a set of data in format {pollens:[{type:"", data_points:[{date:YYYY-MM-DD, value: 0}, ...]}, ...]}"
     *  )
     * )
     * @return \App\Response\CrossJsonResponse
     */
    public function insertData(Request $request)
    {
        $this->purger->purge();

        $dataLoader = new DataLoader($this->registry, $this->logger);
        
        try {
            $this->logger->debug('Starting insert process');
            $dataLoader->insertData(json_decode($request->getContent(), true));
        } catch (\Exception $e) {
            $this->logger->debug('Process failed', ['error' => $e]);
            return new CrossJsonResponse(
                [
                    'message' => $e->getMessage()
                ],
                $e->getCode()
            );
        }
        
        return new CrossJsonResponse(
            [
                'message' => 'success'
            ],
            200
        );
    }
    
    /**
     * @SWG\Get(
     *  summary="Get each pollen informations",
     *  produces={"application/json"},
     *  @SWG\Response(
     *      response=200,
     *      description="Returns the set of pollen information, with min and max dates of historical data"
     *  ),
     *  @SWG\Parameter(
     *      required=false,
     *      name="predicate",
     *      in="query",
     *      type="boolean",
     *      description="Only return enabled predication"
     *  )
     * )
     * @return \App\Response\CrossJsonResponse
     */
    public function listPolens(Request $request)
    {
        $withPredicate = $request->query->has('predicate') && (bool)$request->query->get('predicate');
        $cacheKey = md5('polen.list' . ($withPredicate ? '.predicate' : ''));
        $cache = $this->cache->getItem($cacheKey);

        if ($cache->isHit()) {
            return new CrossJsonResponse($cache->get());
        }
        
        $keyStore = $this->cache->getItem('key_store');
        $data = $keyStore->get();
        if (!is_array($data)) {
            $data = [];
        }
        $data[] = $cacheKey;
        $keyStore->set($data);
        $keyStore->expiresAfter(86400);
        $this->cache->save($keyStore);
        
        $polenList = $this->registry->getRepository(PolenDocument::class)->findAll();
        $infos = $this->registry->getRepository(PolenRecord::class)->findInfoForPolen();

        $result = [];
        foreach ($polenList as $polen) {
            if ($request->query->has('predicate') && (bool)$request->query->get('predicate') && !$polen->getPredictive()) {
                continue;
            }
            $range = [];
            $history = false;
            
            if (isset($infos[$polen->getId()])) {
                $range = [
                    'min' => $infos[$polen->getId()]['min']->toDateTime(),
                    'max' => $infos[$polen->getId()]['max']->toDateTime(),
                    'max-concentration' => $infos[$polen->getId()]['max-concentration'],
                    'min-concentration' => $infos[$polen->getId()]['min-concentration']
                ];
                $history = true;
            }
            
            $result[] = [
                'id' => $polen->getId(),
                'name' => $polen->getName(),
                'isPredictive' => $polen->getPredictive(),
                'range' => $range,
                'history' => $history,
                'warning' => $polen->getWarning(),
                'alert' => $polen->getAlert(),
                'image' => $polen->getImageUrl()
            ];
        }
        
        $cache->set($result);
        $cache->expiresAfter(3600);
        $this->cache->save($cache);
        
        return new CrossJsonResponse($result, 200);
    }
    
    protected function resolveDate($date)
    {
        if (!$date) {
            return null;
        }
        
        return \DateTime::createFromFormat('Y-m-d', $date);
    }
}

