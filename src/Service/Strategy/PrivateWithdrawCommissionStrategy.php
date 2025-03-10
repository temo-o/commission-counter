<?php

namespace App\Service\Strategy;

use App\Dto\OperationDto;
use App\Service\ExchangeRateService;
use DateTime;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PrivateWithdrawCommissionStrategy implements CommissionStrategyInterface
{
    private array $weeklyWithdrawals = [];
    private ExchangeRateService $exchangeRateService;
    private int $freeWithdrawalsLimit;
    private float $freeAmountLimit;
    private float $feePercentage;

    public function __construct(
        ExchangeRateService $exchangeRateService,
        int $freeWithdrawalsLimit,
        float $freeAmountLimit,
        float $feePercentage
    ) {
        $this->exchangeRateService = $exchangeRateService;
        $this->freeWithdrawalsLimit = $freeWithdrawalsLimit;
        $this->freeAmountLimit = $freeAmountLimit;
        $this->feePercentage = $feePercentage;
    }

    /**
     * @throws Exception
     */
    public function calculate(OperationDto $operation): float
    {
        $weekKey = $this->getWeekKey($operation->date);

        if (!isset($this->weeklyWithdrawals[$operation->userId][$weekKey])) {
            $this->weeklyWithdrawals[$operation->userId][$weekKey] = [
                'count' => 0,
                'total' => 0,
            ];
        }

        $withdrawalData = &$this->weeklyWithdrawals[$operation->userId][$weekKey];

        $convertedAmount = $this->convertToEur($operation);

        // First check free withdrawal count
        if (
            $withdrawalData['count'] < $this->freeWithdrawalsLimit &&
            ($withdrawalData['total'] + $convertedAmount) < $this->freeAmountLimit
        ) {
            $withdrawalData['count']++;
            $withdrawalData['total'] += $convertedAmount;

            return 0.0;
        }

        // Then check free amount limit
        if ($withdrawalData['total'] < $this->freeAmountLimit) {
            $remainingFreeAmount = $this->freeAmountLimit - $withdrawalData['total'];

            if ($convertedAmount <= $remainingFreeAmount) {
                $withdrawalData['total'] += $convertedAmount;
                return 0.0;
            } else {
                $chargeableAmount = $convertedAmount - $remainingFreeAmount;
                $withdrawalData['total'] += $convertedAmount;

                return $this->convertBackToOriginalCurrency($operation, ($chargeableAmount * $this->feePercentage) / 100);
            }
        }

        $withdrawalData['total'] += $operation->amount;
        $withdrawalData['count']++;

        return $this->convertBackToOriginalCurrency($operation, ($convertedAmount * $this->feePercentage) / 100);
    }

    /**
     * @throws Exception
     */
    private function getWeekKey(string $date): string
    {
        $dateTime = new DateTime($date);
        return $dateTime->format('o-W');
    }

    public function supports(OperationDto $operation): bool
    {
        return $operation->operationType === 'withdraw' && $operation->userType === 'private';
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function convertToEur(OperationDto $operation): float
    {
        if ($operation->currency === 'EUR') {
            return $operation->amount;
        }

        // Use cached rate or fetch new one if missing
        $rate = $this->exchangeRateService->getExchangeRate($operation->currency);

        if (!$rate) {
            throw new \RuntimeException("Missing exchange rate for {$operation->currency}");
        }

        return $operation->amount / $rate;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function convertBackToOriginalCurrency(OperationDto $operation, float $amount): float
    {
        if ($operation->currency === 'EUR') {
            return ceil($amount * 100) / 100;
        }

        $rate = $this->exchangeRateService->getExchangeRate($operation->currency);

        if (!$rate) {
            throw new \RuntimeException("Missing exchange rate for {$operation->currency}");
        }

        return ceil(($amount * $rate) * 100) / 100;
    }

}
