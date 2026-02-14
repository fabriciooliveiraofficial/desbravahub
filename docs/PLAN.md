# PLAN: Proofs Evaluation Center Upgrade & Submission Fix

## 1. Goal
Resolution of the "JSON Syntax Error" during submission and a complete redesign of the Leader's Evaluation Center to include missing context (questions) and interactive media (embeds).

## 2. Technical Strategy

### ⚡ Phase 1: Global JSON Hardening
- **Target**: `LearningController::json` (and base controller if possible).
- **Action**: Use `ob_clean()` to wipe any PHP warnings/notices that might have been echoed before the JSON payload.
- **Action**: Fix specific logic in `submitStep` (checking for undefined indices in `$_POST['answers']`).

### 📊 Phase 2: Data Enrichment (`AdminController.php`)
- **Action**: Modify `AdminController::proofs` to stop flattening responses into a single string.
- **New Structure**: If `kind === 'program'`, provide a `structured_content` array containing `question`, `answer`, and `type`.
- **Media Detection**: Implement a helper to flag URLs as "embeddable" (YouTube, Vimeo, etc.).

### 🎨 Phase 3: Dashboard Redesign (`admin/proofs.php`)
- **Aesthetics**: Apply **"Master Design v2.0"** (Light Mode, Cyan accents, Slate grays, Clean Cards).
- **Question Layout**: Show the question text clearly above each answer in high-contrast cards.
- **Rich Media**: Implement responsive `<iframe>` embeds for detected social media/video links.
- **Interaction**: Use standard admin layout patterns for consistency.

### 🖥️ Phase 4: Focused Review Overhaul (`approvals/review.php`)
- **Action**: Modify `ApprovalController::review` to provide `structured_content` (question/answer pairs) instead of a pre-formatted string.
- **Redesign**: Apply Master Design v2.0 (Light mode, centered workspace, standard admin card shadows).
- **Dynamic Content**: Update the JavaScript logic in `review.php` to render the cards and media embeds for each step in a clean, high-contrast light theme.

## 3. Agents Invoked
- `project-planner`: Architecture & Plan Writing.
- `debugger`: JSON error squashing & logic verification.
- `backend-specialist`: Controller logic & Data structure updates.
- `frontend-specialist`: UI Redesign & Embed implementation.

## 4. Verification Plan
- **Pre-flight**: Run `lint_runner.py` on modified controllers.
- **Backend Test**: Verify JSON responses via `curl` or browser console.
- **Frontend Test**:
    - Confirm questions are visible on `/admin/proofs`.
    - Confirm YouTube videos play within the card.
    - Confirm "Deep Glass" aesthetics are active.
