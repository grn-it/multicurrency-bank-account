<?php

declare(strict_types=1);

namespace App\CurrencyRate\Service;

use App\Currency\Currencies;
use App\Currency\Service\CurrencyManager;
use App\CurrencyRate\CurrencyRate;
use LogicException;
use RuntimeException;

class CurrencyRateManager
{
    /** @var array<CurrencyRate> $currencyRates */
    private array $currencyRates = [];

    private ?CurrencyRate $currencyRate = null;

    public function __construct(private readonly CurrencyManager $currencyManager)
    {
    }

    public function createCurrencyRate(Currencies $currency, Currencies $toCurrency, int $rate): CurrencyRate
    {
        return new CurrencyRate(
            $this->currencyManager->createCurrency($currency),
            $this->currencyManager->createCurrency($toCurrency),
            $rate
        );
    }

    public function getCurrencyRate(): CurrencyRate
    {
        if (!$this->currencyRate) {
            throw new LogicException('Currency rate not set.');
        }

        return $this->currencyRate;
    }

    public function setCurrencyRate(CurrencyRate $currencyRate): void
    {
        $this->currencyRate = $currencyRate;
    }

    /** @return array<CurrencyRate> */
    public function getCurrencyRates(): array
    {
        return $this->currencyRates;
    }

    public function addCurrencyRate(CurrencyRate $currencyRate): void
    {
        if ($this->contains($currencyRate)) {
            return;
        }

        $this->currencyRates[$this->getObjectHash($currencyRate)] = $currencyRate;
    }

    public function findCurrencyRate(Currencies $currency, Currencies $toCurrency): ?CurrencyRate
    {
        return $this->currencyRates[$this->createHash($currency->value, $toCurrency->value)] ?? null;
    }

    public function setRate(int $rate): void
    {
        if ($rate <= 0) {
            throw new RuntimeException('Rate must be positive.');
        }

        $this->getCurrencyRate()->setRate($rate);
    }

    private function contains(CurrencyRate $currencyRate): bool
    {
        return isset($this->currencyRates[$this->getObjectHash($currencyRate)]);
    }

    private function getObjectHash(CurrencyRate $currencyRate): string
    {
        return $this->createHash(
            $currencyRate->getCurrency()->getName(),
            $currencyRate->getToCurrency()->getName()
        );
    }

    private function createHash(string $currency, string $toCurrency): string
    {
        return $currency . $toCurrency;
    }
}
