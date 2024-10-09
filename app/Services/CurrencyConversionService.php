<?php

namespace App\Services;

use App\Traits\ConsumeExternalServices;
use Illuminate\Http\Request;

class CurrencyConversionService
{
    use ConsumeExternalServices;

    protected string $baseUri;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUri = config('services.currency_conversion.base_uri');
        $this->apiKey = config('services.currency_conversion.api_key');
    }

    public function resolveAuthorization(&$queryParams, &$formParams, array &$headers): void
    {
        $queryParams['api_key'] = $this->resolveAccessToken();
    }

    public function decodeResponse($response): object
    {
        return json_decode($response);
    }

    public function resolveAccessToken(): string
    {
        return $this->apiKey;
    }

    public function convertCurrency(string $from, string $to)
    {
        $response = $this->makeRequest(
            'GET',
            '/api/v7/convert',
            [
                'q' => "{$from}_$to",
                'compact' => 'ultra'
            ]
        );
        return $response->{strtoupper("{$from}_$to")};
    }
}
