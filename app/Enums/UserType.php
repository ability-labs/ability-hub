<?php

namespace App\Enums;

enum UserType: string
{
    case ADMINISTRATOR = 'admin';
    case USER = 'user';
}
