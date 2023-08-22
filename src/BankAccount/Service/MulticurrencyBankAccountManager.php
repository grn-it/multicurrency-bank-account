<?php

declare(strict_types=1);

namespace App\BankAccount\Service;

use App\BankAccount\MulticurrencyBankAccount;
use App\BankAccount\MulticurrencyBankAccountCurrency;
use App\Currency\Currencies;
use App\CurrencyRate\Service\CurrencyRateManager;
use LogicException;
use RuntimeException;

class MulticurrencyBankAccountManager
{
    private ?MulticurrencyBankAccount $bankAccount = null;

    public function __construct(private readonly CurrencyRateManager $currencyRateManager)
    {
    }

    public function createBankAccount(): MulticurrencyBankAccount
    {
        return new MulticurrencyBankAccount();
    }

    public function getBankAccount(): MulticurrencyBankAccount
    {
        if (!$this->bankAccount) {
            throw new LogicException('Bank account not set.');
        }

        return $this->bankAccount;
    }

    public function setBankAccount(MulticurrencyBankAccount $bankAccount): void
    {
        $this->bankAccount = $bankAccount;
    }

    public function getCurrency(Currencies $currency): ?MulticurrencyBankAccountCurrency
    {
        return $this->getBankAccount()->getCurrency($currency);
    }

    public function addCurrency(MulticurrencyBankAccountCurrency $bankAccountCurrency): void
    {
        $this->getBankAccount()->addCurrency($bankAccountCurrency);
    }

    public function removeCurrency(Currencies $currency, ?Currencies $convertToCurrency = null): void
    {
        $bankAccountCurrency = $this->getCurrency($currency);

        if (!$bankAccountCurrency) {
            return;
        }

        $mainCurrency = $this->getMainCurrency();

        if ($mainCurrency) {
            if ($mainCurrency->getCurrency()->getName() === $currency->value) {
                throw new LogicException('Cannot remove main bank account currency.');
            }
        }

        if ($bankAccountCurrency->getAmount() > 0) {
            $fromCurrency = $currency;

            if ($convertToCurrency) {
                $toCurrency = $this->getCurrency($convertToCurrency);

                if (!$toCurrency) {
                    throw new LogicException(
                        sprintf(
                            'Bank account currency "%s" for conversion not found.',
                            $convertToCurrency->value
                        )
                    );
                }
            } else {
                if (!$mainCurrency) {
                    throw new LogicException('Main bank account currency not set.');
                }

                $toCurrency = $mainCurrency;
            }

            $toCurrencyValue = Currencies::tryFrom($toCurrency->getCurrency()->getName());

            if ($toCurrencyValue) {
                $toCurrency = $toCurrencyValue;
            } else {
                throw new LogicException(
                    sprintf('Currency "%s" does not exist.', $toCurrency->getCurrency()->getName())
                );
            }

            $this->convert($fromCurrency, $toCurrency, $bankAccountCurrency->getAmount());
        }

        $this->getBankAccount()->removeCurrency($bankAccountCurrency);
    }

    /** @return array<MulticurrencyBankAccountCurrency> */
    public function getCurrencies(): array
    {
        return $this->getBankAccount()->getCurrencies();
    }

    /** @return array<string> */
    public function getCurrencyNames(): array
    {
        $currencyNames = [];

        foreach ($this->getCurrencies() as $bankAccountCurrency) {
            $currencyNames[] = $bankAccountCurrency->getCurrency()->getName();
        }

        return $currencyNames;
    }

    public function getMainCurrency(): ?MulticurrencyBankAccountCurrency
    {
        $mainCurrency = null;

        /** @var MulticurrencyBankAccountCurrency $currency */
        foreach ($this->getBankAccount()->getCurrencies() as $currency) {
            if ($currency->isMain()) {
                $mainCurrency = $currency;
                
                break;
            }
        }

        return $mainCurrency;
    }

    public function setMainCurrency(Currencies $currency): void
    {
        $bankAccountCurrency = $this->getBankAccount()->getCurrency($currency);

        if (!$bankAccountCurrency) {
            throw new LogicException(
                sprintf(
                    'Bank account currency "%s" not found.',
                    $currency->value
                )
            );
        }

        $mainCurrency = $this->getMainCurrency();

        if ($mainCurrency) {
            $mainCurrency->setMain(false);
        }

        $bankAccountCurrency->setMain(true);
    }

    public function getBalance(?Currencies $currency = null): int
    {
        if ($currency) {
            $bankAccountCurrency = $this->getBankAccount()->getCurrency($currency);

            if (!$bankAccountCurrency) {
                throw new LogicException(
                    sprintf('Bank account currency "%s" not found.', $currency->value)
                );
            }
        } else {
            $mainCurrency = $this->getMainCurrency();

            if ($mainCurrency) {
                $bankAccountCurrency = $mainCurrency;
            } else {
                throw new LogicException('Main bank account currency not set.');
            }
        }

        return $bankAccountCurrency->getAmount();
    }

    public function deposit(int $amount, ?Currencies $currency = null): void
    {
        if ($currency) {
            $bankAccountCurrency = $this->getCurrency($currency);

            if (!$bankAccountCurrency) {
                throw new LogicException(
                    sprintf(
                        'Bank account currency "%s" not found.',
                        $currency->value
                    )
                );
            }
        } else {
            $bankAccountCurrency = $this->getMainCurrency();

            if (!$bankAccountCurrency) {
                throw new LogicException('Main bank account currency not set.');
            }
        }

        $amount = $bankAccountCurrency->getAmount() + $amount;

        $bankAccountCurrency->setAmount($amount);
    }

    public function withdraw(int $amount, ?Currencies $currency = null): void
    {
        if ($currency) {
            $bankAccountCurrency = $this->getCurrency($currency);

            if (!$bankAccountCurrency) {
                throw new LogicException(
                    sprintf(
                        'Bank account currency "%s" not found.',
                        $currency->value
                    )
                );
            }
        } else {
            $bankAccountCurrency = $this->getMainCurrency();

            if (!$bankAccountCurrency) {
                throw new LogicException('Main bank account currency not set.');
            }
        }

        if ($amount > $bankAccountCurrency->getAmount()) {
            throw new RuntimeException(
                sprintf(
                    'Failed withdraw %d %s. Balance: %d.',
                    $amount,
                    $bankAccountCurrency->getCurrency()->getName(),
                    $bankAccountCurrency->getAmount()
                )
            );
        }

        $amount = $bankAccountCurrency->getAmount() - $amount;

        $bankAccountCurrency->setAmount($amount);
    }

    public function convert(Currencies $fromCurrency, Currencies $toCurrency, int $amount): void
    {
        $currencyRate = $this->currencyRateManager->findCurrencyRate($fromCurrency, $toCurrency);

        if ($currencyRate) {
            $convertedCurrencyAmount = $amount * $currencyRate->getRate();
        } else {
            $currencyRate = $this->currencyRateManager->findCurrencyRate($toCurrency, $fromCurrency);
    
            if ($currencyRate) {
                $convertedCurrencyAmount = (int) round($amount / $currencyRate->getRate());
            } else {
                throw new LogicException(
                    sprintf(
                        'Currency rate "%s"/"%s" and "%s"/"%s" not found.',
                        $fromCurrency->value,
                        $toCurrency->value,
                        $toCurrency->value,
                        $fromCurrency->value
                    )
                );
            }
        }

        $this->withdraw($amount, $fromCurrency);
        $this->deposit($convertedCurrencyAmount, $toCurrency);
    }
}
