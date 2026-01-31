<?php
/**
 * Category Controller
 * 
 * Manages tenant-scoped learning categories (CRUD).
 * Categories can be type: specialty, class, or both.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\View;

class CategoryController
{
    /**
     * Require admin/director role
     */
    private function requireAdmin(): void
    {
        $user = App::user();
        $role = $user['role_name'] ?? '';

        if (!in_array($role, ['admin', 'director'])) {
            error_log("CategoryController::requireAdmin - Access Denied: User " . ($user['id'] ?? 'unknown') . " with role $role tried to access categories.");
            header('HTTP/1.0 403 Forbidden');
            echo 'Acesso negado';
            exit;
        }
    }

    /**
     * List all categories for the tenant
     */
    public function index(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();

        // Get all categories for this tenant
        $categories = db_fetch_all(
            "SELECT * FROM learning_categories WHERE tenant_id = ? ORDER BY sort_order, name",
            [$tenant['id']]
        );

        View::render('admin/categories/index', [
            'tenant' => $tenant,
            'user' => $user,
            'categories' => $categories
        ]);
    }

    /**
     * Show category with its programs
     */
    public function show(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();
        $categoryId = (int) ($params['id'] ?? 0);

        // Get category
        $category = db_fetch_one(
            "SELECT * FROM learning_categories WHERE id = ? AND tenant_id = ?",
            [$categoryId, $tenant['id']]
        );

        if (!$category) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/categorias'));
            return;
        }

        // Get programs in this category
        $programs = db_fetch_all(
            "SELECT * FROM learning_programs WHERE category_id = ? AND tenant_id = ? ORDER BY name",
            [$categoryId, $tenant['id']]
        );

        // Get assignment counts for each program
        foreach ($programs as &$program) {
            $program['assigned_count'] = db_fetch_column(
                "SELECT COUNT(*) FROM user_program_progress WHERE program_id = ? AND tenant_id = ?",
                [$program['id'], $tenant['id']]
            ) ?: 0;

            $program['completed_count'] = db_fetch_column(
                "SELECT COUNT(*) FROM user_program_progress WHERE program_id = ? AND tenant_id = ? AND status = 'completed'",
                [$program['id'], $tenant['id']]
            ) ?: 0;
        }

        View::render('admin/categories/show', [
            'tenant' => $tenant,
            'user' => $user,
            'category' => $category,
            'programs' => $programs
        ]);
    }

    /**
     * Store new category (AJAX)
     */
    public function store(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'specialty';
        $color = $_POST['color'] ?? '#00D9FF';
        $icon = $_POST['icon'] ?? '📚';
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $this->json(['error' => 'Nome é obrigatório'], 400);
            return;
        }

        // Get max sort_order
        $maxOrder = db_fetch_column(
            "SELECT MAX(sort_order) FROM learning_categories WHERE tenant_id = ?",
            [$tenant['id']]
        ) ?? 0;

        try {
            $id = db_insert('learning_categories', [
                'tenant_id' => $tenant['id'],
                'name' => $name,
                'type' => $type,
                'color' => $color,
                'icon' => $icon,
                'description' => $description,
                'sort_order' => $maxOrder + 1,
                'status' => 'active'
            ]);

            $this->json([
                'success' => true,
                'message' => 'Categoria criada!',
                'category' => [
                    'id' => $id,
                    'name' => $name,
                    'type' => $type,
                    'color' => $color,
                    'icon' => $icon
                ]
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao criar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update category (AJAX)
     */
    public function update(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $id = (int) ($params['id'] ?? 0);

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'specialty';
        $color = $_POST['color'] ?? '#00D9FF';
        $icon = $_POST['icon'] ?? '📚';
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $this->json(['error' => 'Nome é obrigatório'], 400);
            return;
        }

        try {
            db_update('learning_categories', [
                'name' => $name,
                'type' => $type,
                'color' => $color,
                'icon' => $icon,
                'description' => $description
            ], 'id = ? AND tenant_id = ?', [$id, $tenant['id']]);

            $this->json([
                'success' => true,
                'message' => 'Categoria atualizada!'
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao atualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete category (AJAX)
     */
    public function delete(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $id = (int) ($params['id'] ?? 0);

        // Check if category has programs
        $programCount = db_fetch_column(
            "SELECT COUNT(*) FROM learning_programs WHERE category_id = ? AND tenant_id = ?",
            [$id, $tenant['id']]
        );

        if ($programCount > 0) {
            // Archive instead of delete
            db_update('learning_categories', ['status' => 'archived'], 'id = ? AND tenant_id = ?', [$id, $tenant['id']]);
            $this->json([
                'success' => true,
                'message' => 'Categoria arquivada (possui programas vinculados)'
            ]);
            return;
        }

        try {
            db_delete('learning_categories', 'id = ? AND tenant_id = ?', [$id, $tenant['id']]);
            $this->json([
                'success' => true,
                'message' => 'Categoria excluída!'
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao excluir: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete category with cascade (deletes category, all programs, and all user assignments)
     * WARNING: This is a destructive operation!
     */
    public function deleteCascade(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $id = (int) ($params['id'] ?? 0);

        // Verify category belongs to this tenant
        $category = db_fetch_one(
            "SELECT * FROM learning_categories WHERE id = ? AND tenant_id = ?",
            [$id, $tenant['id']]
        );

        if (!$category) {
            $this->json(['error' => 'Categoria não encontrada'], 404);
            return;
        }

        try {
            // Get all program IDs in this category
            $programs = db_fetch_all(
                "SELECT id FROM learning_programs WHERE category_id = ? AND tenant_id = ?",
                [$id, $tenant['id']]
            );

            $programIds = array_column($programs, 'id');
            $deletedPrograms = count($programIds);
            $deletedAssignments = 0;

            if (!empty($programIds)) {
                $placeholders = implode(',', array_fill(0, count($programIds), '?'));

                // Delete all user progress/assignments for these programs
                $deletedAssignments = db_execute(
                    "DELETE FROM user_program_progress WHERE program_id IN ($placeholders) AND tenant_id = ?",
                    array_merge($programIds, [$tenant['id']])
                );

                // Delete all requirement progress for these programs
                db_execute(
                    "DELETE FROM user_requirement_progress WHERE program_id IN ($placeholders) AND tenant_id = ?",
                    array_merge($programIds, [$tenant['id']])
                );

                // Delete all requirements for these programs
                db_execute(
                    "DELETE FROM program_requirements WHERE program_id IN ($placeholders)",
                    $programIds
                );

                // Delete all programs in this category
                db_execute(
                    "DELETE FROM learning_programs WHERE category_id = ? AND tenant_id = ?",
                    [$id, $tenant['id']]
                );
            }

            // Finally, delete the category itself
            db_delete('learning_categories', 'id = ? AND tenant_id = ?', [$id, $tenant['id']]);

            $this->json([
                'success' => true,
                'message' => "Categoria excluída! ($deletedPrograms programas e suas atribuições foram removidos)"
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao excluir: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reorder categories (AJAX)
     */
    public function reorder(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $input = json_decode(file_get_contents('php://input'), true);
        $order = $input['order'] ?? [];

        try {
            foreach ($order as $index => $id) {
                db_update('learning_categories', ['sort_order' => $index], 'id = ? AND tenant_id = ?', [(int) $id, $tenant['id']]);
            }

            $this->json(['success' => true, 'message' => 'Ordem salva!']);

        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao reordenar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * JSON response helper
     */
    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
