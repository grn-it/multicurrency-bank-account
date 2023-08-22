<?php

declare(strict_types=1);

namespace App\BankAccount;

use App\Currency\Currencies;

class MulticurrencyBankAccount
{
    /** @var array<MulticurrencyBankAccountCurrency> */
    private array $currencies = [];

    public function addCurrency(MulticurrencyBankAccountCurrency $currency): void
    {
        $this->currencies[$currency->getCurrency()->getName()] = $currency;
    }

    public function removeCurrency(MulticurrencyBankAccountCurrency $currency): void
    {
        $currencyName = $currency->getCurrency()->getName();

        if (isset($this->currencies[$currencyName])) {
            unset($this->currencies[$currencyName]);
        }
    }

    public function getCurrency(Currencies $currency): ?MulticurrencyBankAccountCurrency
    {
        return $this->currencies[$currency->value] ?? null;
    }

    /** @return array<MulticurrencyBankAccountCurrency> */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }
}
