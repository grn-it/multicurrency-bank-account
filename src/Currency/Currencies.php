<?php

declare(strict_types=1);

namespace App\Currency;

enum Currencies: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case RUB = 'RUB';
}
