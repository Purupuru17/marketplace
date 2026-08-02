<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreWalletService
{
    public function walletFor(Store $store): Wallet
    {
        return $store->wallet()->firstOrCreate([], [
            'available_balance' => 0,
            'held_balance' => 0,
        ]);
    }

    public function hasTransaction(Order $order, string $type): bool
    {
        return WalletTransaction::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('type', $type)
            ->exists();
    }

    public function hold(Order $order): void
    {
        if ($this->hasTransaction($order, 'hold')) {
            return;
        }

        DB::transaction(function () use ($order) {
            $wallet = $this->lockedWallet($order->store);
            $amount = (float) $order->total;
            $before = (float) $wallet->held_balance;

            $wallet->update(['held_balance' => $before + $amount]);

            $this->record($wallet, 'hold', $amount, $before, $before + $amount, $order, "Dana pesanan {$order->order_no} ditahan.");
        });
    }

    public function settle(Order $order): void
    {
        if ($this->hasTransaction($order, 'release') || $this->hasTransaction($order, 'credit')) {
            return;
        }

        DB::transaction(function () use ($order) {
            $wallet = $this->lockedWallet($order->store);
            $amount = (float) $order->total;

            if ($this->hasTransaction($order, 'hold')) {
                $heldBefore = (float) $wallet->held_balance;
                $availableBefore = (float) $wallet->available_balance;

                $wallet->update([
                    'held_balance' => max(0, $heldBefore - $amount),
                    'available_balance' => $availableBefore + $amount,
                ]);

                $this->record($wallet, 'release', $amount, $availableBefore, $availableBefore + $amount, $order, "Dana pesanan {$order->order_no} dilepas ke saldo.");
            } else {
                $availableBefore = (float) $wallet->available_balance;

                $wallet->update(['available_balance' => $availableBefore + $amount]);

                $this->record($wallet, 'credit', $amount, $availableBefore, $availableBefore + $amount, $order, "Pembayaran COD pesanan {$order->order_no} diterima.");
            }
        });
    }

    public function reverseHold(Order $order): void
    {
        if (! $this->hasTransaction($order, 'hold') || $this->hasTransaction($order, 'debit')) {
            return;
        }

        DB::transaction(function () use ($order) {
            $wallet = $this->lockedWallet($order->store);
            $amount = (float) $order->total;
            $before = (float) $wallet->held_balance;

            $wallet->update(['held_balance' => max(0, $before - $amount)]);

            $this->record($wallet, 'debit', $amount, $before, max(0, $before - $amount), $order, "Dana pesanan {$order->order_no} dikembalikan (pembatalan).");
        });
    }

    public function withdrawable(Wallet $wallet): float
    {
        // Withdrawal berstatus 'approved' sudah memotong available_balance secara langsung
        // di process() — jadi jangan dihitung lagi di sini, cukup yang masih 'pending'.
        $reserved = (float) $wallet->withdrawalRequests()
            ->where('status', 'pending')
            ->sum('amount');

        return max(0, (float) $wallet->available_balance - $reserved);
    }

    public function requestWithdrawal(Wallet $wallet, array $data): WithdrawalRequest
    {
        $amount = (float) $data['amount'];

        if ($amount <= 0 || $amount > $this->withdrawable($wallet)) {
            throw ValidationException::withMessages([
                'amount' => 'Jumlah penarikan melebihi saldo yang dapat ditarik.',
            ]);
        }

        return WithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'store_id' => $wallet->store_id,
            'amount' => $amount,
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_name' => $data['account_name'],
            'status' => 'pending',
        ]);
    }

    public function process(WithdrawalRequest $withdrawal, string $action, User $user): WithdrawalRequest
    {
        abort_unless($user->hasRole('Administrator'), 403);

        return DB::transaction(function () use ($withdrawal, $action, $user) {
            if ($action === 'approve') {
                $this->throwUnlessPending($withdrawal);

                $wallet = Wallet::whereKey($withdrawal->wallet_id)->lockForUpdate()->firstOrFail();
                $amount = (float) $withdrawal->amount;
                $before = (float) $wallet->available_balance;

                if ($before < $amount) {
                    throw ValidationException::withMessages([
                        'amount' => 'Saldo tidak mencukupi untuk penarikan ini.',
                    ]);
                }

                $wallet->update(['available_balance' => $before - $amount]);

                $this->record($wallet, 'debit', $amount, $before, $before - $amount, $withdrawal, 'Penarikan dana disetujui.');

                $withdrawal->update([
                    'status' => 'approved',
                    'processed_by' => $user->id,
                    'processed_at' => now(),
                ]);
            } elseif ($action === 'reject') {
                $this->throwUnlessPending($withdrawal);

                $withdrawal->update([
                    'status' => 'rejected',
                    'processed_by' => $user->id,
                    'processed_at' => now(),
                ]);
            } elseif ($action === 'complete') {
                if ($withdrawal->status !== 'approved') {
                    throw ValidationException::withMessages([
                        'status' => 'Hanya permintaan yang disetujui yang dapat diselesaikan.',
                    ]);
                }

                $withdrawal->update([
                    'status' => 'completed',
                    'processed_by' => $user->id,
                    'processed_at' => now(),
                ]);
            } else {
                abort(404);
            }

            return $withdrawal->refresh()->load('wallet.store');
        });
    }

    protected function throwUnlessPending(WithdrawalRequest $withdrawal): void
    {
        if ($withdrawal->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Permintaan sudah diproses.',
            ]);
        }
    }

    protected function lockedWallet(Store $store): Wallet
    {
        $wallet = $this->walletFor($store);

        return Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
    }

    protected function record(Wallet $wallet, string $type, float $amount, float $before, float $after, Model $reference, string $notes): void
    {
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'reference_type' => $reference::class,
            'reference_id' => $reference->id,
            'notes' => $notes,
        ]);
    }
}
