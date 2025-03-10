<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\{Exception\ClientExceptionInterface,
    Exception\DecodingExceptionInterface,
    Exception\RedirectionExceptionInterface,
    Exception\ServerExceptionInterface,
    Exception\TransportExceptionInterface};

class ExchangeRateService
{
    private ApiService $apiService;
    private string $endpoint;
    private string $accessKey;

    public function __construct(ApiService $apiService, string $endpoint, string $accessKey)
    {
        $this->apiService = $apiService;
        $this->endpoint = $endpoint;
        $this->accessKey = $accessKey;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getExchangeRate(string $currency): ?float
    {
        $exchangeRateData = $this->apiService->get($this->endpoint, [
            'access_key' => $this->accessKey
        ]);

        if (!isset($exchangeRateData['rates'][$currency])) {
            throw new \RuntimeException("Exchange rate for {$currency} not found.");
        }

        return (float) $exchangeRateData['rates'][$currency];
    }
}
