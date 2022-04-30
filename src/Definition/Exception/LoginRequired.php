<?php

namespace App\Definition\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginRequired extends AuthenticationException { }