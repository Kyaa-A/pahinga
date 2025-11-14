<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public LeaveRequest $leaveRequest
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $manager = $this->leaveRequest->manager;

        return (new MailMessage)
            ->subject('Leave Request Approved')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Great news! Your leave request has been approved.')
            ->line('**Type:** '.$this->leaveRequest->getLeaveTypeLabel())
            ->line('**Dates:** '.$this->leaveRequest->start_date->format('M d, Y').' - '.$this->leaveRequest->end_date->format('M d, Y'))
            ->line('**Duration:** '.$this->leaveRequest->duration.' days')
            ->line('**Approved by:** '.$manager->name)
            ->action('View Request', route('leave-requests.show', $this->leaveRequest))
            ->line('Enjoy your time off!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'manager_id' => $this->leaveRequest->manager_id,
            'manager_name' => $this->leaveRequest->manager->name,
            'type' => $this->leaveRequest->leave_type,
            'type_label' => $this->leaveRequest->getLeaveTypeLabel(),
            'start_date' => $this->leaveRequest->start_date->toDateString(),
            'end_date' => $this->leaveRequest->end_date->toDateString(),
            'duration' => $this->leaveRequest->duration,
            'action_url' => route('leave-requests.show', $this->leaveRequest),
            'message' => 'Your '.$this->leaveRequest->getLeaveTypeLabel().' request was approved',
        ];
    }
}
