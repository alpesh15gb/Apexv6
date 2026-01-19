<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegularizationStatusUpdated extends Notification
{
    use Queueable;

    public $request;

    /**
     * Create a new notification instance.
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Your Regularization Request for ' . $this->request->date->format('d M Y'))
            ->line('Has been ' . strtoupper($this->request->status))
            ->line('Remarks: ' . ($this->request->remarks ?? 'None'))
            ->action('View History', route('attendance.history'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'regularization_status_updated',
            'request_id' => $this->request->id,
            'status' => $this->request->status,
            'message' => 'Your request for ' . $this->request->date->format('d M') . ' was ' . $this->request->status,
            'url' => route('attendance.history'),
        ];
    }
}
