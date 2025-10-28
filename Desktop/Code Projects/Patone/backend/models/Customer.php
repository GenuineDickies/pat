<?php
/**
 * Roadside Assistance Admin Platform - Customer Model
 * Handles customer data operations
 */

class Customer extends Model {
    protected $table = 'customers';

    // Get customer by ID
    public function getById($id) {
        return $this->db->getRow(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    // Get customer by email
    public function getByEmail($email) {
        return $this->db->getRow(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    // Get all customers with pagination
    public function getAll($limit = null, $offset = 0, $search = '', $filters = []) {
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['state'])) {
            $whereConditions[] = "state = ?";
            $params[] = $filters['state'];
        }

        if (!empty($filters['is_vip'])) {
            $whereConditions[] = "is_vip = ?";
            $params[] = $filters['is_vip'] ? 1 : 0;
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // Get total count
        $totalQuery = "SELECT COUNT(*) FROM {$this->table} $whereClause";
        $total = (int)$this->db->getValue($totalQuery, $params);

        // Get customers
        $orderBy = "ORDER BY created_at DESC";
        $limitClause = $limit ? "LIMIT ? OFFSET ?" : "";
        if ($limit) {
            $params[] = $limit;
            $params[] = $offset;
        }

        $query = "SELECT *,
                         (SELECT COUNT(*) FROM service_requests WHERE customer_id = {$this->table}.id) as total_requests,
                         (SELECT MAX(created_at) FROM service_requests WHERE customer_id = {$this->table}.id AND status = 'completed') as last_service_date
                  FROM {$this->table}
                  $whereClause $orderBy $limitClause";

        $customers = $this->db->getRows($query, $params);

        return [
            'customers' => $customers,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    // Create new customer
    public function create($data) {
        $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field missing: $field");
            }
        }

        // Clean phone numbers
        $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);
        $data['emergency_contact'] = preg_replace('/[^0-9]/', '', $data['emergency_contact'] ?? '');

        // Set defaults
        $data['status'] = $data['status'] ?? 'active';
        $data['is_vip'] = isset($data['is_vip']) ? 1 : 0;

        return $this->db->insert(
            "INSERT INTO {$this->table}
             (first_name, last_name, email, phone, emergency_contact, date_of_birth,
              address, address2, city, state, zip, is_vip, status, notes, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['first_name'], $data['last_name'], $data['email'],
                $data['phone'], $data['emergency_contact'], $data['date_of_birth'],
                $data['address'], $data['address2'], $data['city'],
                $data['state'], $data['zip'], $data['is_vip'],
                $data['status'], $data['notes']
            ]
        );
    }

    // Update customer
    public function update($id, $data) {
        $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field missing: $field");
            }
        }

        // Clean phone numbers
        $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);
        $data['emergency_contact'] = preg_replace('/[^0-9]/', '', $data['emergency_contact'] ?? '');

        // Set defaults
        $data['is_vip'] = isset($data['is_vip']) ? 1 : 0;

        return $this->db->update(
            "UPDATE {$this->table} SET
             first_name = ?, last_name = ?, email = ?, phone = ?, emergency_contact = ?,
             date_of_birth = ?, address = ?, address2 = ?, city = ?, state = ?, zip = ?,
             is_vip = ?, status = ?, notes = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['first_name'], $data['last_name'], $data['email'],
                $data['phone'], $data['emergency_contact'], $data['date_of_birth'],
                $data['address'], $data['address2'], $data['city'],
                $data['state'], $data['zip'], $data['is_vip'],
                $data['status'], $data['notes'], $id
            ]
        );
    }

    // Delete customer
    public function delete($id) {
        // Check for active requests
        $activeRequests = $this->db->getValue(
            "SELECT COUNT(*) FROM service_requests WHERE customer_id = ? AND status IN ('pending', 'assigned', 'in_progress')",
            [$id]
        );

        if ($activeRequests > 0) {
            throw new Exception("Cannot delete customer with active service requests");
        }

        return $this->db->delete("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    // Get customer vehicles
    public function getVehicles($customerId) {
        return $this->db->getRows(
            "SELECT * FROM customer_vehicles WHERE customer_id = ? ORDER BY id",
            [$customerId]
        );
    }

    // Get customer service history
    public function getServiceHistory($customerId, $limit = 20) {
        return $this->db->getRows(
            "SELECT sr.*, st.name as service_type_name, d.first_name as driver_first_name, d.last_name as driver_last_name
             FROM service_requests sr
             LEFT JOIN service_types st ON sr.service_type_id = st.id
             LEFT JOIN drivers d ON sr.driver_id = d.id
             WHERE sr.customer_id = ?
             ORDER BY sr.created_at DESC
             LIMIT ?",
            [$customerId, $limit]
        );
    }

    // Search customers
    public function search($query, $limit = 10) {
        return $this->db->getRows(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name, email, phone, city, state
             FROM {$this->table}
             WHERE status = 'active' AND
             (CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR phone LIKE ?)
             ORDER BY last_name, first_name
             LIMIT ?",
            ["%$query%", "%$query%", "%$query%", $limit]
        );
    }

    // Get customer statistics
    public function getStats() {
        $stats = [];

        $stats['total'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table}");
        $stats['active'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'");
        $stats['vip'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} WHERE is_vip = 1");
        $stats['new_this_month'] = (int)$this->db->getValue(
            "SELECT COUNT(*) FROM {$this->table} WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
        );

        return $stats;
    }

    // Get customer tags
    public function getTags($customerId) {
        return $this->db->getRows(
            "SELECT t.* FROM customer_tags t
             INNER JOIN customer_tag_assignments cta ON t.id = cta.tag_id
             WHERE cta.customer_id = ?
             ORDER BY t.name",
            [$customerId]
        );
    }

    // Add tag to customer
    public function addTag($customerId, $tagId) {
        // Check if tag assignment already exists
        $exists = $this->db->getValue(
            "SELECT COUNT(*) FROM customer_tag_assignments WHERE customer_id = ? AND tag_id = ?",
            [$customerId, $tagId]
        );

        if (!$exists) {
            $this->db->insert(
                "INSERT INTO customer_tag_assignments (customer_id, tag_id, created_at) VALUES (?, ?, NOW())",
                [$customerId, $tagId]
            );
        }
    }

    // Remove tag from customer
    public function removeTag($customerId, $tagId) {
        $this->db->delete(
            "DELETE FROM customer_tag_assignments WHERE customer_id = ? AND tag_id = ?",
            [$customerId, $tagId]
        );
    }

    // Get customers by tag
    public function getByTag($tagId, $limit = null, $offset = 0) {
        $limitClause = $limit ? "LIMIT ? OFFSET ?" : "";
        $params = [$tagId];
        
        if ($limit) {
            $params[] = $limit;
            $params[] = $offset;
        }

        return $this->db->getRows(
            "SELECT c.* FROM {$this->table} c
             INNER JOIN customer_tag_assignments cta ON c.id = cta.customer_id
             WHERE cta.tag_id = ?
             ORDER BY c.last_name, c.first_name
             $limitClause",
            $params
        );
    }

    // Get activity log for customer
    public function getActivityLog($customerId, $limit = 50) {
        return $this->db->getRows(
            "SELECT * FROM activity_log
             WHERE entity_type = 'customer' AND entity_id = ?
             ORDER BY created_at DESC
             LIMIT ?",
            [$customerId, $limit]
        );
    }
}
?>
