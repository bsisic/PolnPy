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

class PolenController
{
    private $registry;
    
    /**
     * @var Logger
     */
    private $logger;
    
    public function __construct(ManagerRegistry $manager, Logger $logger)
    {
        $this->registry = $manager;
        $this->logger = $logger;
    }
    
    /**
     * @SWG\Get(
     *  summary="Get each pollen concentration in a day",
     *  produces={"application/json"},
     *  @SWG\Response(
     *      response=200,
     *      description="Returns the set of pollen concentration for specified date"
     *  ),
     *  @SWG\Parameter(
     *      required=true,
     *      name="date",
     *      in="query",
     *      type="string",
     *      description="The data starting date in format YYYY-MM-DD"
     *  )
     * )
     * @return \App\Response\CrossJsonResponse
     */
    public function dateOverview(Request $request)
    {
        $date = $request->query->get('date', date('Y-m-d'));
        $startDateTime = \DateTime::createFromFormat('Y-m-d', $date);
        $startDateTime->setTime(0, 0, 0, 0);
        
        $endDateTime = \DateTime::createFromFormat('Y-m-d', $date);
        $endDateTime->setTime(23, 59, 59, 999);
        
        $records = $this->registry->getManager()->getRepository(PolenRecord::class)->findInRange($startDateTime, $endDateTime);
        
        $results = [];
        foreach ($records as $record) {
            $results[] = [
                'concentration' => $record->getConcentration(),
                'polen' => $record->getPolen()->getName(),
                'polenId' => $record->getPolen()->getId()
            ];
        }
        
        return new CrossJsonResponse($results, 200);
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
     *  )
     * )
     * @return \App\Response\CrossJsonResponse
     */
    public function updateLevel(Request $request)
    {
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
            
            $result[] = [
                'id' => $polen->getId(),
                'name' => $polen->getName(),
                'isPredictive' => $polen->getPredictive(),
                'warning' => $polen->getWarning(),
                'alert' => $polen->getAlert(),
                'image' => $polen->getImageUrl()
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
     *  )
     * )
     * @return \App\Response\CrossJsonResponse
     */
    public function listPolens()
    {
        $polenList = $this->registry->getRepository(PolenDocument::class)->findAll();
        
        $result = [];
        foreach ($polenList as $polen) {
            $info = $this->registry->getRepository(PolenRecord::class)->findInfoForPolen($polen);
            $range = [];
            $history = false;
            
            if ($info) {
                $range = [
                    'min' => $info['max']->toDateTime(),
                    'max' => $info['max']->toDateTime()
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

