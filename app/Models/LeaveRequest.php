<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'manager_id',
        'leave_type',
        'start_date',
        'end_date',
        'status',
        'employee_notes',
        'manager_notes',
        'submitted_at',
        'reviewed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get the employee who submitted the request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user() - get the employee who submitted the request.
     */
    public function employee(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Get the manager who will review the request.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all history records for this leave request.
     */
    public function history(): HasMany
    {
        return $this->hasMany(LeaveRequestHistory::class);
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include denied requests.
     */
    public function scopeDenied(Builder $query): Builder
    {
        return $query->where('status', 'denied');
    }

    /**
     * Scope a query to only include requests for a specific manager.
     */
    public function scopeForManager(Builder $query, int $managerId): Builder
    {
        return $query->where('manager_id', $managerId);
    }

    /**
     * Scope a query to find requests that overlap with given date range.
     */
    public function scopeOverlapping(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is denied.
     */
    public function isDenied(): bool
    {
        return $this->status === 'denied';
    }

    /**
     * Check if the request is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get the human-readable label for the leave type.
     */
    public function getLeaveTypeLabel(): string
    {
        return match ($this->leave_type) {
            'paid_time_off' => 'Paid Time Off',
            'unpaid_leave' => 'Unpaid Leave',
            'sick_leave' => 'Sick Leave',
            'vacation' => 'Vacation',
            default => ucfirst(str_replace('_', ' ', $this->leave_type)),
        };
    }

    /**
     * Get the duration of the leave request in days.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Record a history entry for this leave request.
     */
    public function recordHistory(string $action, int $performedByUserId, ?string $notes = null): LeaveRequestHistory
    {
        return $this->history()->create([
            'action' => $action,
            'performed_by_user_id' => $performedByUserId,
            'notes' => $notes,
        ]);
    }
}
