<?php

namespace App\Http\Controllers\Store;

use App\Models\Store;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\Store\StoreWalletService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WalletController extends BaseCoreController
{
    private $module = 'toko.wallet';

    protected $view = 'store.wallet';

    public function __construct(protected StoreWalletService $service) {}

    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');

        $stores = $isAdmin
            ? Store::orderBy('store_name')->get()
            : $user->stores()->orderBy('store_name')->get();

        $storeIds = $stores->pluck('id');

        $wallets = $stores->map(fn (Store $store) => $this->service->walletFor($store));

        $transactions = WalletTransaction::query()
            ->with('wallet.store')
            ->whereIn('wallet_id', $wallets->pluck('id'))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $withdrawals = WithdrawalRequest::query()
            ->with('wallet.store')
            ->when(! $isAdmin, fn ($query) => $query->whereIn('store_id', $storeIds))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view($this->view.'.index', [
            'wallets' => $wallets,
            'withdrawals' => $withdrawals,
            'transactions' => $transactions,
            'withdrawables' => $wallets->mapWithKeys(
                fn (Wallet $wallet) => [$wallet->id => $this->service->withdrawable($wallet)]
            ),
            'isAdmin' => $isAdmin,

            'title' => 'Saldo',
            'subtitle' => 'Data Saldo',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Saldo']],
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $store = Store::findOrFail($request->input('store_id', $user->stores()->value('id')));

        abort_unless($user->hasRole('Administrator') || $store->user_id === $user->id, 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->service->requestWithdrawal($this->service->walletFor($store), $validated);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Permintaan penarikan dikirim. Menunggu persetujuan admin.');
    }

    public function process(WithdrawalRequest $withdrawal, string $action)
    {
        try {
            $this->service->process($withdrawal, $action, Auth::user());
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Permintaan penarikan diperbarui.');
    }
}
