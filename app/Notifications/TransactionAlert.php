<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionAlert extends Notification
{
    use Queueable;

    protected $transaction;
    protected $action;

    /**
     * @param Transaction $transaction
     * @param string $action  ("created" ou "updated")
     */
    public function __construct(Transaction $transaction, string $action)
    {
        $this->transaction = $transaction;
        $this->action = $action;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Transaction {$this->action}: {$this->transaction->description}",
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'type' => $this->transaction->type,
        ];
    }
}
