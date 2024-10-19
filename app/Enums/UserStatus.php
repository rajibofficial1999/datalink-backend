<?php

namespace App\Enums;

enum UserStatus: string
{
    case PENDING = "pending";
    case APPROVED = "approved";
    case SUSPENDED = "suspended";
    case REJECTED = "rejected";
}
