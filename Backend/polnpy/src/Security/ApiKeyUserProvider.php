<?php
namespace App\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use App\Document\User;

class ApiKeyUserProvider implements UserProviderInterface
{
    private $registry;
    
    public function __construct(ManagerRegistry $manager)
    {
        $this->registry = $manager;
    }
    
    public function loadUserByUsername($username)
    {
        return $this->registry->getManager()->getRepository(User::class)->findOneByKey($username);
    }
    
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }
    
    public function supportsClass($class)
    {
        return User::class === $class;
    }
}

