<?php
namespace App\Repositories;

use Doctrine\ODM\MongoDB\DocumentRepository;
use App\Document\PolenDocument;
use App\Document\PolenRecord;

class PolenRecordRepository extends DocumentRepository
{
    public function findInRange($start, $end)
    {
        $qb = $this->createQueryBuilder(PolenRecord::class);
        $qb->field('polen')->prime(true);
        $qb->field('recordDate')->gte($start);
        $qb->field('recordDate')->lte($end);
        
        return $qb->getQuery()->execute();
    }
    
    public function findByPolenAndRange(PolenDocument $polen, $start, $end)
    {
        $qb = $this->createQueryBuilder(PolenRecord::class)
            ->field('polen')->equals($polen);
        
        if ($start) {
            $qb->field('recordDate')->gt($start);
        }
        if ($end) {
            $qb->field('recordDate')->lt($end);
        }
        
        return $qb->getQuery()->execute();
    }
    
    public function findInfoForPolen()
    {
        $qb = $this->createAggregationBuilder()
            ->group()
                ->field('_id')->expression('$polen')
                ->field('max')->max('$recordDate')
                ->field('min')->min('$recordDate')
                ->field('max-concentration')->max('$concentration')
                ->field('min-concentration')->min('$concentration');
        
        $results =  $qb->execute();
        $returned = [];
        foreach ($results as $result) {
            $returned[(string)$result['_id']['$id']] = $result;
        }
        
        return $returned;
    }
}

