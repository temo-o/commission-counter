<?php

namespace App\DTO;

class OperationDto
{
    public function __construct(
        public string $date,
        public int $userId,
        public string $userType,
        public string $operationType,
        public float $amount,
        public string $currency
    ) {}
}
