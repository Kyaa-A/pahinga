# Horizon Sentinel - Immediate Next Steps Guide

This guide provides step-by-step instructions to get your Horizon Sentinel project up and running, starting from your current state.

**📊 PROGRESS: Steps 1-9 ✅ COMPLETED | Steps 10+ ⏭️ NEXT**

---

## ✅ Phase 1: Basic Environment Setup (COMPLETED)

**Tasks:** HS-DB-001 and HS-DB-002
**Status:** ✅ Done - Skip to Phase 3!

### Step 1: Install Composer Dependencies

```bash
composer install
```

This will install all Laravel dependencies defined in `composer.json`.

### Step 2: Create and Configure .env File

```bash
cp .env.example .env
```

Then edit the `.env` file to configure your database connection:

```env
APP_NAME="Horizon Sentinel"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=horizon_sentinel
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

**Alternative: Use SQLite for Quick Start**

For simpler development without MySQL setup:

```env
DB_CONNECTION=sqlite
# Comment out or remove these:
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=horizon_sentinel
# DB_USERNAME=root
# DB_PASSWORD=
```

Then create the SQLite database file:

```bash
touch database/database.sqlite
```

### Step 3: Generate Application Key

```bash
php artisan key:generate
```

### Step 4: Create Database (MySQL only)

If using MySQL, create the database:

```bash
mysql -u root -p
```

Then in MySQL:

```sql
CREATE DATABASE horizon_sentinel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 5: Run Initial Migrations

```bash
php artisan migrate
```

This creates the default Laravel tables including `users`.

**Expected Output:**
- `users` table created
- `password_reset_tokens` table created
- `sessions` table created (if using database sessions)
- `cache` table created
- `jobs` table created

---

## ✅ Phase 2: Install Authentication (COMPLETED)

**Tasks:** HS-AUTH-001, HS-AUTH-002
**Status:** ✅ Done - Skip to Phase 3!

### Step 6: Install Laravel Breeze

Laravel Breeze provides simple authentication scaffolding.

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

**When prompted, choose:**
- Stack: **blade** (default)
- Dark mode: **Your preference** (recommended: yes)
- Testing: **PHPUnit** (default)

### Step 7: Install Frontend Dependencies

```bash
npm install
```

### Step 8: Build Frontend Assets

```bash
npm run build
```

Or for development with hot reloading:

```bash
npm run dev
```

(Keep this running in a separate terminal)

### Step 9: Test Authentication (Task HS-AUTH-002)

Start the Laravel development server:

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser. You should see:
- Register link
- Login link
- Breeze authentication is working

**Test Registration:**
1. Click "Register"
2. Create a test account
3. Verify you can login/logout

---

---
---
---

# 👉 START HERE! Phase 3: Extend User Model

**Tasks:** HS-AUTH-003 & HS-DB-003
**Status:** 🔄 IN PROGRESS - This is your next step!
**Time:** ~30 minutes

---
---
---

### Step 10: Create Migration for User Extensions

```bash
php artisan make:migration add_role_and_manager_to_users_table
```

Edit the newly created migration file in `database/migrations/`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['employee', 'manager'])
                  ->default('employee')
                  ->after('email');

            $table->foreignId('manager_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['role', 'manager_id']);
        });
    }
};
```

### Step 11: Run the New Migration

```bash
php artisan migrate
```

### Step 12: Update User Model

Edit `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'manager_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Helper Methods
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function hasManager(): bool
    {
        return $this->manager_id !== null;
    }
}
```

---

## ⏳ Phase 4: Create Leave Request Data Model

**Tasks:** HS-DB-004, HS-DB-005
**Status:** Not Started - Complete Phase 3 first
**Time:** ~1-2 hours

### Step 13: Create LeaveRequest Migration

```bash
php artisan make:migration create_leave_requests_table
```

Edit the migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->enum('leave_type', [
                'paid_time_off',
                'unpaid_leave',
                'sick_leave',
                'vacation'
            ]);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', [
                'pending',
                'approved',
                'denied',
                'cancelled'
            ])->default('pending');
            $table->text('employee_notes')->nullable();
            $table->text('manager_notes')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('manager_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
```

### Step 14: Create LeaveRequestHistory Migration (Optional but Recommended)

```bash
php artisan make:migration create_leave_request_history_table
```

Edit the migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_request_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->enum('action', [
                'submitted',
                'approved',
                'denied',
                'cancelled',
                'updated'
            ]);
            $table->foreignId('performed_by_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('leave_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_history');
    }
};
```

### Step 15: Run Migrations

```bash
php artisan migrate
```

---

## ⏳ Phase 5: Create Models

**Tasks:** HS-BE-001, HS-BE-002, HS-BE-003
**Status:** Not Started - Complete Phase 4 first
**Time:** ~2-3 hours

### Step 16: Create LeaveRequest Model

```bash
php artisan make:model LeaveRequest
```

Edit `app/Models/LeaveRequest.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequest extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(LeaveRequestHistory::class);
    }

    // Query Scopes
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    public function scopeDenied(Builder $query): void
    {
        $query->where('status', 'denied');
    }

    public function scopeForManager(Builder $query, int $managerId): void
    {
        $query->where('manager_id', $managerId);
    }

    public function scopeOverlapping(Builder $query, string $startDate, string $endDate): void
    {
        $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isDenied(): bool
    {
        return $this->status === 'denied';
    }

    public function recordHistory(string $action, int $userId, ?string $notes = null): void
    {
        $this->history()->create([
            'action' => $action,
            'performed_by_user_id' => $userId,
            'notes' => $notes,
        ]);
    }
}
```

### Step 17: Create LeaveRequestHistory Model

```bash
php artisan make:model LeaveRequestHistory
```

Edit `app/Models/LeaveRequestHistory.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestHistory extends Model
{
    use HasFactory;

    protected $table = 'leave_request_history';

    public $timestamps = false;

    protected $fillable = [
        'leave_request_id',
        'action',
        'performed_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
```

---

## ⏳ Phase 6: Create Test Data

**Task:** HS-BE-004
**Status:** Not Started - Complete Phase 5 first
**Time:** ~30 minutes

### Step 18: Create Database Seeders

```bash
php artisan make:seeder UserSeeder
php artisan make:seeder LeaveRequestSeeder
```

Edit `database/seeders/UserSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Managers
        $manager1 = User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah.johnson@horizondynamics.com',
            'password' => Hash::make('password'), // Default: password
            'role' => 'manager',
            'manager_id' => null,
        ]);

        $manager2 = User::create([
            'name' => 'Michael Chen',
            'email' => 'michael.chen@horizondynamics.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'manager_id' => null,
        ]);

        // Create Employees under Manager 1
        User::create([
            'name' => 'John Smith',
            'email' => 'john.smith@horizondynamics.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'manager_id' => $manager1->id,
        ]);

        User::create([
            'name' => 'Emily Davis',
            'email' => 'emily.davis@horizondynamics.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'manager_id' => $manager1->id,
        ]);

        User::create([
            'name' => 'David Martinez',
            'email' => 'david.martinez@horizondynamics.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'manager_id' => $manager1->id,
        ]);

        // Create Employees under Manager 2
        User::create([
            'name' => 'Jessica Lee',
            'email' => 'jessica.lee@horizondynamics.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'manager_id' => $manager2->id,
        ]);

        User::create([
            'name' => 'Robert Wilson',
            'email' => 'robert.wilson@horizondynamics.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'manager_id' => $manager2->id,
        ]);

        User::create([
            'name' => 'Amanda Brown',
            'email' => 'amanda.brown@horizondynamics.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'manager_id' => $manager2->id,
        ]);

        echo "Created 2 managers and 6 employees. Default password: 'password'\n";
    }
}
```

Edit `database/seeders/LeaveRequestSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role', 'employee')->get();

        foreach ($employees as $employee) {
            // Create a pending request
            $pending = LeaveRequest::create([
                'user_id' => $employee->id,
                'manager_id' => $employee->manager_id,
                'leave_type' => 'vacation',
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(15),
                'status' => 'pending',
                'employee_notes' => 'Planning a family vacation.',
                'submitted_at' => Carbon::now(),
            ]);
            $pending->recordHistory('submitted', $employee->id);

            // Create an approved request
            $approved = LeaveRequest::create([
                'user_id' => $employee->id,
                'manager_id' => $employee->manager_id,
                'leave_type' => 'paid_time_off',
                'start_date' => Carbon::now()->addDays(30),
                'end_date' => Carbon::now()->addDays(32),
                'status' => 'approved',
                'employee_notes' => 'Doctor appointment and recovery.',
                'manager_notes' => 'Approved. Take care!',
                'submitted_at' => Carbon::now()->subDays(5),
                'reviewed_at' => Carbon::now()->subDays(4),
            ]);
            $approved->recordHistory('submitted', $employee->id);
            $approved->recordHistory('approved', $employee->manager_id, 'Approved. Take care!');
        }

        // Create overlapping requests for conflict testing
        $manager1Employees = User::where('manager_id', 1)->limit(3)->get();
        foreach ($manager1Employees as $emp) {
            LeaveRequest::create([
                'user_id' => $emp->id,
                'manager_id' => $emp->manager_id,
                'leave_type' => 'vacation',
                'start_date' => Carbon::now()->addDays(20),
                'end_date' => Carbon::now()->addDays(25),
                'status' => 'pending',
                'employee_notes' => 'Holiday trip - potential conflict!',
                'submitted_at' => Carbon::now(),
            ]);
        }

        echo "Created leave requests with some overlapping dates for conflict testing.\n";
    }
}
```

### Step 19: Update DatabaseSeeder

Edit `database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LeaveRequestSeeder::class,
        ]);
    }
}
```

### Step 20: Run Seeders

```bash
php artisan db:seed
```

Or refresh everything:

```bash
php artisan migrate:fresh --seed
```

---

## Verification Checklist

Before proceeding to build the employee interface, verify:

- [ ] Laravel server runs without errors (`php artisan serve`)
- [ ] You can register and login successfully
- [ ] Database has `users`, `leave_requests`, `leave_request_history` tables
- [ ] Sample data is seeded (check with `php artisan tinker` then `User::count()`)
- [ ] Models have proper relationships (test in tinker: `User::first()->leaveRequests`)

---

## Next Phase: Employee Interface Development

Once the above is complete, you'll be ready to start implementing the employee interface (Tasks HS-FE-001 through HS-FE-012). This includes:

1. Creating the LeaveRequestController
2. Building Blade views for:
   - Listing leave requests
   - Creating new requests
   - Viewing request details
3. Implementing authorization policies
4. Adding navigation

Refer to the detailed task list in `process-task-list.md` for specific implementation steps.

---

## Useful Commands Reference

```bash
# Start development server
php artisan serve

# Start frontend build watcher
npm run dev

# Check routes
php artisan route:list

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run migrations
php artisan migrate
php artisan migrate:fresh --seed  # Reset and seed

# Tinker (Laravel REPL)
php artisan tinker

# Create files
php artisan make:controller ControllerName
php artisan make:model ModelName
php artisan make:migration migration_name
php artisan make:seeder SeederName
php artisan make:policy PolicyName
php artisan make:request RequestName
```

---

## Troubleshooting

**Issue: "No application encryption key has been specified"**
```bash
php artisan key:generate
```

**Issue: "SQLSTATE[HY000] [2002] Connection refused"**
- Check database is running
- Verify .env database credentials

**Issue: npm errors**
```bash
rm -rf node_modules package-lock.json
npm install
```

**Issue: Composer errors**
```bash
rm -rf vendor composer.lock
composer install
```

---

## Summary

You've now completed:
- ✅ Basic Laravel setup
- ✅ Authentication with Breeze
- ✅ Database schema for users and leave requests
- ✅ Models with relationships
- ✅ Test data seeding

**Your project is ready for feature development!**

Next: Start implementing the employee interface following tasks HS-FE-001 through HS-FE-012.
