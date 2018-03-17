<?php
namespace App\Controller;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Document\PolenRecord;
use App\Document\PolenDocument;
use App\Loader\DataLoader;
use Monolog\Logger;

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
    
    public function updateLevel(Request $request)
    {
        $this->logger->debug('Starting update level');
        
        $datas = json_decode($request->getContent(), true);
        if (!$datas) {
            return new JsonResponse(
                [
                    'message' => 'The data must follow the json format : [{"pollen":"name", "warning": 10, "alert": 20[, "prediction": true]}, ...]'
                ],
                400
            );
        }
        
        foreach ($datas as $data) {
            if (
                !array_key_exists('pollen', $data) ||
                !array_key_exists('warning', $data) ||
                !array_key_exists('alert', $data)
            ) {
                return new JsonResponse(
                    [
                        'message' => 'The data must follow the format : [{"pollen":"name", "warning": 10, "alert": 20[, "prediction": true]}, ...]'
                    ],
                    400
                );
            }
            
            $polen = $this->registry->getRepository(PolenDocument::class)->findOneByName($data['pollen']);
            
            if (!$polen) {
                return new JsonResponse(
                    [
                        'message' => sprintf('Pollen "%s" not found', $data['pollen'])
                    ],
                    404
                );
            }
            
            $polen->setWarning((int)$data['warning']);
            $polen->setAlert((int)$data['alert']);
            
            if (array_key_exists('prediction', $data)) {
                $polen->setPredictive((bool)$data['prediction']);
            }
        }
        
        $this->registry->getManager()->flush();
        
        return new JsonResponse(
            [
                'message' => 'success'
            ]
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
            return new JsonResponse(
                [
                    'message' => $e->getMessage()
                ],
                $e->getCode()
            );
        }
        
        return new JsonResponse(
            [
                'message' => 'success'
            ]
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
                'alert' => $polen->getAlert()
            ];
        }
        
        return new JsonResponse($result);
    }
    
    public function history(Request $request)
    {
        if (!$request->query->has('type')) {
            return new JsonResponse(
                [
                    'message' => 'Type is required'
                ],
                400
            );
        }
        
        $type = $request->query->get('type');
        $polen = $this->registry->getRepository(PolenDocument::class)->find($type);

        if (!$polen) {
            return new JsonResponse(
                [
                    'message' => 'Polen not found'
                ],
                404
            );
        }

        try {
            $start = $this->resolveDate($request->query->get('start', null));
            $end = $this->resolveDate($request->query->get('end', null));
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'message' => 'Incorrect date format (YYYY-mm-dd)'
                ],
                400
            );
        }
        
        $records = $this->registry->getRepository(PolenRecord::class)->findByPolenAndRange(
            $polen,
            $start,
            $end
        );
        $results = [];
        
        foreach ($records as $record) {
            $results[] = [
                'id' => $record->getId(),
                'concentration' => $record->getConcentration(),
                'date' => $record->getRecordDate()
            ];
        }
        
        return new JsonResponse($results);
    }
    
    protected function resolveDate($date)
    {
        if (!$date) {
            return null;
        }
        
        return \DateTime::createFromFormat('Y-m-d', $date);
    }
}

