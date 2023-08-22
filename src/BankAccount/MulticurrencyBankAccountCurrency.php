<?php

declare(strict_types=1);

namespace App\BankAccount;

use App\Currency\Currency;

class MulticurrencyBankAccountCurrency
{
    public function __construct(private readonly Currency $currency, private int $amount, private bool $main = false)
    {
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function isMain(): bool
    {
        return $this->main;
    }

    public function setMain(bool $main): void
    {
        $this->main = $main;
    }
}
