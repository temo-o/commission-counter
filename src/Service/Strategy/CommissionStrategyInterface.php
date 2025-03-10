<?php

namespace App\Service\Strategy;

use App\Dto\OperationDto;

interface CommissionStrategyInterface
{
    public function calculate(OperationDto $operation): float;

    // This determines if the strategy supports the given operation
    public function supports(OperationDto $operation): bool;
}
