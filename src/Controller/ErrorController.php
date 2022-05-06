<?php

namespace App\Controller;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ErrorController
{
    public function show(FlattenException $exception, Request $request)
    {
        
        return new JsonResponse([
            'xxx' => $exception->getMessage(),
            'path' => $request->getPathInfo(),
        ]);
    }
}