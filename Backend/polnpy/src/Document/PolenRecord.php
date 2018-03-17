<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;

/**
 * @author vallance
 * @Mongo\Document(repositoryClass="App\Repositories\PolenRecordRepository")
 */
class PolenRecord
{
    /**
     * @Mongo\Id()
     */
    private $id;
    
    /**
     * @Mongo\Field(type="int")
     */
    private $concentration;
    
    /**
     * @Mongo\Field(type="date")
     */
    private $recordDate;
    
    /**
     * @Mongo\ReferenceOne(targetDocument="PolenDocument")
     */
    private $polen;
    
    /**
     * @return PolenDocument
     */
    public function getPolen()
    {
        return $this->polen;
    }

    /**
     * @param mixed $polen
     */
    public function setPolen($polen)
    {
        $this->polen = $polen;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getConcentration()
    {
        return $this->concentration;
    }

    /**
     * @return mixed
     */
    public function getRecordDate()
    {
        return $this->recordDate;
    }

    /**
     * @param mixed $concentration
     */
    public function setConcentration($concentration)
    {
        $this->concentration = $concentration;
    }

    /**
     * @param mixed $recordDate
     */
    public function setRecordDate($recordDate)
    {
        $this->recordDate = $recordDate;
    }
}

