<?php
/**
 * Roadside Assistance Admin Platform - Driver Model
 * Handles driver data operations and GPS tracking
 */

class Driver extends Model {
    protected $table = 'drivers';

    // Get driver by ID
    public function getById($id) {
        return $this->db->getRow(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    // Get driver by email
    public function getByEmail($email) {
        return $this->db->getRow(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    // Get all drivers with filtering
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

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // Get total count
        $totalQuery = "SELECT COUNT(*) FROM {$this->table} $whereClause";
        $total = (int)$this->db->getValue($totalQuery, $params);

        // Get drivers
        $orderBy = "ORDER BY last_name, first_name";
        $limitClause = $limit ? "LIMIT ? OFFSET ?" : "";
        if ($limit) {
            $params[] = $limit;
            $params[] = $offset;
        }

        $query = "SELECT *,
                         (SELECT COUNT(*) FROM service_requests WHERE driver_id = {$this->table}.id) as total_requests,
                         (SELECT COUNT(*) FROM service_requests WHERE driver_id = {$this->table}.id AND status = 'completed') as completed_requests
                  FROM {$this->table}
                  $whereClause $orderBy $limitClause";

        $drivers = $this->db->getRows($query, $params);

        return [
            'drivers' => $drivers,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    // Get available drivers for dispatch
    public function getAvailable($latitude = null, $longitude = null, $maxDistance = 50) {
        $query = "SELECT *,
                         (SELECT COUNT(*) FROM service_requests WHERE driver_id = {$this->table}.id AND status IN ('assigned', 'in_progress')) as active_requests";
        
        if ($latitude && $longitude) {
            // Calculate distance using Haversine formula
            $query .= ",
                      (6371 * acos(cos(radians(?)) * cos(radians(current_latitude)) * 
                       cos(radians(current_longitude) - radians(?)) + 
                       sin(radians(?)) * sin(radians(current_latitude)))) as distance";
        }

        $query .= " FROM {$this->table}
                    WHERE status = 'available'";

        $params = [];
        if ($latitude && $longitude) {
            $params = [$latitude, $longitude, $latitude];
            $query .= " HAVING distance <= ?";
            $params[] = $maxDistance;
        }

        $query .= " ORDER BY active_requests ASC, rating DESC";

        return $this->db->getRows($query, $params);
    }

    // Create new driver
    public function create($data) {
        $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'license_number', 'license_state', 'license_expiry'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field missing: $field");
            }
        }

        // Clean phone numbers
        $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);

        // Set defaults
        $data['status'] = $data['status'] ?? 'offline';
        $data['rating'] = $data['rating'] ?? 0.00;
        $data['total_jobs'] = $data['total_jobs'] ?? 0;
        $data['completed_jobs'] = $data['completed_jobs'] ?? 0;

        return $this->db->insert(
            "INSERT INTO {$this->table}
             (user_id, first_name, last_name, email, phone, license_number, license_state, 
              license_expiry, vehicle_info, status, rating, total_jobs, completed_jobs, notes, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['user_id'] ?? null, $data['first_name'], $data['last_name'], 
                $data['email'], $data['phone'], $data['license_number'], 
                $data['license_state'], $data['license_expiry'], $data['vehicle_info'] ?? null,
                $data['status'], $data['rating'], $data['total_jobs'], 
                $data['completed_jobs'], $data['notes'] ?? null
            ]
        );
    }

    // Update driver
    public function update($id, $data) {
        $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'license_number', 'license_state', 'license_expiry'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field missing: $field");
            }
        }

        // Clean phone numbers
        $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);

        return $this->db->update(
            "UPDATE {$this->table} SET
             user_id = ?, first_name = ?, last_name = ?, email = ?, phone = ?,
             license_number = ?, license_state = ?, license_expiry = ?, vehicle_info = ?,
             status = ?, notes = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['user_id'] ?? null, $data['first_name'], $data['last_name'],
                $data['email'], $data['phone'], $data['license_number'],
                $data['license_state'], $data['license_expiry'], $data['vehicle_info'] ?? null,
                $data['status'], $data['notes'] ?? null, $id
            ]
        );
    }

    // Update driver location (GPS)
    public function updateLocation($id, $latitude, $longitude) {
        return $this->db->update(
            "UPDATE {$this->table} SET
             current_latitude = ?, current_longitude = ?, last_location_update = NOW(), updated_at = NOW()
             WHERE id = ?",
            [$latitude, $longitude, $id]
        );
    }

    // Update driver status
    public function updateStatus($id, $status) {
        $validStatuses = ['available', 'busy', 'offline', 'on_break'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status: $status");
        }

        return $this->db->update(
            "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $id]
        );
    }

    // Update driver rating
    public function updateRating($id, $rating) {
        if ($rating < 0 || $rating > 5) {
            throw new Exception("Rating must be between 0 and 5");
        }

        return $this->db->update(
            "UPDATE {$this->table} SET rating = ?, updated_at = NOW() WHERE id = ?",
            [$rating, $id]
        );
    }

    // Increment job counters
    public function incrementJobCounters($id, $completed = false) {
        $query = "UPDATE {$this->table} SET total_jobs = total_jobs + 1";
        if ($completed) {
            $query .= ", completed_jobs = completed_jobs + 1";
        }
        $query .= ", updated_at = NOW() WHERE id = ?";

        return $this->db->update($query, [$id]);
    }

    // Get driver performance stats
    public function getPerformanceStats($id, $days = 30) {
        $stats = [];

        $stats['total_requests'] = (int)$this->db->getValue(
            "SELECT COUNT(*) FROM service_requests WHERE driver_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$id, $days]
        );

        $stats['completed_requests'] = (int)$this->db->getValue(
            "SELECT COUNT(*) FROM service_requests WHERE driver_id = ? AND status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$id, $days]
        );

        $stats['avg_completion_time'] = $this->db->getValue(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) FROM service_requests 
             WHERE driver_id = ? AND status = 'completed' AND started_at IS NOT NULL AND completed_at IS NOT NULL
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$id, $days]
        );

        $stats['avg_rating'] = $this->db->getValue(
            "SELECT AVG(rating) FROM service_requests WHERE driver_id = ? AND rating IS NOT NULL 
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$id, $days]
        );

        $stats['total_earnings'] = $this->db->getValue(
            "SELECT SUM(final_cost) FROM service_requests WHERE driver_id = ? AND status = 'completed' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$id, $days]
        );

        return $stats;
    }

    // Get driver statistics
    public function getStats() {
        $stats = [];

        $stats['total'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table}");
        $stats['available'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} WHERE status = 'available'");
        $stats['busy'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} WHERE status = 'busy'");
        $stats['offline'] = (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} WHERE status = 'offline'");

        return $stats;
    }
}
?>
