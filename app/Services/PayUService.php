<?php

namespace App\Services;

use App\Traits\ConsumeExternalServices;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayUService
{
    use ConsumeExternalServices;

    protected string $baseUri;
    protected string $key;
    protected string $secret;
    protected string $baseCurrency;
    protected string $merchantId;
    protected string $accountId;
    protected CurrencyConversionService $converter;

    public function __construct(
        CurrencyConversionService $converter
    ) {
        $this->baseUri = config('services.payu.base_uri');
        $this->key = config('services.payu.key');
        $this->secret = config('services.payu.secret');
        $this->baseCurrency = strtoupper(config('services.payu.base_currency'));
        $this->merchantId = config('services.payu.merchant_id');
        $this->accountId = config('services.payu.account_id');

        $this->converter = $converter;
    }

    public function resolveAuthorization(&$queryParams, &$formParams, array &$headers): void
    {
        $formParams['merchant']['apiLogin'] = $this->secret;
        $formParams['merchant']['apiKey'] = $this->key;
    }

    public function decodeResponse($response): object
    {
        return json_decode($response);
    }

    public function handlePayment(Request $request)
    {
        $request->validate([
            'payu_card' => 'required',
            'payu_cvc' => 'required',
            'payu_year' => 'required',
            'payu_month' => 'required',
            'payu_network' => 'required',
            'payu_name' => 'required',
            'payu_email' => 'required|string|email',
        ]);

        $payment = $this->createPayment(
            $request->get('value'),
            $request->get('currency'),
            $request->get('payu_name'),
            $request->get('payu_email'),
            $request->get('payu_card'),
            $request->get('payu_cvc'),
            $request->get('payu_year'),
            $request->get('payu_month'),
            $request->get('payu_network')
        );

        if ($payment->code !== 'ERROR' && $payment->transactionResponse->state == 'APPROVED') {
            $name = $request->get('payu_name');
            $amount = $request->get('value');
            $currency = strtoupper($request->get('currency'));

            return redirect()
                ->route('dashboard')
                ->withSuccess([
                    'payment' => "Gracias, $name. Recibimos tu pago $amount $currency"
                ]);
        }

        return redirect()
            ->route('dashboard')
            ->withErrors('Algo sucedió al realizar su pago. Favor de contactar al administrador');
    }

    public function createPayment(
        $value, $currency, $name, $email, $card, $cvc, $year, $month, $network, $installments = 1, $paymentCountry = 'MX'
    ) {
        return $this->makeRequest(
            'POST',
            '/payments-api/4.0/service.cgi',
            [],
            [
                'language' => $language = config('app.locale'),
                'command' => 'SUBMIT_TRANSACTION',
                'test' => json_encode(true),
                'transaction' => [
                    'type' => 'AUTHORIZATION_AND_CAPTURE',
                    'paymentMethod' => strtoupper($network),
                    'paymentCountry' => strtoupper($paymentCountry),
                    'deviceSessionId' => session()->getId(),
                    'ipAddress' => request()->ip(),
                    'userAgent' => request()->userAgent(),
                    'creditCard' => [
                        'number' => $card,
                        'securityCode' => $cvc,
                        'expirationDate' => "$year/$month",
                        'name' => "APPROVED",
                    ],
                    'extraParameters' => [
                        'INSTALLMENTS_NUMBER' => $installments
                    ],
                    'payer' => [
                        'fullName' => $name,
                        'emailAddress' => $email
                    ],
                    'order' => [
                        'accountId' => $this->accountId,
                        'referenceCode' => $reference = Str::random(12),
                        'description' => "Testing PayU",
                        'language' => $language,
                        'signature' => $this->generateSignature($reference, $value),
                        'additionalValues' => [
                            'TX_VALUE' => [
                                'value' => $value,
                                'currency' => $this->baseCurrency
                            ]
                        ],
                        'buyer' => [
                            'fullName' => $name,
                            'emailAddress' => $email,
                            'shippingAddress' => [
                                'street1' => '',
                                'city' => '',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'Accept' => 'application/json',
            ],
            true
        );
    }

    public function resolveFactor(string $currency)
    {
        return $this->converter->convertCurrency($currency, $this->baseCurrency);
    }

    public function generateSignature($referenceCode, $value): string
    {
        return md5("$this->key~$this->merchantId~$referenceCode~$value~$this->baseCurrency");
    }
}
