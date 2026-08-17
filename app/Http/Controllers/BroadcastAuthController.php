<?php

namespace App\Http\Controllers;

use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->setUserResolver(function () {
            return Auth::guard('api-customer')->user()
                ?? Auth::guard('customer')->user()
                ?? Auth::guard('web')->user();
        });

        return app(BroadcastController::class)->authenticate($request);
    }
}
