<?php
/**
 * Roadside Assistance Admin Platform - Permission Model
 * Handles role-based permissions and access control
 */

class Permission extends Model {
    protected $table = 'permissions';

    // Get all permissions
    public function getAllPermissions() {
        return $this->db->getRows(
            "SELECT * FROM {$this->table} ORDER BY category, name"
        );
    }

    // Get permission by key
    public function getByKey($key) {
        return $this->db->getRow(
            "SELECT * FROM {$this->table} WHERE permission_key = ?",
            [$key]
        );
    }

    // Get permissions for a role
    public function getForRole($role) {
        $query = "SELECT p.* FROM {$this->table} p
                  INNER JOIN role_permissions rp ON p.id = rp.permission_id
                  WHERE rp.role = ?
                  ORDER BY p.category, p.name";
        
        return $this->db->getRows($query, [$role]);
    }

    // Get permission keys for a role
    public function getKeysForRole($role) {
        $permissions = $this->getForRole($role);
        return array_column($permissions, 'permission_key');
    }

    // Check if role has permission
    public function roleHasPermission($role, $permissionKey) {
        $query = "SELECT COUNT(*) FROM {$this->table} p
                  INNER JOIN role_permissions rp ON p.id = rp.permission_id
                  WHERE rp.role = ? AND p.permission_key = ?";
        
        return (bool)$this->db->getValue($query, [$role, $permissionKey]);
    }

    // Assign permission to role
    public function assignToRole($permissionId, $role) {
        try {
            return $this->db->insert(
                "INSERT INTO role_permissions (role, permission_id, created_at) 
                 VALUES (?, ?, NOW())",
                [$role, $permissionId]
            );
        } catch (Exception $e) {
            // Ignore duplicate key errors
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                throw $e;
            }
            return false;
        }
    }

    // Remove permission from role
    public function removeFromRole($permissionId, $role) {
        return $this->db->delete(
            "DELETE FROM role_permissions WHERE role = ? AND permission_id = ?",
            [$role, $permissionId]
        );
    }

    // Sync permissions for a role (replace all)
    public function syncRolePermissions($role, $permissionIds) {
        $this->db->beginTransaction();

        try {
            // Delete existing permissions
            $this->db->delete(
                "DELETE FROM role_permissions WHERE role = ?",
                [$role]
            );

            // Insert new permissions
            foreach ($permissionIds as $permissionId) {
                $this->assignToRole($permissionId, $role);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Get all roles with their permissions
    public function getAllRolesWithPermissions() {
        $roles = ['admin', 'manager', 'dispatcher', 'driver'];
        $result = [];

        foreach ($roles as $role) {
            $result[$role] = [
                'name' => ucfirst($role),
                'permissions' => $this->getKeysForRole($role)
            ];
        }

        return $result;
    }

    // Get grouped permissions by category
    public function getGroupedByCategory() {
        $permissions = $this->getAllPermissions();
        $grouped = [];

        foreach ($permissions as $permission) {
            $category = $permission['category'] ?: 'Other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $permission;
        }

        return $grouped;
    }

    // Create new permission
    public function createPermission($key, $name, $description, $category = null) {
        return $this->db->insert(
            "INSERT INTO {$this->table} (permission_key, name, description, category, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())",
            [$key, $name, $description, $category]
        );
    }

    // Update permission
    public function updatePermission($id, $name, $description, $category = null) {
        return $this->db->update(
            "UPDATE {$this->table} 
             SET name = ?, description = ?, category = ?, updated_at = NOW()
             WHERE id = ?",
            [$name, $description, $category, $id]
        );
    }

    // Delete permission
    public function deletePermission($id) {
        $this->db->beginTransaction();

        try {
            // Delete role associations
            $this->db->delete(
                "DELETE FROM role_permissions WHERE permission_id = ?",
                [$id]
            );

            // Delete permission
            $this->db->delete(
                "DELETE FROM {$this->table} WHERE id = ?",
                [$id]
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
?>
