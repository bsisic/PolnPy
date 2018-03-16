<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;

/**
 * @author vallance
 * @Mongo\Document(repositoryClass="App\Repositories\PolenDocumentRepository")
 */
class PolenDocument
{
    /**
     * @Mongo\Id()
     */
    private $id;
    
    /**
     * @Mongo\Field(type="string")
     */
    private $name;
    
    /**
     * @Mongo\Field(type="boolean")
     */
    private $predictive = false;
    
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function getPredictive()
    {
        return $this->predictive;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param boolean $predictive
     */
    public function setPredictive($predictive)
    {
        $this->predictive = $predictive;
    }
}

