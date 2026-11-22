<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Số dư không đủ để mua tài khoản này.');
    }
}
