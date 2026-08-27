<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Customer\CustomerService;
use App\Services\Customer\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected CustomerService $service,
        protected LoyaltyService $loyaltyService
    ) {}

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $customer = $this->service->register($data);

        return response()->json([
            'data' => [
                'customer' => $this->payload($customer),
                'token' => $customer->createToken('react')->plainTextToken,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::where('email', $validated['email'])->first();

        if (! $customer || ! Hash::check($validated['password'], $customer->password)) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi salah.']);
        }

        if (! $customer->isActive()) {
            throw ValidationException::withMessages(['email' => 'Akun tidak aktif.']);
        }

        return response()->json([
            'data' => [
                'customer' => $this->payload($customer),
                'token' => $customer->createToken('react')->plainTextToken,
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'data' => $this->payload($request->user('api-customer')),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user('api-customer')->currentAccessToken()->delete();

        return response()->json([
            'data' => ['message' => 'Logout berhasil.'],
        ]);
    }

    public function update(Request $request)
    {
        $customer = $request->user('api-customer');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $customer->update($data);

        return response()->json([
            'data' => $this->payload($customer->fresh()),
        ]);
    }

    public function changePassword(Request $request)
    {
        $customer = $request->user('api-customer');

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini salah.',
            ]);
        }

        $customer->update([
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'data' => ['message' => 'Kata sandi diperbarui.'],
        ]);
    }

    protected function payload(Customer $customer): array
    {
        $orderCounts = Order::query()
            ->where('customer_id', $customer->id)
            ->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', ['pending', 'processing', 'ready', 'completed'])
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'points' => $this->loyaltyService->availablePoints($customer),
            'level' => $customer->level?->name,
            'order_counts' => [
                'pending' => (int) ($orderCounts['pending'] ?? 0),
                'processing' => (int) ($orderCounts['processing'] ?? 0),
                'ready' => (int) ($orderCounts['ready'] ?? 0),
                'completed' => (int) ($orderCounts['completed'] ?? 0),
            ],
        ];
    }
}
