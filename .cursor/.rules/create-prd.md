# Product Requirements Document (PRD) - Horizon Sentinel

## 1. Introduction
Horizon Sentinel is a digital leave request and conflict avoidance system designed to formalize and centralize time off management for Horizon Dynamics. This system aims to eliminate the current manual process, which leads to staffing conflicts and operational bottlenecks due to disorganized time off management.

## 2. Goals & Objectives
*   **Primary Goal:** To provide a single, easy-to-use platform for managing employee time off requests and approvals.
*   **Key Objectives:**
    *   Formalize the leave request submission process, ensuring all requests are digitally recorded.
    *   Provide real-time visibility into scheduled absences across teams.
    *   Reduce staffing conflicts by enabling managers to make informed approval decisions.
    *   Streamline communication between employees and managers regarding leave status.

## 3. Target Audience
*   **Employees of Horizon Dynamics:** Users who need to submit and track their leave requests.
*   **Managers of Horizon Dynamics:** Users who need to review, approve/deny leave requests, and monitor team availability.

## 4. Problem Statement
Horizon Dynamics currently suffers from disorganized time off management characterized by:
*   **Submission Chaos:** Informal email/note-based requests are easily overlooked.
*   **Scheduling Conflicts:** Managers approve requests in isolation, leading to critical understaffing or overlapping key personnel absences due to inconsistent calendar updates.
*   **Lack of Central Visibility:** No single, comprehensive view of company-wide absences, hindering cross-departmental planning.

## 5. Solution Overview - Horizon Sentinel
Horizon Sentinel will be a concise, digital Information System built using Laravel and Tailwind CSS. It will function as a simple Digital Leave Request and Conflict Avoidance System by handling the routing and scheduling view, eliminating the need for email tracking.

## 6. Key Features

### 6.1. Employee Interface
*   **Leave Request Submission:**
    *   Allows employees to submit new leave requests with a specified date range.
    *   Option to select the type of leave (e.g., Paid Time Off, Unpaid Leave, Sick Leave, Vacation).
*   **Request Status Tracking:**
    *   Employees can view the current status of their submitted requests (e.g., Pending, Approved, Denied).
    *   Notifications for status changes.

### 6.2. Manager Interface
*   **Pending Request Review:**
    *   View a list of all pending leave requests from their direct reports.
    *   Ability to approve or deny requests.
*   **Team Availability Calendar:**
    *   Crucial calendar overlay showing existing approved time off for their team *before* making an approval decision.
    *   Highlighting potential conflicts or critical staffing levels.

### 6.3. System Functionality (Core)
*   **Digital Record Keeping:** Ensures a digital, timestamped, and visible record of all intended absences.
*   **Request Routing:** Automated routing of requests to the appropriate manager.
*   **Conflict Detection (Manager View):** Visual cues in the calendar to alert managers of potential understaffing or critical overlaps.

### 6.4. Conflict Detection Logic (Detailed)
The conflict detection system should identify and warn managers about:

*   **Overlapping Leave Requests:**
    *   When multiple team members request leave on the same dates.
    *   Threshold: Alert when more than X% of team (configurable, default 30%) is scheduled off simultaneously.
*   **Critical Role Coverage:**
    *   Flag when employees with critical roles/skills are scheduled off together.
    *   Initially implemented as simple overlap detection; future: role-based rules.
*   **Sequential Leave Patterns:**
    *   Highlight potential coverage gaps where approved leaves occur back-to-back, leaving minimal coverage.
*   **Visual Indicators:**
    *   Calendar color-coding: Green (safe), Yellow (approaching threshold), Red (critical conflict).
    *   Tooltip/popover showing which team members are affected.
*   **Warning on Approval:**
    *   When a manager attempts to approve a request that would create a conflict, display a modal warning with:
        - List of other approved/pending leaves during that period.
        - Calculated team availability percentage.
        - Option to proceed with approval or deny with reason.

### 6.5. User Stories

**As an Employee:**
*   I want to submit a leave request for specific dates so that my manager knows when I'll be absent.
*   I want to see all my past, pending, and approved leave requests in one place.
*   I want to receive notifications when my leave request is approved or denied.
*   I want to optionally add notes/reasons to my leave request.
*   I want to be able to cancel a pending leave request before it's approved.

**As a Manager:**
*   I want to see all pending leave requests from my direct reports so I can review them promptly.
*   I want to view my team's calendar showing all approved leaves before approving new requests.
*   I want to be warned when approving a leave would create a staffing conflict.
*   I want to add comments/reasons when denying a leave request so employees understand the decision.
*   I want to see a dashboard summarizing my team's leave status (upcoming leaves, pending requests).

**As the System:**
*   The system must route leave requests to the correct manager based on employee-manager relationships.
*   The system must prevent unauthorized users from viewing other teams' leave data.
*   The system must maintain an audit trail of all leave request actions (submitted, approved, denied, cancelled).

## 7. Data Model Overview

### 7.1. Core Entities

**Users Table (Extended):**
*   id (primary key)
*   name
*   email
*   password
*   role (enum: 'employee', 'manager')
*   manager_id (foreign key to users.id, nullable for managers)
*   created_at, updated_at

**Leave Requests Table:**
*   id (primary key)
*   user_id (foreign key to users.id) - Employee who submitted
*   manager_id (foreign key to users.id) - Manager who needs to approve
*   leave_type (enum: 'paid_time_off', 'unpaid_leave', 'sick_leave', 'vacation')
*   start_date (date)
*   end_date (date)
*   status (enum: 'pending', 'approved', 'denied', 'cancelled')
*   employee_notes (text, nullable)
*   manager_notes (text, nullable) - Reason for denial or comments
*   submitted_at (timestamp)
*   reviewed_at (timestamp, nullable)
*   created_at, updated_at

**Leave Request History Table (Optional for MVP, recommended for audit):**
*   id (primary key)
*   leave_request_id (foreign key)
*   action (enum: 'submitted', 'approved', 'denied', 'cancelled')
*   performed_by_user_id (foreign key to users.id)
*   notes (text, nullable)
*   created_at

### 7.2. Business Rules

*   An employee cannot submit overlapping leave requests (date range validation).
*   A leave request can only be approved/denied by the designated manager.
*   Once approved, a leave request can only be cancelled by the employee (with manager notification) or by the manager.
*   Start date must be before or equal to end date.
*   Leave requests should ideally be submitted at least X days in advance (configurable warning, not enforced initially).

## 8. Non-Functional Requirements
*   **Performance:** The system should be responsive, with quick load times for all pages.
*   **Security:** User authentication, authorization, and protection against common web vulnerabilities (e.g., CSRF, XSS).
*   **Usability:** Intuitive and easy-to-navigate interface for both employees and managers.
*   **Scalability:** Designed to accommodate growth in employee numbers at Horizon Dynamics.
*   **Maintainability:** Clean, well-documented code using Laravel best practices.

## 9. Technology Stack
*   **Backend:** Laravel (PHP Framework)
*   **Frontend:** Tailwind CSS (for styling), Blade (templating engine), Alpine.js (for minor interactivity, if needed).
*   **Database:** MySQL (default, can be configured)
*   **Other:** Composer, npm, Git

## 10. Future Considerations (Out of Scope for initial MVP)
*   Integration with HR systems.
*   Automated accrual tracking for PTO.
*   Detailed reporting and analytics.
*   Company-wide administrator role with global visibility/management.

## 11. Success Metrics
*   Reduction in reported staffing conflicts due to leave.
*   Increased efficiency in time off approval process.
*   High user adoption rate among employees and managers.
*   Positive feedback from users on ease of use and visibility.