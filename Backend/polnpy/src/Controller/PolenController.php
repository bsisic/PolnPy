<?php
namespace App\Controller;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Document\PolenRecord;
use App\Document\PolenDocument;
use App\Loader\DataLoader;

class PolenController
{   
    private $registry;
    
    public function __construct(ManagerRegistry $manager)
    {
        $this->registry = $manager;
    }
    
    public function insertData(Request $request)
    {
        $dataLoader = new DataLoader($this->registry);
        
        try {
            $dataLoader->insertData(json_decode($request->getContent(), true));
        } catch (\Exception $e) {
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
            $result[] = [
                'id' => $polen->getId(),
                'name' => $polen->getName(),
                'isPredictive' => $polen->getPredictive()
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
        
        /**
         * @var PolenRecord $record
         */
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

