<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subscripción') }}
        </h2>
    </x-slot>

    @if(isset($errors) && $errors->any())
        <div class="bg-red-700 dark:text-white">
            <ul class="list-disc p-4 mx-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session()->has('success'))
        <div class="bg-green-700 dark:text-white">
            <ul class="list-disc p-4 mx-4">
                @foreach(session()->get('success') as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('subscribe.store') }}" id="paymentForm">
                        @csrf

                        <div class="flex flex-col gap-3">
                            <div class="flex flex-col gap-3">
                                <x-input-label :value="__('Seleccione el plan deseado')" />

                                <div class="flex flex-row gap-3">
                                    @foreach($plans as $plan)
                                        <div class="inline-flex items-center">
                                            <label class="relative flex items-center cursor-pointer"
                                                   for="{{ strtolower($plan->slug) }}">
                                                <input name="plan"
                                                       type="radio"
                                                       class="peer h-5 w-5 cursor-pointer appearance-none rounded-full border border-slate-300 checked:border-slate-400 transition-all"
                                                       id="{{ strtolower($plan->slug) }}"
                                                       value="{{ $plan->slug }}"
                                                       required>
                                                <span class="absolute bg-slate-800 w-3 h-3 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></span>
                                            </label>
                                            <label class="ml-2 text-slate-600 cursor-pointer text-sm dark:text-gray-300"
                                                   for="{{ strtolower($plan->slug) }}">
                                                {{ $plan->slug }} - {{ $plan->visual_price }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <x-input-label :value="__('Seleccione la plataforma deseada')" />

                                <div class="flex flex-row gap-3">
                                    @foreach($paymentPlatforms as $paymentPlatform)
                                        <div class="inline-flex items-center">
                                            <label class="relative flex items-center cursor-pointer"
                                                   for="{{ strtolower($paymentPlatform->name) }}">
                                                <input name="paymentPlatform"
                                                       type="radio"
                                                       onclick="platformOnChange(event.target.id)"
                                                       class="peer h-5 w-5 cursor-pointer appearance-none rounded-full border border-slate-300 checked:border-slate-400 transition-all"
                                                       id="{{ strtolower($paymentPlatform->name) }}"
                                                       value="{{ $paymentPlatform->id }}"
                                                       required>
                                                <span class="absolute bg-slate-800 w-3 h-3 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></span>
                                            </label>
                                            <label class="ml-2 text-slate-600 cursor-pointer text-sm dark:text-gray-300"
                                                   for="{{ strtolower($paymentPlatform->name) }}">
                                                {{ $paymentPlatform->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex flex-col mt-2">
                                    @foreach($paymentPlatforms as $paymentPlatform)
                                        <div id="{{ strtolower($paymentPlatform->name) }}-collapse"
                                             class="platform-collapse hidden">
                                            @includeIf('components.' . strtolower($paymentPlatform->name) . '-collapse')
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <x-primary-button class="ms-4" id="payButton">
                                    {{ __('Suscribirse') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    const platformOnChange = (value) => {
        const platforms = document.querySelectorAll('.platform-collapse');
        const selectedPlatform = document.querySelector(`#${value}-collapse`);

        platforms.forEach(platform => {
            platform.classList.add('hidden');
        });
        selectedPlatform.classList.remove('hidden');
    }
</script>
