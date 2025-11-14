<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestDeniedNotification extends Notification implements ShouldQueue
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
        $latestHistory = $this->leaveRequest->history()
            ->where('action', 'denied')
            ->latest()
            ->first();

        $message = (new MailMessage)
            ->subject('Leave Request Denied')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your leave request has been denied.')
            ->line('**Type:** '.$this->leaveRequest->getLeaveTypeLabel())
            ->line('**Dates:** '.$this->leaveRequest->start_date->format('M d, Y').' - '.$this->leaveRequest->end_date->format('M d, Y'))
            ->line('**Duration:** '.$this->leaveRequest->duration.' days')
            ->line('**Denied by:** '.$manager->name);

        if ($latestHistory && $latestHistory->notes) {
            $message->line('**Reason:** '.$latestHistory->notes);
        }

        $message->action('View Request', route('leave-requests.show', $this->leaveRequest))
            ->line('If you have questions, please contact your manager.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $latestHistory = $this->leaveRequest->history()
            ->where('action', 'denied')
            ->latest()
            ->first();

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'manager_id' => $this->leaveRequest->manager_id,
            'manager_name' => $this->leaveRequest->manager->name,
            'type' => $this->leaveRequest->leave_type,
            'type_label' => $this->leaveRequest->getLeaveTypeLabel(),
            'start_date' => $this->leaveRequest->start_date->toDateString(),
            'end_date' => $this->leaveRequest->end_date->toDateString(),
            'duration' => $this->leaveRequest->duration,
            'reason' => $latestHistory?->notes,
            'action_url' => route('leave-requests.show', $this->leaveRequest),
            'message' => 'Your '.$this->leaveRequest->getLeaveTypeLabel().' request was denied',
        ];
    }
}
