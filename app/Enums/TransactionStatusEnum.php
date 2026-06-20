<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionStatusEnum: string
{
    case PENDING = 'PENDING';
    case SUCCESS = 'SUCCESS';
    case FAILED = 'FAILED';
}
