<?php
namespace App\Controller;

use Symfony\Component\Process\Process;
use App\Response\CrossJsonResponse;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use App\Document\PolenDocument;
use Monolog\Logger;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class PredicateController
{
    private $registry;
    
    /**
     * @var Logger
     */
    private $logger;
    
    private $cache;
    
    public function __construct(ManagerRegistry $manager, Logger $logger, AdapterInterface $cache)
    {
        $this->registry = $manager;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * @SWG\Get(
     *  summary="Get prediction for pollen concentration",
     *  produces={"application/json"},
     *  @SWG\Response(
     *      response=200,
     *      description="Returns a prediction for pollen concentration"
     *  ),
     *  @SWG\Parameter(
     *      required=true,
     *      name="pollen",
     *      in="query",
     *      type="string",
     *      description="The pollen ID to predict"
     *  )
     * )
     * @return \App\Response\CrossJsonResponse
     */
    public function predict(Request $request)
    {
        
        $pollenId = $request->query->get('pollen', null);
        if (!$pollenId) {
            return new CrossJsonResponse(
                ['message' => 'pollen parameter is mandatory']
            );
        }
        
        $cacheKey = md5($pollenId);
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
        
        /**
         * @var PolenDocument $pollen
         */
        $pollen = $this->registry->getManager()->getRepository(PolenDocument::class)->find($pollenId);
        
        if (!$pollen) {
            return new CrossJsonResponse(
                ['message' => sprintf('Pollen "%s" does not exist', $pollenId)]
            );
        }
        if (!$pollen->getPredictive()) {
            return new CrossJsonResponse(
                ['message' => sprintf('Pollen "%s" is not predictible', $pollenId)]
            );
        }
        
        chdir(dirname($pollen->getPredictionArguments()[1]));
        
        $process = new Process($pollen->getPredictionArguments());
        
        $process->run();
        
        if (!$process->isSuccessful()) {
            $this->logger->error('Error during prediction', ['commandLine' => $process->getCommandLine(), 'error' => $process->getErrorOutput()]);
            return new CrossJsonResponse(['message' => 'Error occured'], 500);
        }
        
        $lines = explode("\n", $process->getOutput());
        
        $this->logger->debug('Prediction done', [$lines]);
        
        while (count($lines) > 0 && !is_numeric($result = array_pop($lines)));
        
        $result = [
            'pollen_id' => $pollen->getId(),
            'pollen' => $pollen->getName(),
            'concentration' => (float)$result
        ];
        
        $cache->set($result);
        $cache->expiresAfter(3600);
        $this->cache->save($cache);
        
        return new CrossJsonResponse($result);
    }
}

