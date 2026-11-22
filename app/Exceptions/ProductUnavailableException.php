<?php

namespace App\Exceptions;

use RuntimeException;

class ProductUnavailableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Tài khoản không còn khả dụng.');
    }
}
