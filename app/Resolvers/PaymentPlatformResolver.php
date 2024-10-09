<?php

namespace App\Resolvers;

use App\Models\PaymentPlatform;

class PaymentPlatformResolver
{
    protected $paymentPlatforms;

    public function __construct()
    {
        $this->paymentPlatforms = PaymentPlatform::all();
    }

    /**
     * @throws \Exception
     */
    public function resolveService(int $paymentPlatformId)
    {
        $name = strtolower($this->paymentPlatforms->firstWhere('id', $paymentPlatformId)->name);
        $service = config("services.$name.class");
        if ($service) {
            return resolve($service);
        }

        throw new \Exception('Plataforma no está en la configuración');
    }
}
