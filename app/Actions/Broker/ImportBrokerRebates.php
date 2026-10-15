<?php

namespace App\Actions\Broker;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\BrokerAccountStatus;
use App\Models\Broker;
use App\Models\BrokerAccount;
use App\Models\BrokerRebate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Imports an IB report (CSV: account_login, lots, rebate_usd) for one broker and month.
 * All-or-nothing; re-importing a period replaces its rows.
 */
class ImportBrokerRebates
{
    public const COLUMNS = ['account_login', 'lots', 'rebate_usd'];

    public function __construct(private RecordAdminAudit $audit) {}

    /**
     * @return array{rows: int, unmatched: int, rebate_idr: int}
     */
    public function handle(User $admin, Broker $broker, string $period, float $fxRate, UploadedFile $file): array
    {
        $rows = $this->parse($file);
        $linked = BrokerAccount::query()->where('broker_id', $broker->id)->where('status', BrokerAccountStatus::Linked)->pluck('id', 'account_login');

        $summary = DB::transaction(function () use ($broker, $period, $fxRate, $rows, $linked) {
            BrokerRebate::where('broker_id', $broker->id)->where('period', $period)->delete();
            $unmatched = 0;
            $total = 0;

            foreach ($rows as $row) {
                $accountId = $linked[$row['account_login']] ?? null;
                $idr = (int) round($row['rebate_usd'] * $fxRate);
                $unmatched += $accountId === null ? 1 : 0;
                $total += $idr;

                BrokerRebate::create([
                    'broker_id' => $broker->id,
                    'broker_account_id' => $accountId,
                    'account_login' => $row['account_login'],
                    'period' => $period,
                    'lots' => $row['lots'],
                    'rebate_usd' => $row['rebate_usd'],
                    'fx_rate' => $fxRate,
                    'rebate_idr' => $idr,
                ]);
            }

            return ['rows' => count($rows), 'unmatched' => $unmatched, 'rebate_idr' => $total];
        });

        $this->audit->handle($admin, 'broker_rebates.imported', $broker, null, ['period' => $period, ...$summary]);

        return $summary;
    }

    /**
     * @return list<array{account_login: string, lots: float, rebate_usd: float}>
     */
    private function parse(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages(['file' => 'File tidak bisa dibaca.']);
        }

        $header = array_map(fn ($cell) => strtolower(trim((string) $cell)), fgetcsv($handle, escape: '\\') ?: []);
        $missing = array_diff(self::COLUMNS, $header);

        if ($missing !== []) {
            fclose($handle);

            throw ValidationException::withMessages(['file' => 'Kolom wajib tidak ada: '.implode(', ', $missing).'.']);
        }

        $index = array_flip($header);
        $rows = [];
        $line = 1;

        while (($cells = fgetcsv($handle, escape: '\\')) !== false) {
            $line++;

            if ($cells === [null]) {
                continue;
            }

            $login = trim((string) ($cells[$index['account_login']] ?? ''));
            $lots = trim((string) ($cells[$index['lots']] ?? ''));
            $rebate = trim((string) ($cells[$index['rebate_usd']] ?? ''));

            if ($login === '' || ! is_numeric($lots) || ! is_numeric($rebate) || (float) $lots < 0 || (float) $rebate < 0) {
                fclose($handle);

                throw ValidationException::withMessages(['file' => "Baris {$line} tidak valid: login wajib, lots dan rebate_usd harus angka ≥ 0."]);
            }

            $rows[] = ['account_login' => $login, 'lots' => (float) $lots, 'rebate_usd' => (float) $rebate];
        }

        fclose($handle);

        return $rows;
    }
}
