<?php

namespace App\Enums;

enum DomainStatus: string
{
    case PENDING = "pending";
    case APPROVED = "approved";
    case REJECTED = "rejected";
}
