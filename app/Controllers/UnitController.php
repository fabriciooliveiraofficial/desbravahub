<?php
/**
 * Unit Controller
 * 
 * Handles unit (unidade) management for admin.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\UnitService;

class UnitController
{
    /**
     * List all units
     */
    public function index(): void
    {
        $this->requirePermission('units.view');

        $tenant = App::tenant();
        $user = App::user();
        $units = UnitService::getAll();

        // Get counselors and members for each unit
        foreach ($units as &$unit) {
            $unit['counselors'] = UnitService::getCounselors($unit['id']);
            $unit['members'] = UnitService::getMembers($unit['id']);
        }

        \App\Core\View::render('admin/units/index', [
            'tenant' => $tenant,
            'user' => $user,
            'units' => $units,
            'pageTitle' => 'Gerenciar Unidades',
            'pageIcon' => 'groups'
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->requirePermission('units.create');

        $tenant = App::tenant();
        $user = App::user();
        $unit = null;
        $counselors = UnitService::getAvailableCounselors();
        $pathfinders = UnitService::getAvailablePathfinders();

        \App\Core\View::render('admin/units/edit', [
            'tenant' => $tenant,
            'user' => $user,
            'unit' => null,
            'counselors' => $counselors,
            'pathfinders' => $pathfinders,
            'pageTitle' => 'Nova Unidade',
            'pageIcon' => 'groups'
        ]);
    }

    /**
     * Store new unit
     */
    public function store(): void
    {
        $this->requirePermission('units.create');

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $color = trim($_POST['color'] ?? '#00d9ff');
        $mascot = trim($_POST['mascot'] ?? '');
        $motto = trim($_POST['motto'] ?? '');

        if (empty($name)) {
            $this->json(['error' => 'Nome da unidade é obrigatório'], 400);
            return;
        }

        $unitId = UnitService::create([
            'name' => $name,
            'description' => $description,
            'color' => $color,
            'mascot' => $mascot,
            'motto' => $motto,
        ]);

        // Assign counselors if provided
        $counselorIds = $_POST['counselors'] ?? [];
        foreach ($counselorIds as $i => $counselorId) {
            UnitService::assignCounselor($unitId, (int) $counselorId, $i === 0);
        }

        // Assign pathfinders if provided
        $pathfinderIds = $_POST['pathfinders'] ?? [];
        foreach ($pathfinderIds as $pathfinderId) {
            UnitService::assignMember($unitId, (int) $pathfinderId);
        }

        $tenant = App::tenant();
        header('Location: ' . base_url($tenant['slug'] . '/admin/unidades'));
    }

    /**
     * Show edit form
     */
    public function edit(array $params): void
    {
        $this->requirePermission('units.edit');

        $tenant = App::tenant();
        $user = App::user();
        $unitId = (int) $params['id'];

        $unit = UnitService::getById($unitId);
        if (!$unit) {
            http_response_code(404);
            echo "Unidade não encontrada";
            return;
        }

        $unit['counselors'] = UnitService::getCounselors($unitId);
        $unit['members'] = UnitService::getMembers($unitId);

        $counselors = UnitService::getAvailableCounselors();
        $pathfinders = UnitService::getAvailablePathfinders();

        \App\Core\View::render('admin/units/edit', [
            'tenant' => $tenant,
            'user' => $user,
            'unit' => $unit,
            'counselors' => $counselors,
            'pathfinders' => $pathfinders,
            'pageTitle' => 'Editar Unidade',
            'pageIcon' => 'groups'
        ]);
    }

    /**
     * Update unit
     */
    public function update(array $params): void
    {
        $this->requirePermission('units.edit');

        $unitId = (int) $params['id'];

        $unit = UnitService::getById($unitId);
        if (!$unit) {
            $this->json(['error' => 'Unidade não encontrada'], 404);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $this->json(['error' => 'Nome da unidade é obrigatório'], 400);
            return;
        }

        UnitService::update($unitId, [
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'color' => trim($_POST['color'] ?? '#00d9ff'),
            'mascot' => trim($_POST['mascot'] ?? ''),

            'motto' => trim($_POST['motto'] ?? ''),
        ]);

        // Sync Counselors
        $counselorIds = $_POST['counselors'] ?? [];
        $currentCounselors = array_column(UnitService::getCounselors($unitId), 'id');

        // Remove missing
        foreach ($currentCounselors as $cid) {
            if (!in_array($cid, $counselorIds)) {
                UnitService::removeCounselor($unitId, $cid);
            }
        }
        // Add/Update
        foreach ($counselorIds as $i => $cid) {
            UnitService::assignCounselor($unitId, (int) $cid, $i === 0);
        }

        // Sync Pathfinders
        $pathfinderIds = $_POST['pathfinders'] ?? [];
        $currentMembers = array_column(UnitService::getMembers($unitId), 'id');

        // Remove missing
        foreach ($currentMembers as $uid) {
            if (!in_array($uid, $pathfinderIds)) {
                UnitService::removeMember($uid);
            }
        }
        // Add new
        foreach ($pathfinderIds as $uid) {
            UnitService::assignMember($unitId, (int) $uid);
        }

        $tenant = App::tenant();
        header('Location: ' . base_url($tenant['slug'] . '/admin/unidades'));
    }

    /**
     * Delete unit
     */
    public function delete(array $params): void
    {
        $this->requirePermission('units.delete');

        $unitId = (int) $params['id'];

        UnitService::delete($unitId);

        $this->json(['success' => true]);
    }

    /**
     * Add counselor to unit
     */
    public function addCounselor(array $params): void
    {
        $this->requirePermission('units.assign');

        $unitId = (int) $params['id'];
        $userId = (int) ($_POST['user_id'] ?? 0);
        $isPrimary = (bool) ($_POST['is_primary'] ?? false);

        if (!$userId) {
            $this->json(['error' => 'Selecione um conselheiro'], 400);
            return;
        }

        UnitService::assignCounselor($unitId, $userId, $isPrimary);

        $this->json(['success' => true]);
    }

    /**
     * Remove counselor from unit
     */
    public function removeCounselor(array $params): void
    {
        $this->requirePermission('units.assign');

        $unitId = (int) $params['id'];
        $userId = (int) $params['user_id'];

        UnitService::removeCounselor($unitId, $userId);

        $this->json(['success' => true]);
    }

    /**
     * Add member to unit
     */
    public function addMember(array $params): void
    {
        $this->requirePermission('units.assign');

        $unitId = (int) $params['id'];
        $userId = (int) ($_POST['user_id'] ?? 0);

        if (!$userId) {
            $this->json(['error' => 'Selecione um desbravador'], 400);
            return;
        }

        UnitService::assignMember($unitId, $userId);

        $this->json(['success' => true]);
    }

    /**
     * Remove member from unit
     */
    public function removeMember(array $params): void
    {
        $this->requirePermission('units.assign');

        $userId = (int) $params['user_id'];

        UnitService::removeMember($userId);

        $this->json(['success' => true]);
    }

    /**
     * Check permission helper
     */
    private function requirePermission(string $permission): void
    {
        $user = App::user();
        $roleName = $user['role_name'] ?? '';

        // Admins and Directors have all permissions
        if (in_array($roleName, ['admin', 'director', 'associate_director'])) {
            return;
        }

        if (!can($permission)) {
            http_response_code(403);
            echo json_encode(['error' => 'Permissão negada']);
            exit;
        }
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
