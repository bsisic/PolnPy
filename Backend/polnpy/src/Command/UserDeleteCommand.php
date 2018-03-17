<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use App\Document\User;

class UserDeleteCommand extends Command
{
    private $registry;
    
    public function setRegistry(ManagerRegistry $manager)
    {
        $this->registry = $manager;
    }
    
    protected function configure()
    {
        $this->setName('app:delete:user')
            ->addArgument('userId');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->registry->getManager()->getRepository(User::class)->find($input->getArgument('userId'));
        $this->registry->getManager()->remove($user);
        $this->registry->getManager()->flush();
    }
}

