<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\PaymentPlatform;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $currencies = Currency::all();
        $paymentPlatforms = PaymentPlatform::all();

        return view('dashboard')
            ->with([
                'currencies' => $currencies,
                'paymentPlatforms' => $paymentPlatforms,
            ]);
    }
}
