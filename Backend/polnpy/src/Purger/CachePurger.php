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
        $keyStore = $this->cache->getItem('key_store');
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

