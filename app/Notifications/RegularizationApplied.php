<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegularizationApplied extends Notification
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
        return ['database']; // Add 'mail' later if configured
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('New Regularization Request from ' . $this->request->user->name)
            ->line('Date: ' . $this->request->date->format('d M Y'))
            ->line('Reason: ' . $this->request->reason)
            ->action('Review Request', route('leave.approvals')) // Use existing approvals page or new one
            ->line('Please review and approve.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'regularization_applied',
            'request_id' => $this->request->id,
            'message' => 'New Regularization Request from ' . $this->request->user->name,
            'url' => route('leave.approvals'),
        ];
    }
}
