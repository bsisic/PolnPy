<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Ramsey\Uuid\Uuid;
use App\Document\User;

class UserCreationCommand extends Command
{
    private $registry;
    
    public function setRegistry(ManagerRegistry $manager)
    {
        $this->registry = $manager;
    }
    
    protected function configure()
    {
        $this->setName('app:create:user');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tokenId = Uuid::uuid4();
        
        $user = new User();
        $user->setKey((string)$tokenId);
        
        $this->registry->getManager()->persist($user);
        $this->registry->getManager()->flush();
        
        $output->writeln(sprintf('User created with token : <info>%s</info>', $user->getKey()));
    }
}

