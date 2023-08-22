<?php

declare(strict_types=1);

namespace App\Tests\BankAccount\Service;

use App\BankAccount\MulticurrencyBankAccount;
use App\BankAccount\Service\MulticurrencyBankAccountCurrencyManager;
use App\BankAccount\Service\MulticurrencyBankAccountManager;
use App\Currency\Currencies;
use App\Currency\Service\CurrencyManager;
use App\CurrencyRate\Service\CurrencyRateManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MulticurrencyBankAccountManagerTest extends TestCase
{
    public function testCreateBankAccount(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccount = $bankAccountManager->createBankAccount();

        $this->assertSame(MulticurrencyBankAccount::class, $bankAccount::class);
    }

    public function testGetCurrency(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccount = $bankAccountManager->createBankAccount();

        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 100);

        $bankAccount->addCurrency($bankAccountCurrency);

        $this->assertSame($bankAccountCurrency, $bankAccountManager->getCurrency(Currencies::USD));
    }

    public function testAddCurrency(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccount = $bankAccountManager->createBankAccount();

        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);
        $bankAccountManager->addCurrency($bankAccountCurrency);

        $this->assertSame(Currencies::USD->value, $bankAccountManager->getCurrency(Currencies::USD)->getCurrency()->getName());
        $this->assertSame(150, $bankAccountManager->getCurrency(Currencies::USD)->getAmount());
    }

    public function testRemoveCurrency(): void
    {
        $currencyRateManager = new CurrencyRateManager(new CurrencyManager());
        
        $currencyRate = $currencyRateManager->createCurrencyRate(Currencies::USD, Currencies::RUB, 95);
        $currencyRateManager->addCurrencyRate($currencyRate);
        
        $bankAccountManager = new MulticurrencyBankAccountManager($currencyRateManager);
        
        $bankAccount = $bankAccountManager->createBankAccount();
        
        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);
        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::RUB, 500);
        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $this->assertSame(150, $bankAccountManager->getBalance());

        $bankAccountManager->removeCurrency(Currencies::RUB);

        $this->assertSame(155, $bankAccountManager->getBalance());
        $this->assertSame(
            ['USD' => $bankAccountManager->getCurrency(Currencies::USD)],
            $bankAccountManager->getCurrencies()
        );
    }

    public function testGetCurrencies(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccount = $bankAccountManager->createBankAccount();

        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrency =  $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 100);
        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountCurrency =  $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::EUR, 150);
        $bankAccount->addCurrency($bankAccountCurrency);

        $this->assertSame(
            [
                'USD' => $bankAccountManager->getCurrency(Currencies::USD), 
                'EUR' => $bankAccountManager->getCurrency(Currencies::EUR)
            ],
            $bankAccountManager->getCurrencies()
        );
    }

    public function testGetCurrencyNames(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccount = $bankAccountManager->createBankAccount();

        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrency =  $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 100);
        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountCurrency =  $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::EUR, 150);
        $bankAccount->addCurrency($bankAccountCurrency);

        $this->assertSame(['USD', 'EUR'], $bankAccountManager->getCurrencyNames());
    }

    public function testSetMainCurrency(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccount = $bankAccountManager->createBankAccount();
        
        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrency =  $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 100);
        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $this->assertSame(Currencies::USD->value, $bankAccountManager->getMainCurrency()->getCurrency()->getName());
    }

    public function testGetBalance(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));
        
        $bankAccount = $bankAccountManager->createBankAccount();
        
        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);
        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $this->assertSame(150, $bankAccountManager->getBalance());
    }

    public function testGetBalanceCurrency(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));
        
        $bankAccount = $bankAccountManager->createBankAccount();
        
        $bankAccountManager->setBankAccount($bankAccount);
        
        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);
        $bankAccount->addCurrency($bankAccountCurrency);
        
        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::RUB, 100);
        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $this->assertSame(100, $bankAccountManager->getBalance(Currencies::RUB));
    }

    public function testDeposit(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccount = $bankAccountManager->createBankAccount();

        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);

        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $bankAccountManager->deposit(100);

        $this->assertSame(250, $bankAccountManager->getBalance());
    }

    public function testDepositCurrency(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccount = $bankAccountManager->createBankAccount();

        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);

        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $bankAccountManager->deposit(100, Currencies::USD);

        $this->assertSame(250, $bankAccountManager->getBalance());
    }

    public function testWithdraw(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccount = $bankAccountManager->createBankAccount();

        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);

        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $bankAccountManager->withdraw(50);

        $this->assertSame(100, $bankAccountManager->getBalance());
    }

    public function testWithdrawCurrency(): void
    {
        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccount = $bankAccountManager->createBankAccount();

        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);

        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $bankAccountManager->withdraw(50, Currencies::USD);

        $this->assertSame(100, $bankAccountManager->getBalance());
    }

    public function testWithdrawBalanceLessThanZero(): void
    {
        $this->expectException(RuntimeException::class);

        $bankAccountManager = new MulticurrencyBankAccountManager(new CurrencyRateManager(new CurrencyManager()));

        $bankAccount = $bankAccountManager->createBankAccount();

        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);

        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $bankAccountManager->withdraw(250);
    }

    public function testConvert(): void
    {
        $currencyRateManager = new CurrencyRateManager(new CurrencyManager());
        
        $currencyRate = $currencyRateManager->createCurrencyRate(Currencies::USD, Currencies::RUB, 95);
        $currencyRateManager->addCurrencyRate($currencyRate);
        
        $bankAccountManager = new MulticurrencyBankAccountManager($currencyRateManager);
        
        $bankAccount = $bankAccountManager->createBankAccount();
        
        $bankAccountManager->setBankAccount($bankAccount);
        
        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD, 150);

        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->setMainCurrency(Currencies::USD);

        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::RUB, 100);
        $bankAccount->addCurrency($bankAccountCurrency);

        $bankAccountManager->convert(Currencies::USD, Currencies::RUB, 50);
        
        $this->assertSame(100, $bankAccountManager->getBalance());
        $this->assertSame(4850, $bankAccountManager->getBalance(Currencies::RUB));
    }
}