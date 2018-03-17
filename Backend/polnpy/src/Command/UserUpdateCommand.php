<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use App\Document\User;

class UserUpdateCommand extends Command
{
    private $registry;
    
    public function setRegistry(ManagerRegistry $manager)
    {
        $this->registry = $manager;
    }
    
    protected function configure()
    {
        $this->setName('app:update:user')
            ->addArgument('userId')
            ->addArgument('role');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->registry->getManager()->getRepository(User::class)->find($input->getArgument('userId'));
        
        $roles = $user->getRoles();
        array_push($roles, $input->getArgument('role'));
        $user->setRoles($roles);

        $this->registry->getManager()->flush();
    }
}

