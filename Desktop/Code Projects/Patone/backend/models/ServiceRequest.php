<?php
/**
 * Roadside Assistance Admin Platform - Service Request Model
 * Handles service request operations and tracking
 */

class ServiceRequest extends Model {
    protected $table = 'service_requests';

    // Get service request by ID with related data
    public function getById($id) {
        return $this->db->getRow(
            "SELECT sr.*, 
                    c.first_name as customer_first_name, c.last_name as customer_last_name, 
                    c.email as customer_email, c.phone as customer_phone,
                    d.first_name as driver_first_name, d.last_name as driver_last_name,
                    d.phone as driver_phone, d.status as driver_status,
                    st.name as service_type_name, st.base_price as service_base_price,
                    cv.make as vehicle_make, cv.model as vehicle_model, cv.year as vehicle_year
             FROM {$this->table} sr
             LEFT JOIN customers c ON sr.customer_id = c.id
             LEFT JOIN drivers d ON sr.driver_id = d.id
             LEFT JOIN service_types st ON sr.service_type_id = st.id
             LEFT JOIN customer_vehicles cv ON sr.vehicle_id = cv.id
             WHERE sr.id = ?",
            [$id]
        );
    }

    // Get all service requests with filtering
    public function getAll($limit = null, $offset = 0, $search = '', $filters = []) {
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR sr.location_address LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "sr.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $whereConditions[] = "sr.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['driver_id'])) {
            $whereConditions[] = "sr.driver_id = ?";
            $params[] = $filters['driver_id'];
        }

        if (!empty($filters['customer_id'])) {
            $whereConditions[] = "sr.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(sr.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(sr.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // Get total count
        $totalQuery = "SELECT COUNT(*) FROM {$this->table} sr 
                       LEFT JOIN customers c ON sr.customer_id = c.id 
                       $whereClause";
        $total = (int)$this->db->getValue($totalQuery, $params);

        // Get requests
        $orderBy = "ORDER BY sr.created_at DESC";
        $limitClause = $limit ? "LIMIT ? OFFSET ?" : "";
        if ($limit) {
            $params[] = $limit;
            $params[] = $offset;
        }

        $query = "SELECT sr.*, 
                         c.first_name as customer_first_name, c.last_name as customer_last_name,
                         d.first_name as driver_first_name, d.last_name as driver_last_name,
                         st.name as service_type_name
                  FROM {$this->table} sr
                  LEFT JOIN customers c ON sr.customer_id = c.id
                  LEFT JOIN drivers d ON sr.driver_id = d.id
                  LEFT JOIN service_types st ON sr.service_type_id = st.id
                  $whereClause $orderBy $limitClause";

        $requests = $this->db->getRows($query, $params);

        return [
            'requests' => $requests,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    // Create new service request
    public function create($data) {
        $requiredFields = ['customer_id', 'service_type_id', 'location_address', 'location_city', 'location_state'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field missing: $field");
            }
        }

        // Set defaults
        $data['status'] = $data['status'] ?? 'pending';
        $data['priority'] = $data['priority'] ?? 'normal';

        return $this->db->insert(
            "INSERT INTO {$this->table}
             (customer_id, driver_id, service_type_id, vehicle_id, status, priority,
              location_address, location_city, location_state, location_latitude, location_longitude,
              description, estimated_cost, customer_notes, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['customer_id'], $data['driver_id'] ?? null, $data['service_type_id'],
                $data['vehicle_id'] ?? null, $data['status'], $data['priority'],
                $data['location_address'], $data['location_city'], $data['location_state'],
                $data['location_latitude'] ?? null, $data['location_longitude'] ?? null,
                $data['description'] ?? null, $data['estimated_cost'] ?? null,
                $data['customer_notes'] ?? null
            ]
        );
    }

    // Update service request
    public function update($id, $data) {
        $fields = [];
        $values = [];

        $allowedFields = [
            'driver_id', 'service_type_id', 'vehicle_id', 'status', 'priority',
            'location_address', 'location_city', 'location_state', 
            'location_latitude', 'location_longitude', 'description',
            'estimated_cost', 'final_cost', 'customer_notes', 'driver_notes', 'internal_notes'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
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

    // Assign driver to request
    public function assignDriver($requestId, $driverId) {
        return $this->db->update(
            "UPDATE {$this->table} SET 
             driver_id = ?, status = 'assigned', assigned_at = NOW(), updated_at = NOW() 
             WHERE id = ?",
            [$driverId, $requestId]
        );
    }

    // Update status
    public function updateStatus($id, $status, $notes = null) {
        $validStatuses = ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status: $status");
        }

        $timestampFields = [
            'in_progress' => 'started_at',
            'completed' => 'completed_at',
            'cancelled' => 'cancelled_at'
        ];

        $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW()";
        $params = [$status];

        if (isset($timestampFields[$status])) {
            $sql .= ", {$timestampFields[$status]} = NOW()";
        }

        if ($notes !== null) {
            $sql .= ", internal_notes = ?";
            $params[] = $notes;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        return $this->db->update($sql, $params);
    }

    // Cancel request
    public function cancel($id, $reason = null) {
        return $this->db->update(
            "UPDATE {$this->table} SET 
             status = 'cancelled', cancelled_at = NOW(), cancellation_reason = ?, updated_at = NOW() 
             WHERE id = ?",
            [$reason, $id]
        );
    }

    // Complete request
    public function complete($id, $finalCost = null, $driverNotes = null) {
        return $this->db->update(
            "UPDATE {$this->table} SET 
             status = 'completed', completed_at = NOW(), final_cost = ?, driver_notes = ?, updated_at = NOW() 
             WHERE id = ?",
            [$finalCost, $driverNotes, $id]
        );
    }

    // Add customer rating
    public function addRating($id, $rating) {
        if ($rating < 1 || $rating > 5) {
            throw new Exception("Rating must be between 1 and 5");
        }

        return $this->db->update(
            "UPDATE {$this->table} SET rating = ?, updated_at = NOW() WHERE id = ?",
            [$rating, $id]
        );
    }

    // Get pending requests (for dispatch)
    public function getPending($priority = null) {
        $query = "SELECT sr.*, 
                         c.first_name as customer_first_name, c.last_name as customer_last_name,
                         c.phone as customer_phone, c.is_vip,
                         st.name as service_type_name
                  FROM {$this->table} sr
                  LEFT JOIN customers c ON sr.customer_id = c.id
                  LEFT JOIN service_types st ON sr.service_type_id = st.id
                  WHERE sr.status = 'pending'";
        
        $params = [];
        if ($priority) {
            $query .= " AND sr.priority = ?";
            $params[] = $priority;
        }

        $query .= " ORDER BY 
                    FIELD(sr.priority, 'emergency', 'high', 'normal', 'low'),
                    c.is_vip DESC,
                    sr.created_at ASC";

        return $this->db->getRows($query, $params);
    }

    // Get active requests for a driver
    public function getActiveByDriver($driverId) {
        return $this->db->getRows(
            "SELECT sr.*, 
                    c.first_name as customer_first_name, c.last_name as customer_last_name,
                    c.phone as customer_phone,
                    st.name as service_type_name
             FROM {$this->table} sr
             LEFT JOIN customers c ON sr.customer_id = c.id
             LEFT JOIN service_types st ON sr.service_type_id = st.id
             WHERE sr.driver_id = ? AND sr.status IN ('assigned', 'in_progress')
             ORDER BY sr.priority DESC, sr.created_at ASC",
            [$driverId]
        );
    }

    // Get statistics
    public function getStats($dateFrom = null, $dateTo = null) {
        $whereClause = "";
        $params = [];

        if ($dateFrom && $dateTo) {
            $whereClause = "WHERE created_at BETWEEN ? AND ?";
            $params = [$dateFrom, $dateTo];
        } elseif ($dateFrom) {
            $whereClause = "WHERE created_at >= ?";
            $params = [$dateFrom];
        }

        $stats = [];
        $stats['total'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} $whereClause", $params);
        $stats['pending'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} $whereClause AND status = 'pending'", array_merge($params, []));
        $stats['assigned'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} $whereClause AND status = 'assigned'", array_merge($params, []));
        $stats['in_progress'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} $whereClause AND status = 'in_progress'", array_merge($params, []));
        $stats['completed'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} $whereClause AND status = 'completed'", array_merge($params, []));
        $stats['cancelled'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} $whereClause AND status = 'cancelled'", array_merge($params, []));
        
        $stats['total_revenue'] = $this->db->getValue("SELECT SUM(final_cost) FROM {$this->table} $whereClause AND status = 'completed'", array_merge($params, []));
        $stats['avg_completion_time'] = $this->db->getValue(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) 
             FROM {$this->table} $whereClause AND status = 'completed' AND completed_at IS NOT NULL",
            array_merge($params, [])
        );

        return $stats;
    }
}
?>
