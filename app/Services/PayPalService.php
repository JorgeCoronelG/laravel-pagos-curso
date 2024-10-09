<?php

namespace App\Services;

use App\Traits\ConsumeExternalServices;
use Illuminate\Http\Request;

class PayPalService
{
    use ConsumeExternalServices;

    protected string $baseUri;
    protected string $clientId;
    protected string $clientSecret;
    protected array $plans;

    public function __construct()
    {
        $this->baseUri = config('services.paypal.base_uri');
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->plans = config('services.paypal.plans');
    }

    public function resolveAuthorization(&$queryParams, &$formParams, array &$headers): void
    {
        $headers['Authorization'] = $this->resolveAccessToken();
    }

    public function decodeResponse($response): object
    {
        return json_decode($response);
    }

    public function resolveAccessToken(): string
    {
        $credentials = base64_encode("$this->clientId:$this->clientSecret");
        return "Basic $credentials";
    }

    public function handlePayment(Request $request)
    {
        $order = $this->createOrder($request->value, $request->currency);
        $orderLinks = collect($order->links);
        $approve = $orderLinks->where('rel', 'payer-action')->first();
        session()->put('approvalId', $order->id);
        return redirect($approve->href);
    }

    public function handleApproval()
    {
        if (!session()->has('approvalId')) {
            return redirect()
                ->route('dashboard')
                ->withErrors('No se puede capturar el pago. Intente nuevamente, por favor');
        }

        $approvalId = session()->get('approvalId');
        $payment = $this->capturePayment($approvalId);
        $name = $payment->payer->name->given_name;
        $amount = $payment->purchase_units[0]->payments->captures[0]->amount->value;
        $currency = $payment->purchase_units[0]->payments->captures[0]->amount->currency_code;

        return redirect()
            ->route('dashboard')
            ->withSuccess([
                'payment' => "Gracias, $name. Recibimos tu pago de $amount $currency."
            ]);
    }

    public function handleSubscription(Request $request)
    {
        $subscription = $this->createSubscription(
            $request->plan,
            $request->user()->name,
            $request->user()->email,
        );
        $subscriptionLinks = collect($subscription->links);
        $approve = $subscriptionLinks->where('rel', 'approve')->first();
        session()->put('subscriptionId', $subscription->id);
        return redirect($approve->href);
    }

    public function validateSubscription(Request $request): bool
    {
        if (!session()->has('subscriptionId')) {
            return false;
        }

        $subscriptionId = session()->get('subscriptionId');

        session()->forget('subscriptionId');

        return $request->subscription_id === $subscriptionId;
    }

    public function createOrder(float $value, string $currency)
    {
        $factor = $this->resolveFactor($currency);

        return $this->makeRequest(
            'POST',
            '/v2/checkout/orders',
            [],
            [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => strtoupper($currency),
                            'value' => round($value * $factor) / $factor
                        ]
                    ]
                ],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                            'brand_name' => config('app.name'),
                            'locale' => 'es-MX',
                            'shipping_preference' => 'NO_SHIPPING',
                            'user_action' => 'PAY_NOW',
                            'return_url' => route('payments.approval'),
                            'cancel_url' => route('payments.cancelled'),
                        ]
                    ]
                ]
            ],
            [],
            true
        );
    }

    public function capturePayment($approvalId)
    {
        return $this->makeRequest('POST', "/v2/checkout/orders/$approvalId/capture", headers: [
            'Content-Type' => 'application/json'
        ]);
    }

    public function createSubscription(string $planSlug, string $name, string $email)
    {
        return $this->makeRequest(
            'POST',
            '/v1/billing/subscriptions',
            [],
            [
                'plan_id' => $this->plans[$planSlug],
                'suscriber' => [
                    'name' => [
                        'given_name' => $name,
                    ],
                    'email_address' => $email
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => route('subscribe.approval', ['plan' => $planSlug]),
                    'cancel_url' => route('subscribe.cancelled'),
                ]
            ],
            [],
            true
        );
    }

    public function resolveFactor(string $currency): int
    {
        $zeroDecimalCurrencies = ['JPY'];

        return (in_array(strtoupper($currency), $zeroDecimalCurrencies))
            ? 1
            : 100;
    }
}
