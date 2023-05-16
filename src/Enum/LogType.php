<?php

namespace App\Enum;

enum LogType: string
{
    case CREATED = 'created';
    case DELETED = 'deleted';
    case UPDATED = 'updated';
    case RESTORED = 'restored';
}
