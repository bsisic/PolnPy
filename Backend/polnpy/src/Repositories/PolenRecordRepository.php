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
}

