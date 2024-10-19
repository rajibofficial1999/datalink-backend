<?php

namespace App\Enums;

enum LoginUrlEndpoint: string
{
    case POST_DETAILS = "posts/details";
    case LOGIN = "login";
    case FEMALE_ESCORTS = "female_escorts";
    case MAIL_VERIFY = "mail_verify";
}
