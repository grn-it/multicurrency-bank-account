<?php

declare(strict_types=1);

namespace App\CurrencyRate;

use App\Currency\Currency;

class CurrencyRate
{
    public function __construct(
        private readonly Currency $currency,
        private readonly Currency $toCurrency,
        private int $rate
    ) {
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getToCurrency(): Currency
    {
        return $this->toCurrency;
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    public function setRate(int $rate): void
    {
        $this->rate = $rate;
    }
}
