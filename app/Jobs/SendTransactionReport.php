<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\TransactionReportReady;
use App\Exports\TransactionsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class SendTransactionReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutos
    public $tries = 3; // Número de tentativas
    public $backoff = 60; // Intervalo entre tentativas

    protected $userId;
    protected $filters;

    public function __construct(int $userId, array $filters = [])
    {
        $this->userId = $userId;
        $this->filters = $filters;
    }

    public function handle()
    {
        $user = User::find($this->userId);
        if (!$user) {
            // opcional: log ou throw
            throw new \Exception("User not found: {$this->userId}");
        }

        // create the Excel
        $fileName = 'reports/transactions_' . now()->format('Ymd_His') . '.xlsx';
        Excel::store(new TransactionsExport($user, $this->filters), $fileName, 'public');

        // create public URL ex: /storage/reports/transactions_20250906_170000.xlsx
        // front-end needed to get the url and put together with the rest of url project.
        // $reportUrl = Storage::url($fileName);

        //Here is the complete url
        $reportUrl = url(Storage::url($fileName));

        // Notified the user
        $user->notify(new TransactionReportReady($reportUrl));
    }
}
