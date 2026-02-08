<?php

namespace App\Services;

class RoleService
{
    /**
     * Official roles configuration
     * Ordered by hierarchy
     */
    public const OFFICIAL_ROLES = [
        'admin' => [
            'display_name' => 'Administrador',
            'description' => 'Acesso total ao sistema',
            'order' => 1
        ],
        'director' => [
            'display_name' => 'Diretor',
            'description' => 'Gestão total do clube',
            'order' => 2
        ],
        'associate_director' => [
            'display_name' => 'Diretor Associado',
            'description' => 'Auxilia na gestão do clube',
            'order' => 3
        ],
        'chaplain' => [
            'display_name' => 'Capelão',
            'description' => 'Cuidado espiritual do clube',
            'order' => 4
        ],
        'instructor' => [
            'display_name' => 'Instrutor',
            'description' => 'Ministra classes e especialidades',
            'order' => 5
        ],
        'counselor' => [
            'display_name' => 'Conselheiro',
            'description' => 'Lidera unidade e acompanha desbravadores',
            'order' => 6
        ],
        'leader' => [
            'display_name' => 'Líder',
            'description' => 'Membros em formação de liderança',
            'order' => 7
        ],
        'pathfinder' => [
            'display_name' => 'Desbravador',
            'description' => 'Membro do clube em progressão',
            'order' => 8
        ]
    ];

    /**
     * Get official roles list
     */
    public static function getOfficialRoles(): array
    {
        return self::OFFICIAL_ROLES;
    }

    /**
     * Get roles for a specific tenant, ensuring they are official
     */
    public static function getTenantRoles(int $tenantId): array
    {
        $roles = db_fetch_all(
            "SELECT * FROM roles WHERE tenant_id = ? ORDER BY id ASC",
            [$tenantId]
        );

        $official = [];
        $officialConfig = self::OFFICIAL_ROLES;

        // Group by name for easy lookup
        $dbRoles = [];
        foreach ($roles as $r) {
            $dbRoles[$r['name']] = $r;
        }

        // Return precisely the official ones in order
        $ordered = [];
        foreach ($officialConfig as $name => $config) {
            if (isset($dbRoles[$name])) {
                $ordered[] = $dbRoles[$name];
            }
        }

        return $ordered;
    }

    /**
     * Sync official roles for a tenant
     */
    public static function syncTenant(int $tenantId): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'roles' => []];
        
        foreach (self::OFFICIAL_ROLES as $name => $config) {
            $existing = db_fetch_one(
                "SELECT id, display_name FROM roles WHERE tenant_id = ? AND name = ?",
                [$tenantId, $name]
            );

            if ($existing) {
                // Potential update of display name if needed or other fields
                if ($existing['display_name'] !== $config['display_name']) {
                    db_update('roles', [
                        'display_name' => $config['display_name'],
                        'is_system' => 1
                    ], 'id = ?', [$existing['id']]);
                    $stats['updated']++;
                }
                $stats['roles'][$name] = $existing['id'];
            } else {
                $id = db_insert('roles', [
                    'tenant_id' => $tenantId,
                    'name' => $name,
                    'display_name' => $config['display_name'],
                    'description' => $config['description'],
                    'is_system' => 1
                ]);
                $stats['created']++;
                $stats['roles'][$name] = $id;
            }
        }

        return $stats;
    }
}
