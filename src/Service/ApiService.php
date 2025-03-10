<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\{Exception\ClientExceptionInterface,
    Exception\DecodingExceptionInterface,
    Exception\RedirectionExceptionInterface,
    Exception\ServerExceptionInterface,
    Exception\TransportExceptionInterface,
    HttpClientInterface};

class ApiService
{
    private const HTTP_TIMEOUT = 5;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function get(string $url, array $queryParams = []): array
    {
        $response = $this->httpClient->request('GET', $url, [
            'query' => $queryParams,
            'timeout' => self::HTTP_TIMEOUT,
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new \RuntimeException("Failed to fetch data: HTTP $statusCode");
        }

        return $response->toArray();
    }
}
