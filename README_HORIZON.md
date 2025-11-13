# Horizon Sentinel

**A Digital Leave Request and Conflict Avoidance System for Horizon Dynamics**

---

## 📖 What is Horizon Sentinel?

Horizon Sentinel is a Laravel-based leave management system that helps:
- **Employees** submit and track time-off requests
- **Managers** review requests and avoid staffing conflicts
- **Teams** maintain visibility into scheduled absences

**Current Status:** Foundation Complete - Ready for Feature Development

---

## 🚀 Quick Start

### See It Working
```bash
php artisan serve
# Visit http://localhost:8000
# Register an account and log in!
```

### Next Development Step
**Go to:** `NEXT_STEPS.md` - Start at Phase 3, Step 10

---

## 📚 Documentation

### For New Developers
1. **START HERE:** `PROJECT_STATUS.md` - Shows exactly where you are
2. **Quick Overview:** `QUICK_START.md` - Commands and basics
3. **Step-by-Step:** `NEXT_STEPS.md` - Detailed implementation guide

### For Planning & Requirements
4. **Product Requirements:** `.cursor/.rules/create-prd.md`
5. **Task List:** `.cursor/.rules/process-task-list.md`
6. **Task Generation:** `.cursor/.rules/generate-task.md`

---

## ✅ What's Already Built

### Phase 1 & 2: Foundation (COMPLETE)
- ✅ Laravel 12 installed and configured
- ✅ PostgreSQL database connected
- ✅ User authentication (Breeze)
  - Registration
  - Login/Logout
  - Password reset
  - Email verification
  - Profile management
- ✅ Tailwind CSS + Alpine.js
- ✅ Vite build system
- ✅ All tests passing

**Tables Created:**
- `users` - User accounts
- `sessions` - User sessions
- `cache` - Application cache
- `jobs` - Background jobs
- `password_reset_tokens` - Password resets

---

## 🎯 What's Next

### Phase 3: Core Data Model (IN PROGRESS)
Add user roles and leave request tables

**Tasks:**
- [ ] HS-DB-003: Add role & manager_id to users
- [ ] HS-DB-004: Create leave_requests table
- [ ] HS-DB-005: Create leave_request_history table
- [ ] HS-BE-001: Update User model relationships
- [ ] HS-BE-002: Create LeaveRequest model
- [ ] HS-BE-003: Create LeaveRequestHistory model
- [ ] HS-BE-004: Create database seeders

### Phase 4: Employee Interface
Build forms for employees to submit leave requests

### Phase 5: Manager Interface
Build dashboard for managers to approve/deny requests

### Phase 6: Calendar & Conflict Detection
Visual calendar showing team availability and conflicts

---

## 🛠️ Tech Stack

- **Framework:** Laravel 12
- **Database:** PostgreSQL 16
- **Frontend:** Blade Templates, Tailwind CSS, Alpine.js
- **Build Tool:** Vite
- **Auth:** Laravel Breeze
- **PHP:** 8.2+

---

## 📁 Project Structure

```
horizon-sentinel/
├── app/
│   ├── Models/              # User, LeaveRequest models
│   ├── Http/Controllers/    # Page logic & form handling
│   └── Policies/            # Authorization rules
│
├── database/
│   ├── migrations/          # Database table definitions
│   └── seeders/             # Sample data generators
│
├── resources/
│   ├── views/               # Blade templates (HTML)
│   ├── css/                 # Tailwind CSS
│   └── js/                  # Alpine.js
│
├── routes/
│   ├── web.php              # Application routes
│   └── auth.php             # Authentication routes
│
├── .cursor/.rules/          # Planning documents
│   ├── create-prd.md        # Product requirements
│   ├── process-task-list.md # All development tasks
│   └── generate-task.md     # Task creation guide
│
└── Documentation:
    ├── PROJECT_STATUS.md    # Current progress overview
    ├── QUICK_START.md       # Quick reference guide
    ├── NEXT_STEPS.md        # Detailed implementation steps
    └── README_HORIZON.md    # This file
```

---

## ⚡ Common Commands

```bash
# Development
php artisan serve              # Start dev server
npm run dev                    # Watch frontend changes

# Database
php artisan migrate            # Run migrations
php artisan migrate:rollback   # Undo last migration
php artisan migrate:fresh --seed # Reset & add test data
php artisan db:seed            # Add test data only

# Code Generation
php artisan make:model Name    # Create model
php artisan make:migration name # Create migration
php artisan make:controller Name # Create controller
php artisan make:seeder Name   # Create seeder

# Utilities
php artisan route:list         # See all routes
php artisan tinker             # Open Laravel console
php artisan config:clear       # Clear config cache
```

---

## 🔧 Configuration

### Database (PostgreSQL)
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=horizon_sentinel
DB_USERNAME=horizon_user
DB_PASSWORD=password
```

### Application
```env
APP_NAME="Horizon Sentinel"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

**Config File:** `.env`

---

## 📊 Progress Overview

| Component | Status | Tasks | Complete |
|-----------|--------|-------|----------|
| **Setup & Auth** | ✅ Done | 9/9 | 100% |
| **Core Data Model** | 🔄 In Progress | 0/7 | 0% |
| **Employee Interface** | ⏳ Pending | 0/12 | 0% |
| **Manager Interface** | ⏳ Pending | 0/8 | 0% |
| **Calendar View** | ⏳ Pending | 0/4 | 0% |

**Overall:** 30% Complete

---

## 🎓 Key Concepts

### User Roles
- **Employee:** Can submit leave requests
- **Manager:** Can approve/deny requests for their team

### Leave Request Workflow
1. Employee submits request
2. Request routed to their manager
3. Manager sees team calendar (conflict detection)
4. Manager approves or denies
5. Employee notified of decision

### Conflict Detection
System warns managers when:
- Multiple team members request same dates
- Team availability drops below threshold (default 30%)
- Critical coverage gaps exist

---

## 🆘 Troubleshooting

**Can't start server:**
```bash
php artisan serve --port=8001  # Try different port
```

**Database errors:**
```bash
service postgresql status       # Check DB running
service postgresql start        # Start if needed
```

**Migration errors:**
```bash
php artisan migrate:rollback    # Undo last migration
# Fix the migration file
php artisan migrate             # Try again
```

**Need to reset everything:**
```bash
php artisan migrate:fresh --seed
```

---

## 🤝 Development Workflow

1. **Understand the requirement** (check PRD and task list)
2. **Create migration** (define database changes)
3. **Create model** (define PHP class)
4. **Create controller** (handle user actions)
5. **Create views** (HTML templates)
6. **Add routes** (connect URLs)
7. **Test** (manually and with automated tests)
8. **Commit** (save progress with git)

---

## 📞 Getting Help

- **Lost?** Read `PROJECT_STATUS.md`
- **Need quick commands?** See `QUICK_START.md`
- **Ready to code?** Follow `NEXT_STEPS.md`
- **Want details?** Check `.cursor/.rules/create-prd.md`

---

## 📝 License

MIT License - Built with Laravel

---

**Happy Coding! 🚀**

*Last Updated: November 13, 2025*
