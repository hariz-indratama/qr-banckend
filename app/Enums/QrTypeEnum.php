<?php

declare(strict_types=1);

namespace App\Enums;

enum QrTypeEnum: string
{
    case STATIC = 'STATIC';
    case DYNAMIC = 'DYNAMIC';
}
