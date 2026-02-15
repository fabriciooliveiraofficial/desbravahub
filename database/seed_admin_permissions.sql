-- ============================================================================
-- DesbravaHub - Production Admin Permission Sync
-- Run this script to restore missing permissions to the platform admin
-- ============================================================================

-- 1. Identify context
SET @tenant_slug = 'clube-demo'; -- Update if different
SET @admin_email = 'fabriciooliveiraofficial@gmail.com';

-- 2. Find IDs
SET @tenant_id = (SELECT id FROM tenants WHERE slug = @tenant_slug);
SET @admin_role_id = (SELECT id FROM roles WHERE tenant_id = @tenant_id AND name = 'admin');

-- 3. Ensure user is Admin and Active
UPDATE users 
SET role_id = @admin_role_id, status = 'active'
WHERE email = @admin_email AND tenant_id = @tenant_id;

-- 4. Seed all permissions to the admin role
-- This ensures that even without code changes, the DB is consistent
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @admin_role_id, id FROM permissions;

-- 5. Verification
SELECT u.name, u.email, r.name as role, p.key as permission
FROM users u
JOIN roles r ON u.role_id = r.id
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE u.email = @admin_email;
