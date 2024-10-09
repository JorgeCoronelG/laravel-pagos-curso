<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Completa los pasos de seguridad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col">
                        <p>
                            Necesitas seguir algunos pasos para completar el pago.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ config('services.stripe.key') }}');

    stripe.confirmCardPayment("{{ $clientSecret }}", { payment_method: "{{ $paymentMethod }}" })
        .then(function (result) {
            if (result.error) {
                window.location.replace("{{ route('subscribe.cancelled') }}");
            } else {
                window.location.replace("{!! route('subscribe.approval', [
                    'plan' => $plan, 'subscription_id' => $subscriptionId
                ]) !!}");
            }
        });
</script>
