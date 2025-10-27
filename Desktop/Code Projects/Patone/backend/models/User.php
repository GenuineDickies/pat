<?php
/**
 * Roadside Assistance Admin Platform - User Model
 * Handles user authentication and management
 */

class User extends Model {
    protected $table = 'users';

    // Get user by username
    public function getByUsername($username) {
        return $this->db->getRow(
            "SELECT * FROM {$this->table} WHERE username = ?",
            [$username]
        );
    }

    // Get user by email
    public function getByEmail($email) {
        return $this->db->getRow(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    // Authenticate user
    public function authenticate($username, $password) {
        $user = $this->getByUsername($username);

        if (!$user) {
            return false;
        }

        if ($user['status'] !== 'active') {
            throw new Exception("User account is not active");
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Update last login
        $this->updateLastLogin($user['id']);

        // Remove password from returned data
        unset($user['password']);

        return $user;
    }

    // Create new user
    public function create($data) {
        $requiredFields = ['username', 'email', 'password', 'first_name', 'last_name', 'role'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field missing: $field");
            }
        }

        // Check if username already exists
        if ($this->getByUsername($data['username'])) {
            throw new Exception("Username already exists");
        }

        // Check if email already exists
        if ($this->getByEmail($data['email'])) {
            throw new Exception("Email already exists");
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $data['status'] = $data['status'] ?? 'active';

        return $this->db->insert(
            "INSERT INTO {$this->table}
             (username, email, password, first_name, last_name, role, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['username'], $data['email'], $hashedPassword,
                $data['first_name'], $data['last_name'], $data['role'], $data['status']
            ]
        );
    }

    // Update user
    public function update($id, $data) {
        $fields = [];
        $values = [];

        $allowedFields = ['username', 'email', 'first_name', 'last_name', 'role', 'status'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                // Check uniqueness for username and email
                if ($field === 'username' && !empty($data[$field])) {
                    $existing = $this->getByUsername($data[$field]);
                    if ($existing && $existing['id'] != $id) {
                        throw new Exception("Username already exists");
                    }
                }
                if ($field === 'email' && !empty($data[$field])) {
                    $existing = $this->getByEmail($data[$field]);
                    if ($existing && $existing['id'] != $id) {
                        throw new Exception("Email already exists");
                    }
                }

                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->update($sql, $values);
    }

    // Update password
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        return $this->db->update(
            "UPDATE {$this->table} SET password = ?, updated_at = NOW() WHERE id = ?",
            [$hashedPassword, $id]
        );
    }

    // Update last login
    public function updateLastLogin($id) {
        return $this->db->update(
            "UPDATE {$this->table} SET last_login = NOW(), updated_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    // Change user status
    public function changeStatus($id, $status) {
        $validStatuses = ['active', 'inactive', 'suspended'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status: $status");
        }

        return $this->db->update(
            "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $id]
        );
    }

    // Get all users with filtering
    public function getAll($limit = null, $offset = 0, $filters = []) {
        $whereConditions = [];
        $params = [];

        if (!empty($filters['role'])) {
            $whereConditions[] = "role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // Get total count
        $totalQuery = "SELECT COUNT(*) FROM {$this->table} $whereClause";
        $total = (int)$this->db->getValue($totalQuery, $params);

        // Get users
        $orderBy = "ORDER BY last_name, first_name";
        $limitClause = $limit ? "LIMIT ? OFFSET ?" : "";
        if ($limit) {
            $params[] = $limit;
            $params[] = $offset;
        }

        $query = "SELECT id, username, email, first_name, last_name, role, status, last_login, created_at
                  FROM {$this->table}
                  $whereClause $orderBy $limitClause";

        $users = $this->db->getRows($query, $params);

        return [
            'users' => $users,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    // Get user statistics
    public function getStats() {
        $stats = [];

        $stats['total'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table}");
        $stats['active'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'");
        $stats['by_role'] = [];

        $roles = $this->db->getRows("SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role");
        foreach ($roles as $role) {
            $stats['by_role'][$role['role']] = (int)$role['count'];
        }

        return $stats;
    }

    // Record login attempt
    public function recordLoginAttempt($username, $ipAddress, $success) {
        $this->db->insert(
            "INSERT INTO login_attempts (username, ip_address, success, created_at) VALUES (?, ?, ?, NOW())",
            [$username, $ipAddress, $success ? 1 : 0]
        );
    }

    // Check if account is locked due to failed attempts
    public function isAccountLocked($username, $ipAddress) {
        $attempts = $this->db->getValue(
            "SELECT COUNT(*) FROM login_attempts 
             WHERE username = ? AND ip_address = ? AND success = 0 
             AND created_at > DATE_SUB(NOW(), INTERVAL " . LOCKOUT_TIME . " SECOND)",
            [$username, $ipAddress]
        );

        return $attempts >= LOGIN_ATTEMPTS;
    }
}
?>
