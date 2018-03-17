<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use App\Document\User;

class UserListCommand extends Command
{
    private $registry;
    
    public function setRegistry(ManagerRegistry $manager)
    {
        $this->registry = $manager;
    }
    
    protected function configure()
    {
        $this->setName('app:list:user');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->registry->getManager()->getRepository(User::class)->findAll();
    
        foreach ($users as $user) {
            $output->writeln(sprintf('%s: %s [%s]', $user->getId(), $user->getKey(), implode(', ', $user->getRoles())));
        }
    }
}

