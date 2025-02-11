<?php

namespace App\Enums;

enum UserType: string
{
    case ADMINISTRATOR = 'admin';
    case CARE_RECEIVER = 'care-receiver';
    case CARE_GIVER = 'care-giver';
}
