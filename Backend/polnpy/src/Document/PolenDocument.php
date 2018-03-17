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
     * @Mongo\Field(type="int")
     */
    private $warning = 2;
    
    /**
     * @Mongo\Field(type="int")
     */
    private $alert = 6;
    
    /**
     * @Mongo\Field(type="string")
     */
    private $imageUrl = '';
    
    /**
     * @Mongo\Field(type="collection")
     */
    private $predictionArguments = [];
    
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
        return $this->predictive && !empty($this->getPredictionArguments());
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
    
    /**
     * @return number
     */
    public function getWarning()
    {
        return $this->warning;
    }

    /**
     * @return number
     */
    public function getAlert()
    {
        return $this->alert;
    }

    /**
     * @param number $warning
     */
    public function setWarning($warning)
    {
        $this->warning = $warning;
    }

    /**
     * @param number $alert
     */
    public function setAlert($alert)
    {
        $this->alert = $alert;
    }
    
    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }
    
    /**
     * @return multitype:
     */
    public function getPredictionArguments()
    {
        return $this->predictionArguments;
    }

    /**
     * @param multitype: $predictionArguments
     */
    public function setPredictionArguments($predictionArguments)
    {
        $this->predictionArguments = $predictionArguments;
    }
}

