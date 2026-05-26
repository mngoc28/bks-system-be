<?php

namespace App\Enums;

enum Status: int
{
    case PENDING = 0;
    case ACTIVE = 1;
    case BLOCKED = 2;
    case PENDING_APPROVAL = 3;
    case REJECTED = 4;
}
