<?php

namespace App\Enums;

enum PersonGender: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    case UNDEFINED = 'undefined';
}
