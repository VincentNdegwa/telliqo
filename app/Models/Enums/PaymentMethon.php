<?php

namespace App\Models\Enums;

enum PaymentMethon: string
{
    case MPESA = 'mpesa';
    case MANUAL = 'manual';
    case PADDLE = 'paddle';
    case CARD = 'card';
}
