<?php
namespace App\Loader;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use App\Document\PolenDocument;
use App\Document\PolenRecord;

class DataLoader
{
    private $registry;
    
    public function __construct(ManagerRegistry $manager)
    {
        $this->registry = $manager;
    }
    
    public function insertData($data)
    {
        if (!$data || !array_key_exists('pollens', $data)) {
            throw new \Exception('"pollens" is required', 400);
        }
        
        $datas = $data['pollens'];
        
        $polenList = $this->registry->getRepository(PolenDocument::class)->findAll();
        foreach ($polenList as $polen) {
            $polenList[$polen->getName()] = $polen;
        }
        
        foreach ($datas as $element) {
            if (!array_key_exists('type', $element)) {
                throw new \Exception('type key is required inside data', 400);
            }
            $polenName = $element['type'];
            if (!isset($polenList[$polenName])) {
                $polenDocument = new PolenDocument();
                $polenDocument->setName($polenName);
                
                $this->registry->getManager()->persist($polenDocument);
                $polenList[$polenName] = $polenDocument;
            }
            
            if (!array_key_exists('data_points', $element)) {
                throw new \Exception('data_points key is required inside data', 400);
            }
            foreach ($element['data_points'] as $dataPoint) {
                if (!array_key_exists('value', $dataPoint)) {
                    throw new \Exception('value key is required inside data', 400);
                }
                if (!array_key_exists('date', $dataPoint)) {
                    throw new \Exception('date key is required inside data', 400);
                }
                
                $polenRecord = new PolenRecord();
                $polenRecord->setPolen($polenList[$polenName]);
                $polenRecord->setConcentration($dataPoint['value']);
                $polenRecord->setRecordDate(\DateTime::createFromFormat('Y-m-d', $dataPoint['date']));
                
                $this->registry->getManager()->persist($polenRecord);
            }
        }
        
        $this->registry->getManager()->flush();
    }
}

