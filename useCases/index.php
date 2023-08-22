<?php

declare(strict_types=1);

namespace App\UseCases;

require __DIR__ . '/../vendor/autoload.php';

$multicurrencyBankAccountUseCase = new MulticurrencyBankAccountUseCase();

$multicurrencyBankAccountUseCase->useCase();