<?php
namespace App\Controller;

use App\Purger\CachePurger;
use App\Response\CrossJsonResponse;

class CacheController
{
    private $purger;
    
    public function __construct(CachePurger $purger)
    {
        $this->purger = $purger;
    }
    
    public function purge()
    {
        $this->purger->purge();
        
        return new CrossJsonResponse(['message' => 'success']);
    }
}
