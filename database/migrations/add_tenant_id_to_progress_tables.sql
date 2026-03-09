-- ============================================================================
-- Migration: Add tenant_id to user_requirement_progress and user_step_responses
-- Reason: SpecialtyService::deleteAssignment() uses tenant_id for scoped deletes,
--         and initializeRequirements() inserts with tenant_id — both fail without
--         the column.
-- ============================================================================

-- Step 1: Add column as nullable first (required for backfill before NOT NULL)
ALTER TABLE `user_requirement_progress`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`;

-- Step 2: Backfill from parent specialty_assignments
UPDATE `user_requirement_progress` urp
    JOIN `specialty_assignments` sa ON urp.assignment_id = sa.id
    SET urp.tenant_id = sa.tenant_id
WHERE urp.tenant_id IS NULL;

-- Step 3: Enforce NOT NULL now that data is backfilled
ALTER TABLE `user_requirement_progress`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NOT NULL;

-- Step 4: Index for scoped queries + FK for cascade
ALTER TABLE `user_requirement_progress`
    ADD INDEX `idx_urp_tenant` (`tenant_id`),
    ADD CONSTRAINT `fk_urp_tenant`
        FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

-- ----------------------------------------------------------------------------

-- Step 1: Add column as nullable first
ALTER TABLE `user_step_responses`
    ADD COLUMN `tenant_id` INT UNSIGNED NULL AFTER `id`;

-- Step 2: Backfill from parent user_program_progress
UPDATE `user_step_responses` usr
    JOIN `user_program_progress` upp ON usr.progress_id = upp.id
    SET usr.tenant_id = upp.tenant_id
WHERE usr.tenant_id IS NULL;

-- Step 3: Enforce NOT NULL
ALTER TABLE `user_step_responses`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NOT NULL;

-- Step 4: Index + FK
ALTER TABLE `user_step_responses`
    ADD INDEX `idx_usr_tenant` (`tenant_id`),
    ADD CONSTRAINT `fk_usr_tenant`
        FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
