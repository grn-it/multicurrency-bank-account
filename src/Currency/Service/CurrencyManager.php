<?php

declare(strict_types=1);

namespace App\Currency\Service;

use App\Currency\Currencies;
use App\Currency\Currency;

class CurrencyManager
{
    public function createCurrency(Currencies $currency): Currency
    {
        return new Currency($currency->value);
    }
}
