# Dynamic Learning Engine (Specialties & Classes)

## 1. Purpose of This Document

This document is the **single source of truth** for implementing the new **Dynamic Learning Engine** used by the Pathfinder platform.

It is written to be:

* Consulted continuously by an AI Agent (Claude Opus 4.5)
* Used as a technical + product + UX guide
* Free of ambiguity
* Fully aligned with a **multi-tenant, path-based architecture**

This engine replaces all previous hardcoded or static approaches.

---

## 2. Core Vision

The platform is not a list of predefined specialties or classes.

It is a **Learning Execution Engine** where:

* Content is **data**, not code
* Admins define what is taught
* The system enforces learning rules

Each club operates as an **independent LMS**, fully isolated from others.

---

## 3. Multi-Tenant Architecture (Mandatory)

### 3.1 Tenant Isolation

* Each club is a tenant
* Routing is path-based: `/club-slug/...`
* Every database table related to learning MUST include `tenant_id`
* No data is shared between clubs

### 3.2 Tenant-Owned Content

Each tenant independently owns:

* Categories
* Specialties
* Classes
* Versions
* Steps (requirements)
* Questions
* User progress
* Approvals

There are **no global learning entities**.

---

## 4. Unified Learning Model

### 4.1 Learning Program

Everything is modeled as a **Learning Program**.

Program types:

* `specialty`
* `class`

Both types use:

* The same database structure
* The same progress engine
* The same approval workflow

---

## 5. Category System (Tenant-Scoped)

### 5.1 Purpose

Categories organize programs visually and pedagogically.

They exist to:

* Improve UX
* Allow flexible classification
* Avoid hardcoded logic

### 5.2 Category Properties

Each category contains:

* Name
* Type (`specialty`, `class`, or `both`)
* Color (used in UI)
* Icon (optional)
* Description
* Order / priority
* Status (`active`, `archived`)

### 5.3 Category Rules

* Categories are tenant-specific
* Deleting a category must NOT delete programs
* Programs without a category must be handled gracefully

---

## 6. Program Lifecycle & Versioning

### 6.1 Versioning (Critical)

Programs MUST support versioning.

Each version has:

* Version number
* Status:

  * `draft`
  * `published`
  * `archived`

### 6.2 Version Rules

* Editing a published program creates a new draft version
* Users already assigned continue on their version
* New assignments always use the latest published version

This prevents progress loss and inconsistencies.

---

## 7. Program Structure

### 7.1 Steps (Requirements)

Programs are composed of ordered **Steps**.

Each step has:

* Title
* Description
* Order
* Required or optional flag
* Progress rule

### 7.2 Questions / Actions

Each step contains one or more **Questions or Actions**.

Supported types:

* Text answer
* Multiple choice
* File upload
* URL submission
* Manual approval

Each question defines:

* Validation rules
* Required flag
* Weight

---

## 8. Modal-Based Learning UX (Core Experience)

### 8.1 Interaction Model

* User sees list of steps
* Clicking a step opens a **modal**
* All interactions happen inside modals
* No page reloads

### 8.2 Modal Behavior by Question Type

| Type            | UI Element          |
| --------------- | ------------------- |
| Text            | Textarea            |
| Multiple choice | Radio / Checkbox    |
| File            | File input          |
| URL             | URL input           |
| Manual          | Submit for approval |

Modals must be:

* Responsive
* Accessible
* Mobile-first

---

## 9. Progress Engine

### 9.1 Status Flow

Each question and step has a status:

* Not started
* In progress
* Submitted
* Approved
* Rejected

### 9.2 Rules

* Progress auto-saves on every interaction
* Rejected answers keep history
* Approval is mandatory for completion
* Completion requires all required steps approved

---

## 10. Outdoor Programs

Programs marked as `outdoor` behave differently:

* No interactive questions
* Steps contain instructions and criteria
* User submits proof instead of answers
* Approval is mandatory

Allowed proof types:

* File upload
* Video
* URL
* Text report

---

## 11. Roles & Permissions

### 11.1 Admin / Director / Counselor

Can:

* Create, edit, delete categories
* Create, version, publish programs
* Define steps and questions
* Assign programs
* Approve or reject submissions

### 11.2 Pathfinder

Can:

* Access only assigned programs
* Submit answers or proofs
* Track own progress

### 11.3 Guardian

* Read-only access
* Can view progress and achievements
* Cannot submit or approve anything

---

## 12. Notifications System

Notifications must be triggered on:

* Program assignment
* Step submission
* Approval or rejection
* Program completion

Channels:

* Toast (in-app)
* Email (SMTP)
* Push-ready (future)

Users can configure preferences.

---

## 13. Analytics & Insights

Admins should have access to:

* Completion rate per category
* Drop-off points
* Most rejected questions
* Average time per step

These metrics guide pedagogical improvements.

---

## 14. Database Design Guidelines (MySQL)

Core tables include:

* learning_categories
* learning_programs
* program_versions
* program_steps
* program_questions
* user_program_progress
* approval_logs

All learning-related tables MUST include:

* tenant_id
* created_at / updated_at

Indexes and foreign keys are mandatory for performance.

---

## 15. MVC Code Organization

Suggested structure:

* controllers/

  * ProgramController.php
  * LearningController.php
  * ApprovalController.php

* models/

  * Category.php
  * Program.php
  * Version.php
  * Step.php
  * Question.php

* views/

  * admin/programs/
  * user/programs/

---

## 16. Security Rules (Non-Negotiable)

* Validate tenant_id on every request
* Enforce role-based access
* Prevent IDOR vulnerabilities
* Never trust frontend state
* Log approvals and rejections

---

## 17. Forbidden Practices

* Hardcoded specialties or classes
* Global categories
* Backend-only content changes
* Skipping versioning
* Shared data across tenants

---

## 18. Final Guiding Principle

This system must feel like:

* A professional LMS
* A safe educational environment
* A flexible platform for constant change

When uncertain, always choose:
**Flexibility + Isolation + Maintainability**

---

## END OF DOCUMENT