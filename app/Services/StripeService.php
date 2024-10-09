<?php

namespace App\Services;

use App\Traits\ConsumeExternalServices;
use Illuminate\Http\Request;

class StripeService
{
    use ConsumeExternalServices;

    protected string $baseUri;
    protected string $key;
    protected string $secret;
    protected array $plans;

    public function __construct()
    {
        $this->baseUri = config('services.stripe.base_uri');
        $this->key = config('services.stripe.key');
        $this->secret = config('services.stripe.secret');
        $this->plans = config('services.stripe.plans');
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
        return "Bearer $this->secret";
    }

    public function handlePayment(Request $request)
    {
        $request->validate([
            'paymentMethod' => 'required'
        ]);

        $intent = $this->createIntent($request->value, $request->currency, $request->paymentMethod);
        session()->put('paymentIntentId', $intent->id);

        return redirect()->route('payments.approval');
    }

    public function handleApproval()
    {
        if (!session()->has('paymentIntentId')) {
            return redirect()
                ->route('dashboard')
                ->withErrors('No se pudo confirmar el pago. Favor de intentar de nuevo');
        }

        $paymentIntentId = session()->get('paymentIntentId');
        $confirmation = $this->confirmPayment($paymentIntentId);

        if ($confirmation->status === 'requires_action') {
            $clientSecret = $confirmation->client_secret;

            return view('stripe.3d-secure')
                ->with([
                    'clientSecret' => $clientSecret,
                ]);
        }

        if ($confirmation->status === 'succeeded') {
            $currency = strtoupper($confirmation->currency);
            $amount = $confirmation->amount / $this->resolveFactor($currency);

            return redirect()
                ->route('dashboard')
                ->withSuccess([
                    'payment' => "Gracias. Recibimos tu pago de $amount $currency."
                ]);
        }

        return redirect()
            ->route('dashboard')
            ->withErrors('Algo sucedió al realizar su pago. Favor de contactar al administrador');
    }

    public function handleSubscription(Request $request)
    {
        $customer = $this->createCustomer(
            $request->user()->name,
            $request->user()->email,
            $request->paymentMethod
        );

        $subscription = $this->createSubscription(
            $customer->id,
            $request->paymentMethod,
            $this->plans[$request->plan]
        );

        if ($subscription->status === 'active') {
            session()->put('subscriptionId', $subscription->id);

            return redirect()
                ->route('subscribe.approval', [
                    'plan' => $request->plan,
                    'subscription_id' => $subscription->id,
                ]);
        }

        $paymentIntent = $subscription->latest_invoice->payment_intent;

        if ($paymentIntent->status === 'requires_action') {
            $clientSecret = $paymentIntent->client_secret;

            session()->put('subscriptionId', $subscription->id);

            return view('stripe.3d-secure-subscription')
                ->with([
                    'clientSecret' => $clientSecret,
                    'plan' => $request->plan,
                    'paymentMethod' => $request->paymentMethod,
                    'subscriptionId' => $subscription->id,
                ]);
        }

        return redirect()
            ->route('subscribe.show')
            ->withErrors('Algo sucedió al realizar su pago de la suscripción. Favor de contactar al administrador');
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

    public function createIntent($value, $currency, $paymentMethod)
    {
        return $this->makeRequest(
            'POST',
            '/v1/payment_intents',
            formParams: [
                'amount' => round($value * $this->resolveFactor($currency)),
                'currency' => strtolower($currency),
                'payment_method' => $paymentMethod,
                'confirmation_method' => 'manual'
            ]
        );
    }

    public function confirmPayment($paymentIntentId)
    {
        return $this->makeRequest(
            'POST',
            "/v1/payment_intents/$paymentIntentId/confirm",
            formParams: [
                'return_url' => route('dashboard'),
                'use_stripe_sdk' => json_encode(true)
            ],
        );
    }

    public function createCustomer(string $name, string $email, $paymentMethod)
    {
        return $this->makeRequest(
            'POST',
            '/v1/customers',
            formParams: [
                'name' => $name,
                'email' => $email,
                'payment_method' => $paymentMethod,
            ]
        );
    }

    public function createSubscription($customerId, $paymentMethod, $priceId)
    {
        return $this->makeRequest(
            'POST',
            '/v1/subscriptions',
            formParams: [
                'customer' => $customerId,
                'items' => [
                    ['price' => $priceId]
                ],
                'default_payment_method' => $paymentMethod,
                'expand' => ['latest_invoice.payment_intent']
            ]
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
