<?php

namespace App\Console\Commands;

use App\Actions\Wallet\ReconcileWallets;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('wallets:reconcile')]
#[Description('Check cached wallet balances against the ledger')]
class ReconcileWalletBalances extends Command
{
    public function handle(ReconcileWallets $reconcile): int
    {
        $mismatches = $reconcile->handle();

        if ($mismatches === []) {
            $this->info('Semua wallet sesuai ledger.');

            return self::SUCCESS;
        }

        $this->table(['user_id', 'bucket', 'cached', 'ledger'], $mismatches);

        return self::FAILURE;
    }
}
