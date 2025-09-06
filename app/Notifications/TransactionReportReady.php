<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TransactionReportReady extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reportUrl;

    public function __construct(string $reportUrl)
    {
        $this->reportUrl = $reportUrl;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Send email and save on notifications table
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Transaction Report is Ready')
            ->line('Your transaction report has been generated.')
            ->action('Download Report', $this->reportUrl)
            ->line('Thank you for using our service!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Transaction report ready',
            'report_url' => $this->reportUrl,
        ];
    }
}
