<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;

/**
 * @author vallance
 * @Mongo\Document(repositoryClass="App\Repositories\UserRepository")
 */
class User
{
    /**
     * @Mongo\Id()
     */
    private $id;
    
    /**
     * @Mongo\Field(type="string")
     */
    private $key;
    
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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}

