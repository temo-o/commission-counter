<?php

namespace App\Service;

use App\DTO\OperationDto;
use Generator;
use IteratorAggregate;

class CsvParser implements IteratorAggregate
{
    private string $filePath;

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getIterator(): Generator
    {
        if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
            throw new \RuntimeException("File not found or not readable: {$this->filePath}");
        }

        $handle = fopen($this->filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Failed to open file: {$this->filePath}");
        }

        while (($row = fgetcsv($handle)) !== false) {
            $parsedRow = $this->mapRow($row);

            if ($parsedRow) {
                yield $parsedRow;
            }
        }

        fclose($handle);
    }

    private function mapRow(array $row): ?OperationDto
    {
        if (count($row) !== 6) {
            return null;
        }

        [$date, $userId, $userType, $operationType, $amount, $currency] = $row;

        // Basic validation
        if (!$this->validateDate($date) || !$this->validateAmount($amount)) {
            return null;
        }

        return new OperationDto(
            date: $date,
            userId: (int) $userId,
            userType: strtolower($userType),
            operationType: strtolower($operationType),
            amount: (float) $amount,
            currency: strtoupper($currency)
        );
    }

    private function validateDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function validateAmount(string $amount): bool
    {
        return is_numeric($amount) && (float) $amount >= 0;
    }
}
