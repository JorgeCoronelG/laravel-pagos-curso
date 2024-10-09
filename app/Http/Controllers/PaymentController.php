<?php

namespace App\Http\Controllers;

use App\Resolvers\PaymentPlatformResolver;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PaymentController extends Controller
{
    protected $paymentPlatformResolver;

    public function __construct(
        PaymentPlatformResolver $paymentPlatformResolver
    ) {
        $this->middleware('auth');

        $this->paymentPlatformResolver = $paymentPlatformResolver;
    }

    /**
     * @throws \Exception
     */
    public function pay(Request $request)
    {
        $rules = [
            'value' => ['required', 'numeric', 'min:5'],
            'currency' => ['required', 'exists:currencies,iso'],
            'paymentPlatform' => ['required', 'exists:payment_platforms,id']
        ];

        $request->validate($rules);

        $paymentPlatform = $this->paymentPlatformResolver->resolveService($request->get('paymentPlatform'));
        session()->put('paymentPlatformId', $request->get('paymentPlatform'));

        if ($request->user()->hasActiveSubscription()) {
            $request->value = round($request->value * .9, 2);
        }

        return $paymentPlatform->handlePayment($request);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function approval()
    {
        if (!session()->has('paymentPlatformId')) {
            return redirect()
                ->route('dashboard')
                ->withErrors('No se pudo obtener la plataforma de pago. Vuelva a intentar, por favor');
        }

        $paymentPlatform = $this->paymentPlatformResolver->resolveService(session()->get('paymentPlatformId'));
        return $paymentPlatform->handleApproval();
    }

    public function cancelled()
    {
        return redirect()
            ->route('dashboard')
            ->withErrors('Pago cancelado');
    }
}
