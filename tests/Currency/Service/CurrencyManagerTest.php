<?php

declare(strict_types=1);

namespace App\Tests\Currency\Service;

use App\Currency\Currencies;
use App\Currency\Service\CurrencyManager;
use PHPUnit\Framework\TestCase;

final class CurrencyManagerTest extends TestCase
{
    public function testCreate(): void
    {
        $currency = (new CurrencyManager())->createCurrency(Currencies::USD);

        $this->assertSame(Currencies::USD->value, $currency->getName());
    }
}