<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        return view('customer.account.index', compact('customer'));
    }
}
