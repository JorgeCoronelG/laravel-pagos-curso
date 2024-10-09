<label class="mt-3">
    Detalles de la tarjeta:
</label>

<div class="flex flex-col gap-3 mt-3" id="form-checkout">
    <div class="flex flex-row gap-3">
        <div class="flex-auto">
            <x-input-label for="form-checkout__cardNumber" :value="__('Número de la tarjeta')" />
            <x-text-input id="form-checkout__cardNumber"
                          class="block mt-1 w-full"
                          type="text"
                          required />
        </div>

        <div class="w-20 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
             id="form-checkout__securityCode"></div>

        <div class="w-32 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
             id="form-checkout__expirationDate"></div>
    </div>

    <div class="flex flex-row">
        <div class="flex-auto">
            <x-input-label for="form-checkout__cardholderName" :value="__('Nombre')" />
            <x-text-input id="form-checkout__cardholderName"
                          class="block mt-1 w-full"
                          type="text"
                          required />
        </div>

        <div class="flex-auto">
            <x-input-label for="form-checkout__cardholderEmail" :value="__('Correo')" />
            <x-text-input id="form-checkout__cardholderEmail"
                          class="block mt-1 w-full"
                          type="email"
                          name="email"
                          required />
        </div>
    </div>

    <div class="flex flex-row gap-3">
        <select id="form-checkout__issuer"
                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></select>
        <select id="form-checkout__installments"
                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></select>
    </div>

    <div class="flex flex-row">
        <div class="flex-col gap-3">
            <small>
                Tu pago será convertido a {{ strtoupper(config('services.mercadopago.base_currency')) }}
            </small>

            <small id="paymentErrors">

            </small>
        </div>
    </div>

    <input type="hidden" id="cardNetwork" name="cardNetwork">
</div>

@push('scripts')
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        const mp = new MercadoPago("{{ config('services.mercadopago.key') }}");


        const cardForm = mp.cardForm({
            amount: "100.5",
            iframe: true,
            form: {
                id: "form-checkout",
                cardNumber: {
                    id: "form-checkout__cardNumber",
                    placeholder: "Numero de tarjeta",
                },
                expirationDate: {
                    id: "form-checkout__expirationDate",
                    placeholder: "MM/YY",
                },
                securityCode: {
                    id: "form-checkout__securityCode",
                    placeholder: "Código de seguridad",
                },
                cardholderName: {
                    id: "form-checkout__cardholderName",
                    placeholder: "Titular de la tarjeta",
                },
                issuer: {
                    id: "form-checkout__issuer",
                    placeholder: "Banco emisor",
                },
                installments: {
                    id: "form-checkout__installments",
                    placeholder: "Cuotas",
                },
                cardholderEmail: {
                    id: "form-checkout__cardholderEmail",
                    placeholder: "E-mail",
                },
            },
            callbacks: {
                onFormMounted: error => {
                    if (error) return console.warn("Form Mounted handling error: ", error);
                    console.log("Form mounted");
                },
                onSubmit: event => {
                    event.preventDefault();

                    const {
                        paymentMethodId: payment_method_id,
                        issuerId: issuer_id,
                        cardholderEmail: email,
                        amount,
                        token,
                        installments,
                        identificationNumber,
                        identificationType,
                    } = cardForm.getCardFormData();

                    fetch("/process_payment", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            token,
                            issuer_id,
                            payment_method_id,
                            transaction_amount: Number(amount),
                            installments: Number(installments),
                            description: "Descripción del producto",
                            payer: {
                                email,
                                identification: {
                                    type: identificationType,
                                    number: identificationNumber,
                                },
                            },
                        }),
                    });
                },
                onFetching: (resource) => {
                    console.log("Fetching resource: ", resource);
                }
            },
        });
    </script>
@endpush
