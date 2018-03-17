<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author vallance
 * @Mongo\Document(repositoryClass="App\Repositories\UserRepository")
 */
class User implements UserInterface
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
     * @Mongo\Field(type="collection")
     */
    private $roles = ['ROLE_USER'];
    
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

    public function getPassword()
    {}

    public function eraseCredentials()
    {}

    public function getSalt()
    {}

    public function getRoles()
    {
        return $this->roles;
    }
    
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function getUsername()
    {
        return $this->key;
    }

}

