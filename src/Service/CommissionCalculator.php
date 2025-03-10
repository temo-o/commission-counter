<?php

namespace App\Service;

use App\Dto\OperationDto;
use App\Service\Strategy\CommissionStrategyFactory;

class CommissionCalculator
{
    public function __construct(private CommissionStrategyFactory $strategyFactory) {}

    public function calculate(OperationDto $operation): float
    {
        $strategy = $this->strategyFactory->getStrategy($operation);

        return $strategy->calculate($operation);
    }
}
