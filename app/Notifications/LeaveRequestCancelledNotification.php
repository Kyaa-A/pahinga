<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestCancelledNotification extends Notification implements ShouldQueue
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
        $employee = $this->leaveRequest->employee;

        return (new MailMessage)
            ->subject('Leave Request Cancelled')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($employee->name.' has cancelled their leave request.')
            ->line('**Type:** '.$this->leaveRequest->getLeaveTypeLabel())
            ->line('**Dates:** '.$this->leaveRequest->start_date->format('M d, Y').' - '.$this->leaveRequest->end_date->format('M d, Y'))
            ->line('**Duration:** '.$this->leaveRequest->duration.' days')
            ->action('View Request', route('manager.pending-requests.show', $this->leaveRequest))
            ->line('No action is required on your part.');
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
            'employee_id' => $this->leaveRequest->user_id,
            'employee_name' => $this->leaveRequest->employee->name,
            'type' => $this->leaveRequest->leave_type,
            'type_label' => $this->leaveRequest->getLeaveTypeLabel(),
            'start_date' => $this->leaveRequest->start_date->toDateString(),
            'end_date' => $this->leaveRequest->end_date->toDateString(),
            'duration' => $this->leaveRequest->duration,
            'action_url' => route('manager.pending-requests.show', $this->leaveRequest),
            'message' => $this->leaveRequest->employee->name.' cancelled their '.$this->leaveRequest->getLeaveTypeLabel().' request',
        ];
    }
}
