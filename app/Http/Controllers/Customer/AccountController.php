<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $orderCounts = [
            'pending' => $customer->orders()->where('status', 'pending')->count(),
            'processing' => $customer->orders()->where('status', 'processing')->count(),
            'shipped' => $customer->orders()->where('status', 'shipped')->count(),
            'completed' => $customer->orders()->where('status', 'completed')->count(),
        ];

        return view('customer.account.index', compact('customer', 'orderCounts'));
    }

    public function edit()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.account.edit', compact('customer'));
    }

    public function update(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('customers')->ignore($customer->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('customers')->ignore($customer->id)],
        ]);

        $customer->update($validated);

        return redirect()->route('customer.account')->with('success', 'Profil berhasil diperbarui.');
    }
}
