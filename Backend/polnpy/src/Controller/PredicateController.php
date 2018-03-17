<?php
namespace App\Controller;

use Symfony\Component\Process\Process;
use App\Response\CrossJsonResponse;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use App\Document\PolenDocument;
use Monolog\Logger;

class PredicateController
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
        
        $process = new Process($pollen->getPredictionArguments());
        
        $process->run();
        
        if (!$process->isSuccessful()) {
            $this->logger->error('Error during prediction', ['commandLine' => $process->getCommandLine(), 'error' => $process->getErrorOutput()]);
            return new CrossJsonResponse(['message' => 'Error occured'], 500);
        }
        
        return new CrossJsonResponse(
            [
                'pollen_id' => $pollen->getId(),
                'pollen' => $pollen->getName(),
                'concentration' => (float)$process->getOutput()
            ]
        );
    }
}

