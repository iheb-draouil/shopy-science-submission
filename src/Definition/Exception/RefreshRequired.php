<?php

namespace App\Definition\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RefreshRequired extends AuthenticationException
{
    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;    
    }
}