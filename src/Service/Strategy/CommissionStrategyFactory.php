<?php

namespace App\Service\Strategy;

use App\Dto\OperationDto;
use RuntimeException;

class CommissionStrategyFactory
{
    private array $strategies;

    public function __construct(iterable $strategies)
    {
        foreach ($strategies as $strategy) {
            $this->strategies[] = $strategy;
        }
    }

    public function getStrategy(OperationDto $operation): CommissionStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($operation)) {
                return $strategy;
            }
        }

        throw new RuntimeException("No suitable commission strategy found");
    }
}
