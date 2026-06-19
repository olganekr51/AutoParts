<?php

namespace App\Application\Enum;

enum ImportStatus: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case ERROR = 'error';
    case SKIPPED = 'skipped';
}