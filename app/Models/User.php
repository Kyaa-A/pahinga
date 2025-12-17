<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'manager_id',
        'department',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if the user account is active.
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }

    /**
     * Get the manager that the user reports to.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all employees that report to this manager.
     */
    public function directReports(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * Get all leave requests for this user.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Check if the user is a manager.
     */
    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    /**
     * Check if the user is an employee.
     */
    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    /**
     * Check if the user has a manager assigned.
     */
    public function hasManager(): bool
    {
        return $this->manager_id !== null;
    }

    /**
     * Check if the user is an HR admin.
     */
    public function isHRAdmin(): bool
    {
        return $this->role === UserRole::HRAdmin;
    }

    /**
     * Get all leave balances for this user.
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get leave balance for a specific type and year.
     */
    public function getLeaveBalance(string $leaveType, ?int $year = null): ?LeaveBalance
    {
        $year = $year ?? now()->year;

        return $this->leaveBalances()
            ->where('leave_type', $leaveType)
            ->where('year', $year)
            ->first();
    }

    /**
     * Get all delegations where this user is the manager.
     */
    public function delegations(): HasMany
    {
        return $this->hasMany(ManagerDelegation::class, 'manager_id');
    }

    /**
     * Get all delegations where this user is the delegate.
     */
    public function delegateFor(): HasMany
    {
        return $this->hasMany(ManagerDelegation::class, 'delegate_manager_id');
    }

    /**
     * Get the current active delegate for this manager.
     */
    public function getCurrentDelegate(): ?User
    {
        return ManagerDelegation::getActiveDelegate($this->id);
    }

    /**
     * Check if user can approve leave requests (manager or HR admin).
     */
    public function canApproveLeaveRequests(): bool
    {
        return $this->isManager() || $this->isHRAdmin();
    }
}
