<?php
namespace App\Controller;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Document\PolenRecord;
use App\Document\PolenDocument;
use App\Loader\DataLoader;
use Monolog\Logger;
use App\Response\CrossJsonResponse;

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
        }
        
        $this->registry->getManager()->flush();
        
        return new CrossJsonResponse(
            [
                'message' => 'success'
            ],
            200
        );
    }
    
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

