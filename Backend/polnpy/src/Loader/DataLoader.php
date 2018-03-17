<?php
namespace App\Loader;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use App\Document\PolenDocument;
use App\Document\PolenRecord;
use Monolog\Logger;

class DataLoader
{
    private $registry;
    
    private $logger;
    
    public function __construct(ManagerRegistry $manager, Logger $logger)
    {
        $this->registry = $manager;
        $this->logger = $logger;
    }
    
    public function insertData($data)
    {
        if (!$data || !array_key_exists('pollens', $data)) {
            throw new \Exception('"pollens" is required', 400);
        }
        
        $datas = $data['pollens'];
        
        $this->logger->debug('Building polen list');
        $polenList = $this->registry->getRepository(PolenDocument::class)->findAll();
        foreach ($polenList as $polen) {
            $polenList[$polen->getName()] = $polen;
        }
        $this->logger->debug('Builded polen list');
        
        foreach ($datas as $key => $element) {
            $this->logger->debug('Iterating data', ['key' => $key]);
            if (!array_key_exists('type', $element)) {
                $this->logger->debug('type key is required inside data', ['key' => $key]);
                throw new \Exception('type key is required inside data', 400);
            }
            $polenName = $element['type'];
            if (!isset($polenList[$polenName])) {
                $polenDocument = new PolenDocument();
                $polenDocument->setName($polenName);
                
                $this->logger->debug('Persisting new document', ['key' => $key]);
                $this->registry->getManager()->persist($polenDocument);
                $polenList[$polenName] = $polenDocument;
            }
            
            if (!array_key_exists('data_points', $element)) {
                $this->logger->debug('data_points key is required inside data', ['key' => $key]);
                throw new \Exception('data_points key is required inside data', 400);
            }
            foreach ($element['data_points'] as $dataPointKey => $dataPoint) {
                if (!array_key_exists('value', $dataPoint)) {
                    $this->logger->debug('value key is required inside data', ['key' => $key, 'datapoint' => $dataPointKey]);
                    throw new \Exception('value key is required inside data', 400);
                }
                if (!array_key_exists('date', $dataPoint)) {
                    $this->logger->debug('date key is required inside data', ['key' => $key, 'datapoint' => $dataPointKey]);
                    throw new \Exception('date key is required inside data', 400);
                }
                
                $recordDate = \DateTime::createFromFormat('Y-m-d', $dataPoint['date']);
                $this->logger->debug('Loading record');
                
                $polenRecord = new PolenRecord();
                $polenRecord->setPolen($polenList[$polenName]);
                $polenRecord->setConcentration($dataPoint['value']);
                $polenRecord->setRecordDate($recordDate);
                
                $this->logger->debug('Persisting new record', ['key' => $key, 'datapoint' => $dataPointKey]);
                $this->registry->getManager()->persist($polenRecord);
            }
        }
        
        $this->logger->debug('Flush', ['key' => $key]);
        $this->registry->getManager()->flush();
    }
}

