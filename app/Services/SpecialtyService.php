<?php
/**
 * Specialty Service
 * 
 * Handles specialty repository and assignments.
 */

namespace App\Services;

class SpecialtyService
{
    private static ?array $categories = null;
    private static ?array $specialties = null;

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        if (self::$categories === null) {
            $path = BASE_PATH . '/storage/specialties/categories.json';
            if (file_exists($path)) {
                $data = json_decode(file_get_contents($path), true);
                self::$categories = $data['categories'] ?? [];
            } else {
                self::$categories = [];
            }
        }
        return self::$categories;
    }

    /**
     * Get category by ID
     */
    public static function getCategory(string $id): ?array
    {
        foreach (self::getCategories() as $cat) {
            if ($cat['id'] === $id) {
                return $cat;
            }
        }
        return null;
    }

    /**
     * Get all specialties (Repository + Custom)
     */
    public static function getSpecialties(?int $tenantId = null): array
    {
        // Load static repository
        if (self::$specialties === null) {
            $path = BASE_PATH . '/storage/specialties/specialties_repository.json';
            if (file_exists($path)) {
                $data = json_decode(file_get_contents($path), true);
                self::$specialties = $data['specialties'] ?? [];
            } else {
                self::$specialties = [];
            }
        }

        $allSpecialties = self::$specialties;

        // Fetch custom specialties from DB if tenant provided
        if ($tenantId !== null) {
            try {
                // Get hidden items for this tenant
                $hiddenRows = db_fetch_all(
                    "SELECT item_id FROM tenant_hidden_items WHERE tenant_id = ?",
                    [$tenantId]
                );
                $hiddenItems = array_column($hiddenRows, 'item_id');


                $customSpecialties = db_fetch_all(
                    "SELECT *, 'custom' as source FROM specialties WHERE tenant_id = ? AND status = 'active'",
                    [$tenantId]
                );

                // Merge custom specialties
                foreach ($customSpecialties as $custom) {
                    $allSpecialties[] = [
                        'id' => $custom['id'],
                        'name' => $custom['name'],
                        'category_id' => $custom['category_id'],
                        'badge_icon' => $custom['badge_icon'],
                        'type' => $custom['type'],
                        'duration_hours' => $custom['duration_hours'],
                        'difficulty' => $custom['difficulty'],
                        'xp_reward' => $custom['xp_reward'],
                        'description' => $custom['description'],
                        'requirements' => [], // Will be loaded separately if needed
                        'is_custom' => true
                    ];
                }

                // Filter out hidden items
                if (!empty($hiddenItems)) {
                    $allSpecialties = array_filter($allSpecialties, function($s) use ($hiddenItems) {
                        return !in_array($s['id'], $hiddenItems);
                    });
                }

            } catch (\Exception $e) {
                // Table might not exist yet
            }
        }

        return $allSpecialties;
    }

    /**
     * Get specialty by ID (Repository + Custom)
     */
    public static function getSpecialty(string $id): ?array
    {
        // Check if it's a Learning Program (prog_ prefix)
        if (str_starts_with($id, 'prog_')) {
            $programId = substr($id, 5);
            $program = db_fetch_one("SELECT * FROM learning_programs WHERE id = ?", [$programId]);
            
            if ($program) {
                // Get category for color/icon
                $cat = db_fetch_one("SELECT * FROM learning_categories WHERE id = ?", [$program['category_id']]);
                
                return [
                    'id' => $id,
                    'name' => $program['name'],
                    'category_id' => 'lc_' . $program['category_id'],
                    'badge_icon' => $program['icon'],
                    'type' => $program['type'] ?? 'specialty',
                    'duration_hours' => $program['duration_hours'],
                    'difficulty' => $program['difficulty'],
                    'xp_reward' => $program['xp_reward'],
                    'description' => $program['description'],
                    'requirements' => [], // Should be loaded via getRequirementsWithProgress which handles DB steps
                    'is_custom' => false,
                    'is_program' => true,
                    'program_id' => $program['id'],
                    'status' => $program['status'], // Crucial for visibility checks
                    'category' => $cat ? [
                        'id' => 'lc_' . $cat['id'],
                        'name' => $cat['name'],
                        'color' => $cat['color'] ?? '#00d9ff',
                        'icon' => $cat['icon']
                    ] : null
                ];
            }
        }

        // Check Static Repository first
        foreach (self::getSpecialties() as $spec) {
            if ($spec['id'] === $id) {
                $spec['category'] = self::getCategory($spec['category_id']);
                return $spec;
            }
        }

        // Check DB for custom specialty
        try {
            $custom = db_fetch_one("SELECT * FROM specialties WHERE id = ?", [$id]);
            if ($custom) {
                return [
                    'id' => $custom['id'],
                    'name' => $custom['name'],
                    'category_id' => $custom['category_id'],
                    'badge_icon' => $custom['badge_icon'],
                    'type' => $custom['type'],
                    'duration_hours' => $custom['duration_hours'],
                    'difficulty' => $custom['difficulty'],
                    'xp_reward' => $custom['xp_reward'],
                    'description' => $custom['description'],
                    'requirements' => [],
                    'is_custom' => true,
                    'status' => $custom['status'],
                    'category' => self::getCategory($custom['category_id'])
                ];
            }
        } catch (\Exception $e) {
            // Table might not exist
        }

        return null;
    }

    /**
     * Get specialties by category
     */
    public static function getByCategory(string $categoryId, ?int $tenantId = null): array
    {
        return array_filter(self::getSpecialties($tenantId), function ($spec) use ($categoryId) {
            return $spec['category_id'] === $categoryId;
        });
    }

    /**
     * Search specialties
     */
    public static function search(string $query, ?int $tenantId = null): array
    {
        $queryLower = strtolower($query);
        $results = [];

        // 0. Search Legacy Classes (Hardcoded)
        $legacyClasses = [
            ['id' => 'amigo', 'name' => 'Amigo', 'icon' => '🌱'],
            ['id' => 'companheiro', 'name' => 'Companheiro', 'icon' => '🌿'],
            ['id' => 'pesquisador', 'name' => 'Pesquisador', 'icon' => '🔍'],
            ['id' => 'pioneiro', 'name' => 'Pioneiro', 'icon' => '🏕️'],
            ['id' => 'excursionista', 'name' => 'Excursionista', 'icon' => '🥾'],
            ['id' => 'guia', 'name' => 'Guia', 'icon' => '🧭'],
        ];

        foreach ($legacyClasses as $lClass) {
            if (str_contains(strtolower($lClass['name']), $queryLower)) {
                $results[] = [
                    'id' => 'class_' . $lClass['id'],
                    'name' => $lClass['name'],
                    'badge_icon' => $lClass['icon'],
                    'type' => 'class',
                    'is_program' => false,
                    'is_legacy_class' => true
                ];
            }
        }
        
        // 1. Search legacy/static/custom specialties
        $results = array_filter(self::getSpecialties($tenantId), function ($spec) use ($queryLower) {
            return str_contains(strtolower($spec['name']), $queryLower) ||
                str_contains(strtolower($spec['description'] ?? ''), $queryLower);
        });

        // 2. Search new Learning Programs
        if ($tenantId) {
            try {
                // Use parameterized query for safety
                $programs = db_fetch_all(
                    "SELECT * FROM learning_programs 
                     WHERE tenant_id = ? 
                     AND (name LIKE ? OR description LIKE ?)
                     LIMIT 10",
                    [$tenantId, '%' . $query . '%', '%' . $query . '%']
                );

                foreach ($programs as $prog) {
                    $results[] = [
                        'id' => 'prog_' . $prog['id'],
                        'name' => $prog['name'],
                        'badge_icon' => $prog['icon'] ?? ($prog['type'] === 'class' ? '🎖️' : '🎯'),
                        'type' => $prog['type'],
                        'duration_hours' => $prog['estimated_hours'] ?? 0,
                        'difficulty' => $prog['difficulty'] ?? 1,
                        'xp_reward' => $prog['xp_reward'] ?? 0,
                        'description' => $prog['description'],
                        'is_program' => true
                    ];
                }
            } catch (\Exception $e) {
                // Table might not exist yet
            }
        }

        return array_values($results);
    }

    /**
     * Assign specialty to user
     */
    public static function assign(
        int $tenantId,
        string $specialtyId,
        int $userId,
        int $assignedBy,
        ?string $dueDate = null,
        ?string $instructions = null
    ): int {
        // Handle Program assignments
        if (str_starts_with($specialtyId, 'prog_')) {
            $progId = (int) substr($specialtyId, 5);
            
            // Get published version
            $version = db_fetch_one(
                "SELECT id FROM program_versions WHERE program_id = ? AND status = 'published' ORDER BY version_number DESC LIMIT 1",
                [$progId]
            );
            
            if (!$version) {
                throw new \Exception("Esta missão (Programa) não possui uma versão publicada e não pode ser liberada.");
            }

            // PREVENT DUPLICATES: Check if user already has this program assigned
            $existing = db_fetch_one("SELECT id FROM user_program_progress WHERE program_id = ? AND user_id = ? AND tenant_id = ?", [$progId, $userId, $tenantId]);
            if ($existing) {
                return (int) $existing['id'];
            }

            return db_insert('user_program_progress', [
                'tenant_id' => $tenantId,
                'program_id' => $progId,
                'version_id' => $version['id'],
                'user_id' => $userId,
                'status' => 'not_started',
                'progress_percent' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Standard Specialty assignment
        return db_insert('specialty_assignments', [
            'tenant_id' => $tenantId,
            'specialty_id' => $specialtyId,
            'user_id' => $userId,
            'assigned_by' => $assignedBy,
            'due_date' => $dueDate,
            'instructions' => $instructions,
            'status' => 'pending'
        ]);
    }

    /**
     * Get user's assignments (Specialties + Programs)
     */
    public static function getUserAssignments(int $userId, int $tenantId): array
    {
        $assignments = [];

        // 1. Fetch Specialties
        $specs = db_fetch_all(
            "SELECT sa.*, u.name as assigned_by_name
             FROM specialty_assignments sa
             LEFT JOIN users u ON sa.assigned_by = u.id
             WHERE sa.user_id = ? AND sa.tenant_id = ? AND sa.status != 'cancelled'
             ORDER BY sa.created_at DESC",
            [$userId, $tenantId]
        );

        foreach ($specs as $s) {
            $s['specialty'] = self::getSpecialty($s['specialty_id']);
            $s['type_label'] = 'specialty';
            $s['assignment_id'] = 'spec_' . $s['id'];
            $assignments[] = $s;
        }

        // 2. Fetch Programs
    $progs = db_fetch_all(
        "SELECT upp.*, p.name as program_name, p.icon as program_icon, p.type as program_type,
                (SELECT COUNT(*) FROM program_steps ps WHERE ps.version_id = upp.version_id) as total_steps,
                (SELECT COUNT(*) FROM user_step_responses usr WHERE usr.progress_id = upp.id AND usr.status IN ('draft', 'submitted', 'approved', 'rejected')) as answered_steps
         FROM user_program_progress upp
         JOIN learning_programs p ON upp.program_id = p.id
         WHERE upp.user_id = ? AND upp.tenant_id = ?
         ORDER BY upp.created_at DESC",
        [$userId, $tenantId]
    );
        foreach ($progs as $p) {
            $status = $p['status'] === 'not_started' ? 'pending' : $p['status'];
            $assignments[] = [
                'id' => $p['id'],
                'assignment_id' => 'prog_' . $p['id'],
                'tenant_id' => $tenantId,
                'specialty_id' => 'prog_' . $p['program_id'],
                'user_id' => $userId,
                'status' => $status,
                'created_at' => $p['created_at'],
                'assigned_by_name' => 'Sistema', // Programs don't track who assigned them in the table currently
                'specialty' => [
                    'id' => 'prog_' . $p['program_id'],
                    'name' => $p['program_name'],
                    'badge_icon' => $p['program_icon'],
                    'type' => $p['program_type'] ?? 'class'
                ],
                'type_label' => 'program',
                'total_steps' => $p['total_steps'],
                'answered_steps' => $p['answered_steps'],
                'progress_percent' => $p['progress_percent']
            ];
        }

        // Sort by created_at DESC
        usort($assignments, function($a, $b) {
            return strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? '');
        });

        return $assignments;
    }

    /**
     * Get assignment by ID
     */
    public static function getAssignment(int $id, int $tenantId): ?array
    {
        $assignment = db_fetch_one(
            "SELECT sa.*, u.name as assigned_by_name
             FROM specialty_assignments sa
             LEFT JOIN users u ON sa.assigned_by = u.id
             WHERE sa.id = ? AND sa.tenant_id = ?",
            [$id, $tenantId]
        );

        if ($assignment) {
            $assignment['specialty'] = self::getSpecialty($assignment['specialty_id']);
        }

        return $assignment;
    }

    /**
     * Start assignment
     */
    public static function startAssignment(int $id): bool
    {
        return db_query(
            "UPDATE specialty_assignments SET status = 'in_progress', started_at = NOW() WHERE id = ?",
            [$id]
        ) !== false;
    }

    /**
     * Complete assignment
     */
    public static function completeAssignment(int $id, int $xpEarned): bool
    {
        return db_query(
            "UPDATE specialty_assignments SET status = 'completed', completed_at = NOW(), xp_earned = ? WHERE id = ?",
            [$xpEarned, $id]
        ) !== false;
    }

    /**
     * Get Unified Assignment (Specialty or Program) by String ID (spec_X or prog_X)
     */
    public static function getUnifiedAssignment(string $unifiedId, int $tenantId): ?array
    {
        if (str_starts_with($unifiedId, 'spec_')) {
            $id = (int)substr($unifiedId, 5);
            $ass = self::getAssignment($id, $tenantId);
            if ($ass) {
                $ass['type_label'] = 'specialty';
                $ass['assignment_id'] = $unifiedId;
                // Load requirements if not present
                if (empty($ass['specialty']['requirements'])) {
                     // Fetch requirements logic (simplified)
                     // In a real scenario we might need to fetch from JSON or DB
                }
            }
            return $ass;
        }

        if (str_starts_with($unifiedId, 'prog_')) {
            $id = (int)substr($unifiedId, 5);
            $prog = db_fetch_one(
                "SELECT upp.*, p.name as program_name, p.icon as program_icon, 
                        p.description as program_description, p.xp_reward, p.duration_hours,
                        p.type as program_type
                 FROM user_program_progress upp
                 JOIN learning_programs p ON upp.program_id = p.id
                 WHERE upp.id = ? AND upp.tenant_id = ?",
                [$id, $tenantId]
            );

            if ($prog) {
                 return [
                    'id' => $prog['id'],
                    'program_id' => $prog['program_id'], // Added missing key
                    'assignment_id' => $unifiedId,
                    'tenant_id' => $tenantId,
                    'specialty_id' => 'prog_' . $prog['program_id'], // Virtual ID
                    'user_id' => $prog['user_id'],
                    'status' => $prog['status'] === 'not_started' ? 'pending' : $prog['status'],
                    'created_at' => $prog['created_at'],
                    'updated_at' => $prog['updated_at'],
                    'assigned_by_name' => 'Sistema', 
                    'type_label' => 'program',
                    'specialty' => [
                        'id' => 'prog_' . $prog['program_id'],
                        'name' => $prog['program_name'],
                        'description' => $prog['program_description'],
                        'badge_icon' => $prog['program_icon'],
                        'xp_reward' => $prog['xp_reward'],
                        'duration_hours' => $prog['duration_hours'],
                        'type' => $prog['program_type'] ?? 'class',
                        'is_program' => true
                    ]
                 ];
            }
        }

        return null;
    }

    /**
     * Get all assignments for tenant (admin view)
     */
    public static function getTenantAssignments(int $tenantId, ?string $status = null): array
    {
        $sql = "SELECT sa.*, u.name as user_name, u.email as user_email, ab.name as assigned_by_name
                FROM specialty_assignments sa
                JOIN users u ON sa.user_id = u.id
                LEFT JOIN users ab ON sa.assigned_by = ab.id
                WHERE sa.tenant_id = ?";
        $params = [$tenantId];

        if ($status) {
            $sql .= " AND sa.status = ?";
            $params[] = $status;
        }

        // Limit to 500 recent assignments for performance
        $sql .= " ORDER BY sa.created_at DESC LIMIT 500";

        $assignments = db_fetch_all($sql, $params);

        foreach ($assignments as &$a) {
            $a['specialty'] = self::getSpecialty($a['specialty_id']);
            $a['type_label'] = 'specialty'; // Marker for UI
            // Ensure ID is string for distinct handling in frontend
            $a['assignment_id'] = 'spec_' . $a['id'];
        }

        // FETCH PROGRAM/CLASS ASSIGNMENTS
        // We only fetch these if we're not filtering by a status that doesn't map well, 
        // or we map 'pending' -> 'not_started'
        
        $programStatus = null;
        if ($status) {
            if ($status === 'pending') $programStatus = 'not_started';
            elseif ($status === 'in_progress') $programStatus = 'in_progress';
            elseif ($status === 'completed') $programStatus = 'completed';
            // 'pending_review' doesn't exist for programs yet in this context
        }

        $progSql = "SELECT upp.id, upp.program_id, upp.user_id, upp.status, upp.created_at, upp.updated_at,
                           u.name as user_name, u.email as user_email,
                           p.name as program_name, p.icon as program_icon, p.type as program_type
                    FROM user_program_progress upp
                    JOIN users u ON upp.user_id = u.id
                    JOIN learning_programs p ON upp.program_id = p.id
                    WHERE upp.tenant_id = ?";
        
        $progParams = [$tenantId];

        if ($status) {
            if ($programStatus) {
                $progSql .= " AND upp.status = ?";
                $progParams[] = $programStatus;
            } else {
                // If filtering by 'pending_review' and programs don't have it, fetch nothing
                $progSql .= " AND 1=0"; 
            }
        }

        $progSql .= " ORDER BY upp.created_at DESC LIMIT 500";

        $programAssignments = db_fetch_all($progSql, $progParams);

        foreach ($programAssignments as $pa) {
            // Map to unified structure
            $assignments[] = [
                'id' => $pa['id'],
                'assignment_id' => 'prog_' . $pa['id'], // Composite ID
                'tenant_id' => $tenantId,
                'specialty_id' => 'prog_' . $pa['program_id'],
                'user_id' => $pa['user_id'],
                'status' => $pa['status'] === 'not_started' ? 'pending' : $pa['status'],
                'created_at' => $pa['created_at'],
                'user_name' => $pa['user_name'],
                'user_email' => $pa['user_email'],
                'assigned_by_name' => 'Sistema/Admin', // Programs track assigned_by? Not in query currently
                'specialty' => [
                    'id' => 'prog_' . $pa['program_id'],
                    'name' => $pa['program_name'],
                    'badge_icon' => $pa['program_icon'],
                'type' => $pa['program_type'],
                ],
                'type_label' => 'program',
                'xp_earned' => 0, // TODO: fetch if completed
                // Proxy for read_at: if updated_at is newer than created_at, it has been "touched" (viewed)
                'read_at' => ($pa['updated_at'] && $pa['updated_at'] > $pa['created_at']) ? $pa['updated_at'] : null
            ];
        }

        // Sort combined list by date desc
        usort($assignments, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $assignments;
    }

    /**
     * Get comprehensive tracking data for "God Mode"
     * Returns assignments with progress, notification status, and lifecycle events.
     */
    public static function getGodModeData(int $tenantId): array
    {
        $assignments = self::getTenantAssignments($tenantId);
        
        foreach ($assignments as &$a) {
            $specialtyId = $a['specialty_id'];
            $userId = $a['user_id'];
            
            // 1. Fetch Notification Status (Received/Read)
            $notif = db_fetch_one(
                "SELECT read_at, created_at FROM notifications 
                 WHERE tenant_id = ? AND user_id = ? AND type = 'specialty_assigned' 
                 AND (data LIKE ? OR data LIKE ?)
                 ORDER BY created_at DESC LIMIT 1",
                [$tenantId, $userId, '%"specialty_id":"' . $specialtyId . '"%', '%"program_id":' . str_replace('prog_', '', $specialtyId) . '%']
            );
            
            $a['is_received'] = $notif ? true : false;
            $a['read_at'] = $notif['read_at'] ?? null;
            $a['notified_at'] = $notif['created_at'] ?? null;
            
            // 2. Fetch Detailed Requirement Progress
            if ($a['type_label'] === 'specialty') {
                $progress = self::calculateProgress($a['id'], $specialtyId);
                $a['total_requirements'] = $progress['total'];
                $a['completed_requirements'] = $progress['completed'];
                $a['progress_percentage'] = $progress['percentage'];
                
                // Fetch recent requirement activity
                $a['recent_activity'] = db_fetch_all(
                    "SELECT urp.*, sr.title as requirement_title 
                     FROM user_requirement_progress urp
                     JOIN specialty_requirements sr ON urp.requirement_id = sr.id
                     WHERE urp.assignment_id = ? AND urp.status != 'pending'
                     ORDER BY urp.updated_at DESC LIMIT 3",
                    [$a['id']]
                );
            } else {
                // Program progress (Learning Programs)
                $progId = str_replace('prog_', '', $specialtyId);
                $stats = db_fetch_one(
                    "SELECT 
                        (SELECT COUNT(*) FROM program_questions pq 
                         JOIN program_steps ps ON pq.step_id = ps.id 
                         JOIN program_versions pv ON ps.version_id = pv.id
                         WHERE pv.program_id = ? AND pv.status = 'published') as total_q,
                        (SELECT COUNT(*) FROM user_requirement_progress 
                         WHERE assignment_id = ? AND status = 'approved' AND requirement_id LIKE 'pq_%') as completed_q",
                    [$progId, $a['id']]
                );
                
                $total = (int)($stats['total_q'] ?? 0);
                $completed = (int)($stats['completed_q'] ?? 0);
                
                $a['total_requirements'] = $total;
                $a['completed_requirements'] = $completed;
                $a['progress_percentage'] = $total > 0 ? round(($completed / $total) * 100) : 0;
                $a['recent_activity'] = []; // TODO: Implement for programs if needed
            }
        }
        
        return $assignments;
    }

    /**
     * Delete an assignment (Specialty or Program)
     * Only works if the mission has NOT been started.
     */
    public static function deleteAssignment(string $compositeId, int $tenantId): bool
    {
        if (str_starts_with($compositeId, 'spec_')) {
            $id = (int) substr($compositeId, 5);
            // Check status first
            $assignment = db_fetch_one("SELECT status FROM specialty_assignments WHERE id = ? AND tenant_id = ?", [$id, $tenantId]);
            if (!$assignment || $assignment['status'] !== 'pending') {
                return false;
            }
            return db_delete('specialty_assignments', 'id = ? AND tenant_id = ?', [$id, $tenantId]);
        } 
        
        if (str_starts_with($compositeId, 'prog_')) {
            $id = (int) substr($compositeId, 5);
            // Check status first
            $progress = db_fetch_one("SELECT status FROM user_program_progress WHERE id = ? AND tenant_id = ?", [$id, $tenantId]);
            if (!$progress || $progress['status'] !== 'not_started') {
                return false;
            }
            return db_delete('user_program_progress', 'id = ? AND tenant_id = ?', [$id, $tenantId]);
        }

        return false;
    }

    /**
     * Initialize requirements for an assignment
     */
    public static function initializeRequirements(int $assignmentId, int $tenantId, string $specialtyId): void
    {
        $specialty = self::getSpecialty($specialtyId);
        if (!$specialty || empty($specialty['requirements'])) {
            return;
        }

        foreach ($specialty['requirements'] as $req) {
            db_insert('user_requirement_progress', [
                'assignment_id' => $assignmentId,
                'tenant_id' => $tenantId,
                'requirement_id' => $req['id'],
                'status' => 'pending'
            ]);
        }
    }

    /**
     * Get requirements progress for an assignment
     */
    public static function getRequirementsProgress(int $assignmentId): array
    {
        $dbRequirements = db_fetch_all(
            "SELECT * FROM user_requirement_progress WHERE assignment_id = ? ORDER BY requirement_id",
            [$assignmentId]
        );

        // Index by requirement_id
        $progress = [];
        foreach ($dbRequirements as $req) {
            $progress[$req['requirement_id']] = $req;
        }

        return $progress;
    }

    /**
     * Calculate progress percentage for an assignment
     */
    public static function calculateProgress(int $assignmentId, string $specialtyId): array
    {
        $specialty = self::getSpecialty($specialtyId);
        if (!$specialty || empty($specialty['requirements'])) {
            return ['total' => 0, 'completed' => 0, 'percentage' => 0, 'answered' => 0, 'answered_percentage' => 0];
        }

        $total = count($specialty['requirements']);
        $progress = self::getRequirementsProgress($assignmentId);

        $completed = 0;
        $answered = 0;
        foreach ($progress as $req) {
            if ($req['status'] === 'approved') {
                $completed++;
            }
            if ($req['status'] !== 'pending') {
                $answered++;
            }
        }

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'answered' => $answered,
            'answered_percentage' => $total > 0 ? round(($answered / $total) * 100) : 0
        ];
    }

    /**
     * Submit proof for a requirement
     */
    public static function submitRequirementProof(
        int $assignmentId,
        string $requirementId,
        string $proofType,
        string $content
    ): bool {
        return db_query(
            "UPDATE assignment_requirements 
             SET status = 'submitted', proof_type = ?, proof_content = ?, submitted_at = NOW() 
             WHERE assignment_id = ? AND requirement_id = ?",
            [$proofType, $content, $assignmentId, $requirementId]
        ) !== false;
    }

    /**
     * Approve a requirement
     */
    public static function approveRequirement(int $requirementRowId, int $reviewerId, ?string $feedback = null): bool
    {
        return db_query(
            "UPDATE assignment_requirements 
             SET status = 'approved', reviewed_by = ?, reviewed_at = NOW(), feedback = ? 
             WHERE id = ?",
            [$reviewerId, $feedback, $requirementRowId]
        ) !== false;
    }

    /**
     * Reject a requirement
     */
    public static function rejectRequirement(int $requirementRowId, int $reviewerId, string $feedback): bool
    {
        return db_query(
            "UPDATE assignment_requirements 
             SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), feedback = ? 
             WHERE id = ?",
            [$reviewerId, $feedback, $requirementRowId]
        ) !== false;
    }

    /**
     * Check if all requirements are approved
     */
    public static function areAllRequirementsApproved(int $assignmentId, string $specialtyId): bool
    {
        $progress = self::calculateProgress($assignmentId, $specialtyId);
        return $progress['total'] > 0 && $progress['completed'] === $progress['total'];
    }

    /**
     * Get requirements with progress for e-learning view
     * Uses database requirements first, falls back to JSON
     */
    public static function getRequirementsWithProgress(int $assignmentId, string $specialtyId): array
    {
        // First try to get requirements from database
        $dbRequirements = self::getRequirementsFromDB($specialtyId);

        if (!empty($dbRequirements)) {
            // Use database requirements
            $requirements = [];
            foreach ($dbRequirements as $req) {
                // Parse questions from options field if it contains JSON array
                $options = null;
                $questions = null;
                if (!empty($req['options'])) {
                    $decoded = json_decode($req['options'], true);
                    if (is_array($decoded)) {
                        // Check if it's a questions array (for e-learning)
                        if (isset($decoded[0]['type']) || isset($decoded[0]['question'])) {
                            $questions = $decoded;
                        } else {
                            // It's simple options for multiple choice
                            $options = $decoded;
                        }
                    }
                }

                $requirements[] = [
                    'id' => $req['id'],
                    'order_num' => $req['order_num'],
                    'title' => $req['title'],
                    'description' => $req['description'] ?? '',
                    'type' => $req['type'],
                    'options' => $options,
                    'questions' => $questions,
                    'points' => $req['points'] ?? 10,
                    'is_required' => $req['is_required'] ?? 1
                ];
            }
        } else {
            // Fallback to JSON file
            $specialty = self::getSpecialty($specialtyId);
            if (!$specialty || empty($specialty['requirements'])) {
                return [];
            }

            $requirements = [];
            foreach ($specialty['requirements'] as $idx => $req) {
                $requirements[] = [
                    'id' => $req['id'] ?? ($idx + 1),
                    'order_num' => $idx + 1,
                    'title' => $req['text'] ?? $req['title'] ?? '',
                    'description' => $req['description'] ?? '',
                    'type' => $req['type'] ?? 'text',
                    'options' => $req['options'] ?? null,
                    'questions' => null,
                    'points' => $req['points'] ?? 10,
                    'is_required' => 1
                ];
            }
        }

        // Get progress from DB
        $dbProgress = db_fetch_all(
            "SELECT * FROM user_requirement_progress WHERE assignment_id = ?",
            [$assignmentId]
        );

        // Index by requirement_id  
        $progressMap = [];
        foreach ($dbProgress as $p) {
            $progressMap[$p['requirement_id']] = $p;
        }

        // Merge with progress
        $result = [];
        foreach ($requirements as $req) {
            $reqId = $req['id'];
            $progress = $progressMap[$reqId] ?? null;

            $result[] = [
                'id' => $reqId,
                'order_num' => $req['order_num'],
                'title' => $req['title'],
                'description' => $req['description'],
                'type' => $req['type'],
                'options' => $req['options'],
                'questions' => $req['questions'] ?? null,
                'points' => $req['points'],
                'is_required' => $req['is_required'] ?? 1,
                'status' => $progress['status'] ?? 'pending',
                'answer' => $progress['answer'] ?? null,
                'file_path' => $progress['file_path'] ?? null,
                'answered_at' => $progress['answered_at'] ?? null,
                'feedback' => $progress['feedback'] ?? null,
                'progress_id' => $progress['id'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Save requirement answer for e-learning
     */
    public static function saveRequirementAnswer(
        int $assignmentId,
        int $requirementId,
        string $answer,
        ?string $filePath = null
    ): bool {
        // Check if progress record exists
        $existing = db_fetch_one(
            "SELECT id FROM user_requirement_progress WHERE assignment_id = ? AND requirement_id = ?",
            [$assignmentId, $requirementId]
        );

        if ($existing) {
            // Update existing
            return db_query(
                "UPDATE user_requirement_progress 
                 SET answer = ?, file_path = ?, status = 'answered', answered_at = NOW(), updated_at = NOW()
                 WHERE id = ?",
                [$answer, $filePath, $existing['id']]
            ) !== false;
        } else {
            // Insert new
            return db_insert('user_requirement_progress', [
                'assignment_id' => $assignmentId,
                'requirement_id' => $requirementId,
                'answer' => $answer,
                'file_path' => $filePath,
                'status' => 'answered',
                'answered_at' => date('Y-m-d H:i:s'),
            ]) > 0;
        }
    }

    /**
     * Check if all requirements are answered
     */
    public static function checkAllRequirementsAnswered(int $assignmentId): bool
    {
        $assignment = db_fetch_one(
            "SELECT specialty_id FROM specialty_assignments WHERE id = ?",
            [$assignmentId]
        );

        if (!$assignment) {
            return false;
        }

        $specialty = self::getSpecialty($assignment['specialty_id']);
        if (!$specialty || empty($specialty['requirements'])) {
            return true;
        }

        $totalReqs = count($specialty['requirements']);

        $answeredCount = db_fetch_one(
            "SELECT COUNT(*) as cnt FROM user_requirement_progress 
             WHERE assignment_id = ? AND status != 'pending'",
            [$assignmentId]
        );

        return ($answeredCount['cnt'] ?? 0) >= $totalReqs;
    }

    /**
     * Get next unanswered requirement
     */
    public static function getNextUnansweredRequirement(int $assignmentId, string $specialtyId): ?array
    {
        $requirements = self::getRequirementsWithProgress($assignmentId, $specialtyId);

        foreach ($requirements as $req) {
            if ($req['status'] === 'pending') {
                return $req;
            }
        }

        return null;
    }

    /**
     * Check if specialty is outdoor (practical) type
     * Outdoor specialties don't use quiz interface - they use proof submission
     */
    public static function isOutdoorSpecialty(string $specialtyId): bool
    {
        // Check database for specialty type
        $req = db_fetch_one(
            "SELECT type FROM specialty_requirements WHERE specialty_id = ? AND type = 'practical' LIMIT 1",
            [$specialtyId]
        );

        if ($req) {
            return true;
        }

        // Check JSON for type field
        $specialty = self::getSpecialty($specialtyId);
        if ($specialty && isset($specialty['type'])) {
            return $specialty['type'] === 'outdoor';
        }

        // Default to categories that are typically outdoor
        $outdoorCategories = ['cat_outdoor', 'cat_camping', 'cat_nature'];
        if ($specialty && in_array($specialty['category_id'], $outdoorCategories)) {
            return true;
        }

        return false;
    }

    /**
     * Get specialty type: 'indoor', 'outdoor', or 'mixed'
     */
    public static function getSpecialtyType(string $specialtyId): string
    {
        $requirements = self::getRequirementsFromDB($specialtyId);

        if (empty($requirements)) {
            return 'indoor'; // Default
        }

        $practicalCount = 0;
        $textCount = 0;

        foreach ($requirements as $req) {
            if ($req['type'] === 'practical' || $req['type'] === 'file_upload') {
                $practicalCount++;
            } else {
                $textCount++;
            }
        }

        if ($practicalCount === 0) {
            return 'indoor';
        } elseif ($textCount === 0) {
            return 'outdoor';
        } else {
            return 'mixed';
        }
    }

    /**
     * Get requirements from database instead of JSON
     */
    public static function getRequirementsFromDB(string $specialtyId): array
    {
        // Handle Learning Programs (prog_ prefix)
        if (str_starts_with($specialtyId, 'prog_')) {
            $programId = substr($specialtyId, 5);
            
            // Get published version
            $version = db_fetch_one(
                "SELECT id FROM program_versions WHERE program_id = ? AND status = 'published' ORDER BY version_number DESC LIMIT 1",
                [$programId]
            );

            // If no published version, check for draft (for admin preview)
            if (!$version) {
                $version = db_fetch_one(
                    "SELECT id FROM program_versions WHERE program_id = ? ORDER BY version_number DESC LIMIT 1",
                    [$programId]
                );
            }

            if (!$version) return [];

            // Get steps
            $steps = db_fetch_all("SELECT * FROM program_steps WHERE version_id = ? ORDER BY sort_order", [$version['id']]);
            
            $requirements = [];
            $orderNum = 1;

            foreach ($steps as $step) {
                // Get questions for this step
                $questions = db_fetch_all("SELECT * FROM program_questions WHERE step_id = ? ORDER BY sort_order", [$step['id']]);
                
                foreach ($questions as $q) {
                    $requirements[] = [
                        'id' => 'pq_' . $q['id'], // Virtual ID
                        'specialty_id' => $specialtyId,
                        'order_num' => $orderNum++,
                        'title' => $q['question_text'], // Use question text as title
                        'description' => $step['title'] . ($step['description'] ? ': ' . $step['description'] : ''), // Context
                        'type' => $q['type'],
                        'options' => $q['options'], // JSON string
                        'points' => $q['points'],
                        'is_required' => $q['is_required']
                    ];
                }
            }
            
            return $requirements;
        }

        return db_fetch_all(
            "SELECT * FROM specialty_requirements 
             WHERE specialty_id = ? 
             ORDER BY order_num ASC",
            [$specialtyId]
        );
    }

    /**
     * Check if specialty has quiz-type questions (multiple choice, etc.)
     */
    public static function hasQuizQuestions(string $specialtyId): bool
    {
        $result = db_fetch_one(
            "SELECT COUNT(*) as cnt FROM specialty_requirements 
             WHERE specialty_id = ? AND type IN ('multiple_choice', 'checkbox')",
            [$specialtyId]
        );

        return ($result['cnt'] ?? 0) > 0;
    }

    /**
     * Get specialty XP reward (from JSON or calculate from requirements)
     */
    public static function getXpReward(string $specialtyId): int
    {
        $specialty = self::getSpecialty($specialtyId);

        if ($specialty && isset($specialty['xp_reward'])) {
            return (int) $specialty['xp_reward'];
        }

        // Calculate from requirements points
        $result = db_fetch_one(
            "SELECT COALESCE(SUM(points), 100) as total FROM specialty_requirements WHERE specialty_id = ?",
            [$specialtyId]
        );

        return (int) ($result['total'] ?? 100);
    }
}
