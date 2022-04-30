<?php

namespace App\Definition\ServiceResponse\Base;

abstract class AppResponse
{
    public mixed $data;

    public function __construct(mixed $data = null)
    {
        $this->data = $data;        
    }
}