<?php

namespace App\Services;

use App\Traits\ConsumeExternalServices;
use Illuminate\Http\Request;

class MercadoPagoService
{
    use ConsumeExternalServices;

    protected string $baseUri;
    protected string $key;
    protected string $secret;
    protected string $baseCurrency;

    public function __construct()
    {
        $this->baseUri = config('services.mercadopago.base_uri');
        $this->key = config('services.mercadopago.key');
        $this->secret = config('services.mercadopago.secret');
        $this->baseCurrency = config('services.mercadopago.base_currency');
    }

    public function resolveAuthorization(&$queryParams, &$formParams, array &$headers): void
    {
        //
    }

    public function decodeResponse($response): object
    {
        return json_decode($response);
    }

    public function resolveAccessToken(): string
    {
        return '';
    }

    public function handlePayment(Request $request)
    {
        //
    }

    public function resolveFactor(string $currency)
    {
        //
    }
}
