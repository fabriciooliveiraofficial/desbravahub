-- ============================================================================
-- DesbravaHub - Seed Data
-- Demo data for development and testing
-- ============================================================================

SET NAMES utf8mb4;

-- ============================================================================
-- LEVELS (Global)
-- ============================================================================

INSERT INTO `levels` (`level_number`, `name`, `min_xp`) VALUES
(1, 'Iniciante', 0),
(2, 'Explorador', 100),
(3, 'Aventureiro', 300),
(4, 'Desbravador', 600),
(5, 'Pioneiro', 1000),
(6, 'Líder', 1500),
(7, 'Mestre', 2200),
(8, 'Guardião', 3000),
(9, 'Campeão', 4000),
(10, 'Lenda', 5500);

-- ============================================================================
-- PERMISSIONS (Global)
-- ============================================================================

INSERT INTO `permissions` (`key`, `name`, `group`) VALUES
-- Dashboard
('dashboard.view', 'Ver Dashboard', 'dashboard'),

-- Users
('users.view', 'Ver Usuários', 'users'),
('users.create', 'Criar Usuários', 'users'),
('users.edit', 'Editar Usuários', 'users'),
('users.delete', 'Excluir Usuários', 'users'),

-- Activities
('activities.view', 'Ver Atividades', 'activities'),
('activities.create', 'Criar Atividades', 'activities'),
('activities.edit', 'Editar Atividades', 'activities'),
('activities.delete', 'Excluir Atividades', 'activities'),

-- Proofs
('proofs.view', 'Ver Provas', 'proofs'),
('proofs.review', 'Revisar Provas', 'proofs'),

-- Quizzes
('quizzes.view', 'Ver Questionários', 'quizzes'),
('quizzes.create', 'Criar Questionários', 'quizzes'),
('quizzes.edit', 'Editar Questionários', 'quizzes'),
('quizzes.delete', 'Excluir Questionários', 'quizzes'),

-- Events
('events.view', 'Ver Eventos', 'events'),
('events.create', 'Criar Eventos', 'events'),
('events.edit', 'Editar Eventos', 'events'),
('events.delete', 'Excluir Eventos', 'events'),

-- Notifications
('notifications.send', 'Enviar Notificações', 'notifications'),
('notifications.broadcast', 'Enviar Broadcast', 'notifications'),

-- Admin
('admin.settings', 'Configurações do Sistema', 'admin'),
('admin.versions', 'Gerenciar Versões', 'admin'),
('admin.features', 'Gerenciar Features', 'admin'),
('admin.reports', 'Ver Relatórios', 'admin');

-- ============================================================================
-- APP VERSIONS (Global)
-- ============================================================================

INSERT INTO `app_versions` (`version_code`, `version_number`, `release_notes`, `is_active`, `released_at`) VALUES
('1.0.0', 100, 'Versão inicial do DesbravaHub', 1, NOW());

-- ============================================================================
-- FEATURE FLAGS (Global)
-- ============================================================================

INSERT INTO `feature_flags` (`key`, `name`, `description`, `default_enabled`) VALUES
('quiz_system', 'Sistema de Questionários', 'Habilita o sistema de questionários para atividades', 1),
('achievements', 'Sistema de Conquistas', 'Habilita conquistas e badges', 1),
('push_notifications', 'Notificações Push', 'Habilita notificações push no navegador', 0),
('social_proof_validation', 'Validação Automática de Redes Sociais', 'Valida automaticamente URLs de redes sociais', 0),
('events_module', 'Módulo de Eventos', 'Habilita o calendário de eventos', 1),
('dark_mode', 'Modo Escuro', 'Permite aos usuários escolher o tema escuro', 1);

-- ============================================================================
-- DEMO TENANT
-- ============================================================================

INSERT INTO `tenants` (`slug`, `name`, `description`, `status`, `settings`) VALUES
('demo-club', 'Clube Demonstração', 'Clube de demonstração para testes', 'active', JSON_OBJECT(
    'theme', 'default',
    'language', 'pt_BR',
    'timezone', 'America/Sao_Paulo'
));

-- Get the tenant ID
SET @tenant_id = LAST_INSERT_ID();

-- ============================================================================
-- DEMO ROLES (for demo tenant)
-- ============================================================================

INSERT INTO `roles` (`tenant_id`, `name`, `display_name`, `description`, `is_system`) VALUES
(@tenant_id, 'admin', 'Administrador', 'Acesso total ao sistema', 1),
(@tenant_id, 'director', 'Diretor', 'Gerencia atividades e usuários do clube', 1),
(@tenant_id, 'pathfinder', 'Desbravador', 'Usuário padrão do clube', 1);

-- Get role IDs
SET @admin_role_id = (SELECT id FROM roles WHERE tenant_id = @tenant_id AND name = 'admin');
SET @director_role_id = (SELECT id FROM roles WHERE tenant_id = @tenant_id AND name = 'director');
SET @pathfinder_role_id = (SELECT id FROM roles WHERE tenant_id = @tenant_id AND name = 'pathfinder');

-- ============================================================================
-- ASSIGN PERMISSIONS TO ROLES
-- ============================================================================

-- Admin gets all permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @admin_role_id, id FROM permissions;

-- Director permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @director_role_id, id FROM permissions 
WHERE `key` IN (
    'dashboard.view',
    'users.view', 'users.create', 'users.edit',
    'activities.view', 'activities.create', 'activities.edit',
    'proofs.view', 'proofs.review',
    'quizzes.view', 'quizzes.create', 'quizzes.edit',
    'events.view', 'events.create', 'events.edit',
    'notifications.send'
);

-- Pathfinder permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @pathfinder_role_id, id FROM permissions 
WHERE `key` IN (
    'dashboard.view',
    'activities.view',
    'quizzes.view',
    'events.view'
);

-- ============================================================================
-- DEMO USERS
-- Password: 'password123' (bcrypt hash)
-- ============================================================================

INSERT INTO `users` (`tenant_id`, `role_id`, `email`, `password_hash`, `name`, `xp_points`, `level_id`, `status`) VALUES
(@tenant_id, @admin_role_id, 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Demo', 0, 1, 'active'),
(@tenant_id, @director_role_id, 'diretor@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Diretor Demo', 500, 2, 'active'),
(@tenant_id, @pathfinder_role_id, 'desbravador@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Desbravador Demo', 150, 2, 'active');

-- ============================================================================
-- DEMO ACTIVITIES
-- ============================================================================

SET @admin_user_id = (SELECT id FROM users WHERE email = 'admin@demo.com' AND tenant_id = @tenant_id);

INSERT INTO `activities` (`tenant_id`, `title`, `description`, `min_level`, `xp_reward`, `is_outdoor`, `proof_types`, `status`, `order_position`, `created_by`) VALUES
(@tenant_id, 'Primeira Caminhada', 'Complete sua primeira caminhada de 2km no parque.', 1, 50, 1, '["upload", "url"]', 'active', 1, @admin_user_id),
(@tenant_id, 'Conhecendo a Bíblia', 'Leia e resuma o primeiro capítulo de Gênesis.', 1, 30, 0, '["upload"]', 'active', 2, @admin_user_id),
(@tenant_id, 'Nó Básico', 'Aprenda e demonstre 3 tipos de nós básicos.', 1, 40, 0, '["upload", "url"]', 'active', 3, @admin_user_id),
(@tenant_id, 'Quiz de História', 'Complete o questionário sobre a história dos Desbravadores.', 1, 25, 0, '["quiz"]', 'active', 4, @admin_user_id),
(@tenant_id, 'Acampamento Noturno', 'Participe de um acampamento noturno.', 2, 100, 1, '["upload"]', 'active', 5, @admin_user_id);

-- Add prerequisite: Acampamento requires Primeira Caminhada
SET @caminhada_id = (SELECT id FROM activities WHERE tenant_id = @tenant_id AND title = 'Primeira Caminhada');
SET @acampamento_id = (SELECT id FROM activities WHERE tenant_id = @tenant_id AND title = 'Acampamento Noturno');

INSERT INTO `activity_prerequisites` (`activity_id`, `prerequisite_activity_id`) VALUES
(@acampamento_id, @caminhada_id);

-- ============================================================================
-- DEMO ACHIEVEMENTS
-- ============================================================================

INSERT INTO `achievements` (`tenant_id`, `name`, `description`, `xp_reward`, `criteria_type`, `criteria_value`) VALUES
(@tenant_id, 'Primeiro Passo', 'Complete sua primeira atividade', 10, 'activities_completed', 1),
(@tenant_id, 'Dedicado', 'Complete 5 atividades', 25, 'activities_completed', 5),
(@tenant_id, 'Explorador', 'Alcance o nível 2', 15, 'level_reached', 2),
(@tenant_id, 'Veterano', 'Alcance o nível 5', 50, 'level_reached', 5),
(@tenant_id, 'Centurião', 'Acumule 100 XP', 20, 'xp_earned', 100);

-- ============================================================================
-- END OF SEED DATA
-- ============================================================================
