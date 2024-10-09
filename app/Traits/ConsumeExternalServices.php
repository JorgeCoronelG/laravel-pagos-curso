<?php

namespace App\Traits;

use GuzzleHttp\Client;

trait ConsumeExternalServices
{
    public function makeRequest(
        string $method, string $url, array $queryParams = [], $formParams = [], $headers = [], bool $isJsonRequest = false
    ) {
        $client = new Client([
            'verify' => false,
            'base_uri' => $this->baseUri
        ]);

        if (method_exists($this, 'resolveAuthorization')) {
            $this->resolveAuthorization($queryParams, $formParams, $headers);
        }

        $response = $client->request($method, $url, [
            $isJsonRequest ? 'json' : 'form_params' => $formParams,
            'headers' => $headers,
            'query' => $queryParams
        ]);

        $response = $response->getBody()->getContents();

        if (method_exists($this, 'decodeResponse')) {
            $response = $this->decodeResponse($response);
        }

        return $response;
    }
}
