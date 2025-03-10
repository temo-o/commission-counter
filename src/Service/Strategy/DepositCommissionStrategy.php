<?php

namespace App\Service\Strategy;

use App\Dto\OperationDto;

class DepositCommissionStrategy implements CommissionStrategyInterface
{
    private float $feePercentage;

    public function __construct(float $feePercentage)
    {
        $this->feePercentage = $feePercentage;
    }

    public function calculate(OperationDto $operation): float
    {
        return ceil($operation->amount * $this->feePercentage) / 100;
    }

    public function supports(OperationDto $operation): bool
    {
        return $operation->operationType === 'deposit';
    }
}
