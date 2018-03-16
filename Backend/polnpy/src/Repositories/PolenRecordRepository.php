<?php
namespace App\Repositories;

use Doctrine\ODM\MongoDB\DocumentRepository;
use App\Document\PolenDocument;
use App\Document\PolenRecord;

class PolenRecordRepository extends DocumentRepository
{
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
    
    public function findInfoForPolen(PolenDocument $polen)
    {
        $qb = $this->createAggregationBuilder()
            ->match()
                ->field('polen')->equals($polen)
            ->group()
                ->field('_id')->expression('null')
                ->field('max')->max('$recordDate')
                ->field('min')->min('$recordDate');
        
        return $qb->execute()->getSingleResult();
    }
}

