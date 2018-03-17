<?php
namespace App\Controller;

use App\Document\PolenRecord;
use App\Document\PolenDocument;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Monolog\Logger;
use App\Response\CrossJsonResponse;

class HistoryController
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
    
    public function history(Request $request)
    {
        if (!$request->query->has('type')) {
            return new CrossJsonResponse(
                [
                    'message' => 'Type is required'
                ],
                400
            );
        }
        
        $type = $request->query->get('type');
        $polen = $this->registry->getRepository(PolenDocument::class)->find($type);
        
        if (!$polen) {
            return new CrossJsonResponse(
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
            return new CrossJsonResponse(
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
        
        return new CrossJsonResponse($results);
    }
    
    protected function resolveDate($date)
    {
        if (!$date) {
            return null;
        }
        
        return \DateTime::createFromFormat('Y-m-d', $date);
    }
}

