<?php

namespace App\Definition\Exception;

use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RefreshRequired extends AuthenticationException
{
    public $token;

    public function __construct(UnencryptedToken $token)
    {
        $this->token = $token;    
    }
}