<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlatform;
use App\Models\Plan;
use App\Models\Subscription;
use App\Resolvers\PaymentPlatformResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    protected $paymentPlatformResolver;

    public function __construct(
        PaymentPlatformResolver $paymentPlatformResolver
    ) {
        $this->middleware(['auth', 'unsubscribe']);
        $this->paymentPlatformResolver = $paymentPlatformResolver;
    }

    public function show(): View
    {
        $paymentPlatforms = PaymentPlatform::query()
            ->where('subscriptions_enabled', true)
            ->get();
        $plans = Plan::all();

        return view('subscribe')
            ->with([
                'plans' => $plans,
                'paymentPlatforms' => $paymentPlatforms,
            ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'plan' => ['required', 'exists:plans,slug'],
            'paymentPlatform' => ['required', 'exists:payment_platforms,id'],
        ];
        $request->validate($rules);

        $paymentPlatform = $this->paymentPlatformResolver->resolveService($request->paymentPlatform);
        session()->put('subscriptionPlatformId', $request->paymentPlatform);
        return $paymentPlatform->handleSubscription($request);
    }

    public function approval(Request $request)
    {
        $rules = [
            'plan' => ['required', 'exists:plans,slug'],
        ];
        $request->validate($rules);

        if (session()->has('subscriptionPlatformId')) {
            $paymentPlatform = $this->paymentPlatformResolver->resolveService(session()->get('subscriptionPlatformId'));

            if (!$paymentPlatform->validateSubscription($request)) {
                return redirect()
                    ->route('subscribe.show')
                    ->withErrors('No pudimos comprobar tu suscripción. Inténtalo de nuevo');
            }

            $plan = Plan::query()->where('slug', $request->plan)->firstOrFail();
            $user = $request->user();

            Subscription::query()->create([
                'active_until' => now()->addDays($plan->duration_in_days),
                'user_id' => $user->id,
                'plan_id' => $plan->id
            ]);

            return redirect()
                ->route('dashboard')
                ->withSuccess([
                    'payment' => "Gracias, $user->name. Tienes la suscripción $plan->slug"
                ]);
        }

        return redirect()
            ->route('subscribe.show')
            ->withErrors('No pudimos comprobar tu suscripción. Inténtalo de nuevo');
    }

    public function cancelled()
    {
        return redirect()
            ->route('subscribe.show')
            ->withErrors('Cancelaste el proceso de subscripción. Regresa cuando estés listo :)');
    }
}
