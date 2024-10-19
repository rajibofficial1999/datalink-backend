<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = "pending";
    case CONFIRMED = "confirmed";
    case REJECTED = "rejected";
}
