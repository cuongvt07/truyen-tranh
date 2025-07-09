<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Google\Service\AlertCenter\Settings;

class PayPointController extends Controller
{
    /**
     * Display the pay points page.
     */
    public function index()
    {
        return view('client.paypoints.pay-points');
    }
}