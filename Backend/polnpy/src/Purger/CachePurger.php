<?php
namespace App\Purger;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use App\Document\PolenDocument;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class CachePurger
{
    private $registry;
    
    private $cache;
    
    public function __construct(ManagerRegistry $manager, AdapterInterface $cache)
    {
        $this->registry = $manager;
        $this->cache = $cache;
    }
    
    public function purge()
    {
        $this->cache->deleteItem('polen.list');
        $this->cache->deleteItem('polen.list.predicate');
        $polens = $this->registry->getRepository(PolenDocument::class)->findAll();
        foreach ($polens as $polen) {
            $this->cache->deleteItem('polen.history.'.$polen->getId());
        }
        $keyStore = $this->cache->getItem('date_overview');
        $data = $keyStore->get();
        if (!is_array($data)) {
            $data = [];
        }
        foreach ($data as $key) {
            $this->cache->deleteItem($key);
        }
        $keyStore->set([]);
        $this->cache->save($keyStore);
    }
}

