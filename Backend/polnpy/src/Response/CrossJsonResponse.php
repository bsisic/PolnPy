<?php
namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class CrossJsonResponse extends JsonResponse
{
    public function __construct($data = null, int $status = 200, array $headers = array('Access-Control-Allow-Origin' => '*'), bool $json = false)
    {
        parent::__construct($data, $status, $headers, $json);
    }
}

