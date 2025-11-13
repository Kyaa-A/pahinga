# Quick Start Guide - Horizon Sentinel

**For when you just need to get started quickly!**

---

## 🚀 Your App Right Now

Your Laravel application is **running and ready**. Here's what works:

```bash
# Start your server
php artisan serve
```

Visit **http://localhost:8000** and you'll see:
- ✅ Welcome page
- ✅ Login button
- ✅ Register button
- ✅ Full authentication system

**Try it:** Click "Register", create an account, and log in!

---

## 📋 What's Next? (The Simple Version)

You need to add 3 things to make Horizon Sentinel work:

### 1. User Roles (30 min)
Make some users "employees" and some "managers"

**Commands:**
```bash
php artisan make:migration add_role_and_manager_to_users_table
# Then edit the file, add code, and run:
php artisan migrate
```

### 2. Leave Requests (1-2 hours)
Create tables to store leave requests

**Commands:**
```bash
php artisan make:migration create_leave_requests_table
php artisan make:migration create_leave_request_history_table
php artisan make:model LeaveRequest
php artisan make:model LeaveRequestHistory
# Edit files, then:
php artisan migrate
```

### 3. Sample Data (30 min)
Add test users and leave requests

**Commands:**
```bash
php artisan make:seeder UserSeeder
php artisan make:seeder LeaveRequestSeeder
# Edit files, then:
php artisan db:seed
```

---

## 🎯 After These 3 Steps

You'll have:
- Managers and employees in your database
- Leave request tables ready
- Sample data to work with
- Ready to build the employee/manager interfaces

---

## 📚 Where to Get Detailed Instructions

**For complete step-by-step code:**
- Open **NEXT_STEPS.md**
- Go to **Phase 3** (starts at Step 10)
- Copy and paste the code provided

**To see your progress:**
- Open **PROJECT_STATUS.md**

**To understand what you're building:**
- Open **.cursor/.rules/create-prd.md**

---

## 🆘 Common Issues

**"I can't start the server"**
```bash
# Make sure nothing is running on port 8000
php artisan serve --port=8001
```

**"Database connection error"**
```bash
# Check PostgreSQL is running
service postgresql status
# If not:
service postgresql start
```

**"I made a mistake in a migration"**
```bash
# Roll back the last migration
php artisan migrate:rollback
# Fix the file, then:
php artisan migrate
```

---

## ⚡ Super Quick Command Cheat Sheet

```bash
# Start server
php artisan serve

# Create migration
php artisan make:migration name_here

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Create model
php artisan make:model ModelName

# Create controller
php artisan make:controller ControllerName

# Create seeder
php artisan make:seeder SeederName

# Run seeders
php artisan db:seed

# Reset everything and reseed
php artisan migrate:fresh --seed

# Open Laravel console
php artisan tinker
```

---

## 🎓 Want to Learn More?

**Laravel Basics:**
- Models: Define database tables as PHP classes
- Migrations: Create/modify database tables
- Controllers: Handle form submissions and page logic
- Views: HTML templates (Blade files)
- Routes: Map URLs to controllers

**Your workflow:**
1. Create migration → Define table structure
2. Create model → Work with data in PHP
3. Create controller → Handle user actions
4. Create views → Display pages to users
5. Add routes → Connect URLs to controllers

---

## 👉 Ready to Continue?

**I'm ready to build features!**
→ Go to NEXT_STEPS.md, Step 10

**I want to understand better first**
→ Read PROJECT_STATUS.md and the PRD

**I need hands-on guidance**
→ Just ask me! I'll walk you through each step.

---

**Remember:** You've already completed the hard part (setup)! Now it's just building features step by step. 🎉
