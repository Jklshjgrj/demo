# Implementation Plan - Admin Portal Module

Build the core infrastructure for city officials to manage infrastructure reports, track community trends, and communicate status updates back to citizens.

## User Review Required

> [!IMPORTANT]
> - **Weekly Trends**: Analytics will show a weekly summary of report submissions.
> - **Department Assignments**: Admins can now assign reports to departments (e.g., DPWH, Water District).
> - **Default Admin**: I will provide a default admin account (`admin@fixit.com`) for initial testing.

## Proposed Changes

### [MODIFY] Database Schema
#### [MODIFY] [schema.sql](file:///c:/Users/ACER/Documents/fixit_c/schema.sql)
- Add `assigned_department` VARCHAR(100) column to the `reports` table.
- Provide a seed script to create a default admin user:
    - Email: `admin@fixit.com`
    - Password: `admin123` (Hashed)
    - Role: `admin`

### [NEW] Admin Features
#### [MODIFY] [admin/dashboard.php](file:///c:/Users/ACER/Documents/fixit_c/admin/dashboard.php)
- Integrate **Chart.js**:
    - **Category Split**: Doughnut chart.
    - **Weekly Trends**: Line chart showing weekly totals for the last 8 weeks.
- Real-time KPI counts.

#### [NEW] [admin/manage_reports.php](file:///c:/Users/ACER/Documents/fixit_c/admin/manage_reports.php)
- Master table with:
    - Search / Type / Status / Severity filters.
    - **Department Filter**: Filter by assigned department.
- Action: **"Update"** modal to change status, assign department, and add notes.

### [NEW] Backend & API
#### [NEW] [api/admin_update_report.php](file:///c:/Users/ACER/Documents/fixit_c/api/admin_update_report.php)
- Securly handle status changes and department assignments.
- Log all actions in `status_logs`.

## Verification Plan

### Manual Verification
- Log in with the new **Admin Account** and verify sidebar accessibility.
- Assign a report to "DPWH" and verify it shows up in filtered results.
- Verify the Weekly Chart correctly groups data by week.
