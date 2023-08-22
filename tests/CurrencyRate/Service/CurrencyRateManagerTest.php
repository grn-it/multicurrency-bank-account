<?php

declare(strict_types=1);

namespace App\Tests\CurrencyRate\Service;

use App\Currency\Currencies;
use App\Currency\Service\CurrencyManager;
use App\CurrencyRate\CurrencyRate;
use App\CurrencyRate\Service\CurrencyRateManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CurrencyRateManagerTest extends TestCase
{
    public function testConstruct(): void
    {
        $currencyManager = new CurrencyManager();

        $currencyRate = new CurrencyRate(
            $currencyManager->createCurrency(Currencies::USD),
            $currencyManager->createCurrency(Currencies::EUR),
            1
        );

        $currencyRateManager = new CurrencyRateManager(new CurrencyManager());

        $currencyRateManager->setCurrencyRate($currencyRate);

        $this->assertSame($currencyRate, $currencyRateManager->getCurrencyRate());
    }

    public function testAddCurrencyRate(): void
    {
        $currencyManager = new CurrencyManager();

        $currencyRate = new CurrencyRate(
            $currencyManager->createCurrency(Currencies::USD),
            $currencyManager->createCurrency(Currencies::EUR),
            1
        );

        $currencyRateManager = new CurrencyRateManager(new CurrencyManager());

        $currencyRateManager->addCurrencyRate($currencyRate);

        $this->assertSame(['USDEUR' => $currencyRate], $currencyRateManager->getCurrencyRates());
    }

    public function testFindCurrencyRate(): void
    {
        $currencyManager = new CurrencyManager();

        $currencyRate = new CurrencyRate(
            $currencyManager->createCurrency(Currencies::USD),
            $currencyManager->createCurrency(Currencies::EUR),
            1
        );

        $currencyRateManager = new CurrencyRateManager($currencyManager);

        $currencyRateManager->addCurrencyRate($currencyRate);

        $currencyRateUsdEur = $currencyRateManager->findCurrencyRate(Currencies::USD, Currencies::EUR);

        $this->assertSame($currencyRate, $currencyRateUsdEur);
    }

    public function testSetRate(): void
    {
        $currencyManager = new CurrencyManager();

        $currencyRate = new CurrencyRate(
            $currencyManager->createCurrency(Currencies::USD),
            $currencyManager->createCurrency(Currencies::EUR),
            1
        );

        $currencyRateManager = new CurrencyRateManager($currencyManager);

        $currencyRateManager->setCurrencyRate($currencyRate);
        $currencyRateManager->setRate(2);

        $this->assertSame(2, $currencyRate->getRate());
    }

    public function testSetRateZero(): void
    {
        $this->expectException(RuntimeException::class);

        $currencyManager = new CurrencyManager();

        $currencyRate = new CurrencyRate(
            $currencyManager->createCurrency(Currencies::USD),
            $currencyManager->createCurrency(Currencies::EUR),
            1
        );

        $currencyRateManager = new CurrencyRateManager($currencyManager);

        $currencyRateManager->setCurrencyRate($currencyRate);
        $currencyRateManager->setRate(0);
    }
}