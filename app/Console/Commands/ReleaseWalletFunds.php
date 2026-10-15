<?php

namespace App\Console\Commands;

use App\Actions\Wallet\ReleasePendingFunds;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('wallets:release')]
#[Description('Release held income whose hold period has ended')]
class ReleaseWalletFunds extends Command
{
    public function handle(ReleasePendingFunds $release): int
    {
        $this->info("Dirilis: {$release->handle()} transaksi.");

        return self::SUCCESS;
    }
}
