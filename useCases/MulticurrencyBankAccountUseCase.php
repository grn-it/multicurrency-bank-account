<?php

declare(strict_types=1);

namespace App\UseCases;

use App\BankAccount\Service\MulticurrencyBankAccountCurrencyManager;
use App\BankAccount\Service\MulticurrencyBankAccountManager;
use App\Currency\Currencies;
use App\Currency\Service\CurrencyManager;
use App\CurrencyRate\Service\CurrencyRateManager;

class MulticurrencyBankAccountUseCase
{
    public function useCase(): void
    {
        /*
            Базовый курс валют: EUR/RUB = 80, USD/RUB = 70, EUR/USD = 1
        */

        $currencyRateManager = new CurrencyRateManager(new CurrencyManager());
        
        $currencyRate = $currencyRateManager->createCurrencyRate(Currencies::EUR, Currencies::RUB, 80);
        $currencyRateManager->addCurrencyRate($currencyRate);

        $currencyRate = $currencyRateManager->createCurrencyRate(Currencies::USD, Currencies::RUB, 70);
        $currencyRateManager->addCurrencyRate($currencyRate);

        $currencyRate = $currencyRateManager->createCurrencyRate(Currencies::EUR, Currencies::USD, 1);
        $currencyRateManager->addCurrencyRate($currencyRate);

        // Курс: EUR/RUB = 80, USD/RUB = 70, EUR/USD = 1

        /*
            1. Клиент открывает мультивалютный счет, включающий сбережения в 3-х валютах с
            основной валютой российский рубль, и пополняет его следующими суммами: 1000
            RUB, 50 EUR, 40 USD.

            Счет = Банк->ОткрытьНовыйСчет()
            Счет->ДобавитьВалюту(RUB)
            Счет->ДобавитьВалюту(EUR)
            Счет->ДобавитьВалюту(USD)
            Счет->УстановитьОсновнуюВалюту(RUB)
            Счет->СписокПоддеживаемыхВалют() // [RUB, EUR, USD]
            Счет->ПополнитьБаланс(RUB(1000))
            Счет->ПополнитьБаланс(EUR(50))
            Счет->ПополнитьБаланс(USD(50))
        */

        $bankAccountManager = new MulticurrencyBankAccountManager($currencyRateManager);

        // Счет = Банк->ОткрытьНовыйСчет()
        $bankAccount = $bankAccountManager->createBankAccount();
        
        $bankAccountManager->setBankAccount($bankAccount);

        $bankAccountCurrencyManager = new MulticurrencyBankAccountCurrencyManager(new CurrencyManager());

        // Счет->ДобавитьВалюту(RUB)
        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::RUB);
        $bankAccountManager->addCurrency($bankAccountCurrency);

        // Счет->ДобавитьВалюту(EUR)
        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::EUR);
        $bankAccountManager->addCurrency($bankAccountCurrency);

        // Счет->ДобавитьВалюту(USD)
        $bankAccountCurrency = $bankAccountCurrencyManager->createBankAccountCurrency(Currencies::USD);
        $bankAccountManager->addCurrency($bankAccountCurrency);

        // Счет->УстановитьОсновнуюВалюту(RUB)
        $bankAccountManager->setMainCurrency(Currencies::RUB);

        // Счет->СписокПоддеживаемыхВалют() // [RUB, EUR, USD]
        $currencyNames = $bankAccountManager->getCurrencyNames(); // ["RUB", "EUR", "USD"]

        // Счет->ПополнитьБаланс(RUB(1000))
        $bankAccountManager->deposit(1000);

        // Счет->ПополнитьБаланс(EUR(50))
        $bankAccountManager->deposit(50, Currencies::EUR);

        // Счет->ПополнитьБаланс(USD(50))
        $bankAccountManager->deposit(50, Currencies::USD);

        // Баланс: 1000 RUB, 50 EUR, 50 USD

         /*
            2. Клиент хочет увидеть суммарный баланс счета в основной валюте, либо в валюте на
            выбор.
            
            Счет->ПолучитьБаланс() => xxxxx RUB
            Счет->ПолучитьБаланс(USD) => xxxxx USD
            Счет->ПолучитьБаланс(EUR) => xxxxx EUR
        */

        // Счет->ПолучитьБаланс() => xxxxx RUB
        $balance = $bankAccountManager->getBalance();                // 1000 RUB

        // Счет->ПолучитьБаланс(USD) => xxxxx USD
        $balance = $bankAccountManager->getBalance(Currencies::USD); // 50 USD

        // Счет->ПолучитьБаланс(EUR) => xxxxx EUR
        $balance = $bankAccountManager->getBalance(Currencies::EUR); // 50 EUR

        /*
            3. Клиент совершает операции пополнения/списания со счета.

            Счет->ПополнитьБаланс(RUB(1000))
            Счет->ПополнитьБаланс(EUR(50))
            Счет->СписатьСБаланса(USD(10))
        */

        // Счет->ПополнитьБаланс(RUB(1000))
        $bankAccountManager->deposit(1000);

        // Счет->ПополнитьБаланс(EUR(50))
        $bankAccountManager->deposit(50, Currencies::EUR);
        

        // Счет->СписатьСБаланса(USD(10))
        $bankAccountManager->withdraw(10, Currencies::USD);

        // Баланс: 2000 RUB, 100 EUR, 40 USD

        /*
            4. Банк меняет курс валюты для EUR и USD по отношению к рублю на 150 и 100
            соответственно.

            EUR->УстановитьКурсОбменаВалюты(RUR, 150)
            USD->УстановитьКурсОбменаВалюты(RUR, 100)
        */

        $currencyRate = $currencyRateManager->findCurrencyRate(Currencies::EUR, Currencies::RUB);

        $currencyRateManager->setCurrencyRate($currencyRate);

        // EUR->УстановитьКурсОбменаВалюты(RUR, 150)
        $currencyRateManager->setRate(150);

        $currencyRate = $currencyRateManager->findCurrencyRate(Currencies::USD, Currencies::RUB);

        $currencyRateManager->setCurrencyRate($currencyRate);

        // USD->УстановитьКурсОбменаВалюты(RUR, 100)
        $currencyRateManager->setRate(100);

        // Курс: EUR/RUB 150, USD/RUB 100

        /*
            5. Клиент хочет увидеть суммарный баланс счета в рублях, после изменения курса
            валют.

            Счет->ПолучитьБаланс() => xxxxx RUB
        */

        // Счет->ПолучитьБаланс() => xxxxx RUB
        $balance = $bankAccountManager->getBalance(); // 2000 RUB

        /*
            6. После этого клиент решает изменить основную валюту счета на EUR, и запрашивает
            текущий баланс.

            Счет->УстановитьОсновнуюВалюту(EUR)
            Счет->ПолучитьБаланс() => xxx EUR
        */

        // Счет->УстановитьОсновнуюВалюту(EUR)
        $bankAccountManager->setMainCurrency(Currencies::EUR);

        // Счет->ПолучитьБаланс() => xxx EUR
        $balance = $bankAccountManager->getBalance(); // 100 EUR

        /*
            7. Чтобы избежать дальнего ослабления рубля клиент решает сконвертировать
            рублевую часть счета в EUR, и запрашивает баланс.

            ДенежныеСредства = Счет->СписатьСБаланса(RUB(1000))
            Счет->ПополнитьБаланс(EUR(ДенежныеСредства))
            Счет->ПолучитьБаланс() => xxx EUR
        */

        // ДенежныеСредства = Счет->СписатьСБаланса(RUB(1000))
        // Конвертируем 1000 RUB -> EUR
        // Счет->ПополнитьБаланс(EUR(ДенежныеСредства))
        $bankAccountManager->convert(Currencies::RUB, Currencies::EUR, 1000);

        // Счет->ПолучитьБаланс() => xxx EUR
        $balance = $bankAccountManager->getBalance(); // 107 EUR

        // Баланс: 1000 RUB, 107 EUR, 40 USD

        /*
            8. Банк меняет курс валюты для EUR к RUB на 120.

            EUR->УстановитьКурсОбменаВалюты(RUR, 120)
        */

        $currencyRate = $currencyRateManager->findCurrencyRate(Currencies::EUR, Currencies::RUB);

        $currencyRateManager->setCurrencyRate($currencyRate);

        // EUR->УстановитьКурсОбменаВалюты(RUR, 120)
        $currencyRateManager->setRate(120);

        // Курс: EUR/RUB 120

        /*
            9. После изменения курса клиент проверяет, что баланс его счета не изменился.

            Счет->ПолучитьБаланс() => xxx EUR
        */

        // Счет->ПолучитьБаланс() => xxx EUR
        $balance = $bankAccountManager->getBalance(); // 107 EUR

        /*
            10. Банк решает, что не может больше поддерживать обслуживание следующих валют
            EUR и USD. Согласовывает с клиентом изменение основной валюты счета на RUB, с
            конвертацией балансов неподдерживаемых валют.

            Счет->УстановитьОсновнуюВалюту(RUB)
            Счет->ОтключитьВалюту(EUR)
            Счет->ОтключитьВалюту(USD)
            Счет->СписокПоддеживаемыхВалют() // [RUB]
            Счет->ПолучитьБаланс() => xxxxx RUB
        */

        // Счет->УстановитьОсновнуюВалюту(RUB)
        $bankAccountManager->setMainCurrency(Currencies::RUB);

        // Счет->ОтключитьВалюту(EUR)
        $bankAccountManager->removeCurrency(Currencies::EUR);
        // Конвертируем RUB -> EUR, 107 * 120 = 12840 RUB
        // Баланс: 13840 RUB, 40 USD

        // Счет->ОтключитьВалюту(USD)
        $bankAccountManager->removeCurrency(Currencies::USD);
        /* Конвертируем USD -> RUB, 40 * 100 = 4000 RUB */
        // Баланс: 17840 RUB

        // Счет->СписокПоддеживаемыхВалют() // [RUB]
        $currencyNames = $bankAccountManager->getCurrencyNames(); // ["RUB"]

        // Счет->ПолучитьБаланс() => xxxxx RUB
        $balance = $bankAccountManager->getBalance(); // 17840 RUB
    }
}