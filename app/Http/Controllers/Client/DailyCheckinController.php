<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\DailyCheckinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyCheckinController extends Controller
{
    public function status(Request $request, DailyCheckinService $dailyCheckin): JsonResponse
    {
        return response()->json($dailyCheckin->calendar($request->user()));
    }

    public function claim(Request $request, DailyCheckinService $dailyCheckin): JsonResponse
    {
        return response()->json($dailyCheckin->claim($request->user()));
    }
}
