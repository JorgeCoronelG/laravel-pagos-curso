<?php

namespace Database\Seeders;

use App\Models\PaymentPlatform;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentPlatformTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentPlatform::query()
            ->create([
                'name' => 'Paypal',
                'image' => 'img/payment-platform/paypal.jpg',
                'subscriptions_enabled' => true
            ]);

        PaymentPlatform::query()
            ->create([
                'name' => 'Stripe',
                'image' => 'img/payment-platform/stripe.jpg',
                'subscriptions_enabled' => true
            ]);

        /*PaymentPlatform::query()
            ->create([
                'name' => 'MercadoPago',
                'image' => 'img/payment-platform/mercadopago.jpg',
            ]);*/

        PaymentPlatform::query()
            ->create([
                'name' => 'PayU',
                'image' => 'img/payment-platform/payu.jpg',
            ]);
    }
}
