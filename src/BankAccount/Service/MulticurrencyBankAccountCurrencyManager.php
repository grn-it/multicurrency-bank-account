<?php

declare(strict_types=1);

namespace App\BankAccount\Service;

use App\BankAccount\MulticurrencyBankAccountCurrency;
use App\Currency\Currencies;
use App\Currency\Service\CurrencyManager;

class MulticurrencyBankAccountCurrencyManager
{
    public function __construct(private readonly CurrencyManager $currencyManager)
    {
    }

    public function createBankAccountCurrency(
        Currencies $currency,
        int $amount = 0,
        bool $main = false
    ): MulticurrencyBankAccountCurrency
    {
        $currency = $this->currencyManager->createCurrency($currency);

        return new MulticurrencyBankAccountCurrency($currency, $amount, $main);
    }
}
