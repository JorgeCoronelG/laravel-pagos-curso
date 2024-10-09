<label class="mt-3">
    Card details:
</label>

<div class="flex flex-col gap-3 mt-3">
    <div class="flex flex-row gap-3">
        <div class="flex-auto">
            <x-input-label for="payu_card" :value="__('Número de la tarjeta')" />
            <x-text-input id="payu_card"
                          name="payu_card"
                          class="block mt-1 w-full"
                          type="text" />
        </div>

        <div class="w-20">
            <x-input-label for="payu_cvc" :value="__('CVC')" />
            <x-text-input id="payu_cvc"
                          name="payu_cvc"
                          class="block mt-1 w-full"
                          type="text" />
        </div>

        <div class="w-20">
            <x-input-label for="payu_month" :value="__('MM')" />
            <x-text-input id="payu_month"
                          name="payu_month"
                          class="block mt-1 w-full"
                          type="text" />
        </div>

        <div class="w-20">
            <x-input-label for="payu_year" :value="__('YY')" />
            <x-text-input id="payu_year"
                          name="payu_year"
                          class="block mt-1 w-full"
                          type="text"/>
        </div>
    </div>

    <div class="flex flex-row gap-3">
        <div class="flex-auto">
            <x-input-label for="payu_name" :value="__('Nombre')" />
            <x-text-input id="payu_name"
                          class="block mt-1 w-full"
                          type="text"
                          name="payu_name" />
        </div>

        <div class="flex-auto">
            <x-input-label for="payu_email" :value="__('Correo')" />
            <x-text-input id="payu_email"
                          class="block mt-1 w-full"
                          type="email"
                          name="payu_email" />
        </div>

        <div class="flex-auto">
            <x-input-label for="network" :value="__('Tarjeta')" />
            <x-select id="network"
                      class="block mt-1 w-full"
                      name="payu_network">
                <x-slot name="options">
                    <option value="visa">VISA</option>
                    <option value="amex">AMEX</option>
                    <option value="diners">DINERS</option>
                    <option value="mastercard">MASTERCARD</option>
                </x-slot>
            </x-select>
        </div>
    </div>

    <div class="flex-auto">
        <small>
            El pago será convertido a moneda {{ strtoupper(config('services.payu.base_currency')) }}
        </small>
    </div>
</div>
