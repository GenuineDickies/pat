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

    // ============================================
    // Certification Management
    // ============================================

    // Get driver certifications
    public function getCertifications($id) {
        return $this->db->getRows(
            "SELECT * FROM driver_certifications WHERE driver_id = ? ORDER BY expiry_date ASC",
            [$id]
        );
    }

    // Add certification
    public function addCertification($driverId, $data) {
        return $this->db->insert(
            "INSERT INTO driver_certifications 
             (driver_id, certification_type, certification_number, issuing_authority, 
              issue_date, expiry_date, status, document_path, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $driverId,
                $data['certification_type'],
                $data['certification_number'] ?? null,
                $data['issuing_authority'] ?? null,
                $data['issue_date'] ?? null,
                $data['expiry_date'] ?? null,
                $data['status'] ?? 'active',
                $data['document_path'] ?? null,
                $data['notes'] ?? null
            ]
        );
    }

    // Update certification
    public function updateCertification($id, $data) {
        return $this->db->update(
            "UPDATE driver_certifications SET
             certification_type = ?, certification_number = ?, issuing_authority = ?,
             issue_date = ?, expiry_date = ?, status = ?, document_path = ?, notes = ?,
             updated_at = NOW()
             WHERE id = ?",
            [
                $data['certification_type'],
                $data['certification_number'] ?? null,
                $data['issuing_authority'] ?? null,
                $data['issue_date'] ?? null,
                $data['expiry_date'] ?? null,
                $data['status'] ?? 'active',
                $data['document_path'] ?? null,
                $data['notes'] ?? null,
                $id
            ]
        );
    }

    // Delete certification
    public function deleteCertification($id) {
        return $this->db->delete("DELETE FROM driver_certifications WHERE id = ?", [$id]);
    }

    // Get expiring certifications (within days)
    public function getExpiringCertifications($days = 30) {
        return $this->db->getRows(
            "SELECT dc.*, d.first_name, d.last_name, d.email
             FROM driver_certifications dc
             JOIN drivers d ON dc.driver_id = d.id
             WHERE dc.expiry_date IS NOT NULL 
             AND dc.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
             AND dc.expiry_date >= CURDATE()
             AND dc.status = 'active'
             ORDER BY dc.expiry_date ASC",
            [$days]
        );
    }

    // ============================================
    // Document Management
    // ============================================

    // Get driver documents
    public function getDocuments($id) {
        return $this->db->getRows(
            "SELECT * FROM driver_documents WHERE driver_id = ? ORDER BY created_at DESC",
            [$id]
        );
    }

    // Add document
    public function addDocument($driverId, $data) {
        return $this->db->insert(
            "INSERT INTO driver_documents 
             (driver_id, document_type, document_name, file_path, file_size, 
              mime_type, expiry_date, status, uploaded_by, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $driverId,
                $data['document_type'],
                $data['document_name'],
                $data['file_path'],
                $data['file_size'] ?? null,
                $data['mime_type'] ?? null,
                $data['expiry_date'] ?? null,
                $data['status'] ?? 'pending_review',
                $data['uploaded_by'] ?? null,
                $data['notes'] ?? null
            ]
        );
    }

    // Update document
    public function updateDocument($id, $data) {
        return $this->db->update(
            "UPDATE driver_documents SET
             document_type = ?, document_name = ?, expiry_date = ?, 
             status = ?, notes = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['document_type'],
                $data['document_name'],
                $data['expiry_date'] ?? null,
                $data['status'] ?? 'pending_review',
                $data['notes'] ?? null,
                $id
            ]
        );
    }

    // Delete document
    public function deleteDocument($id) {
        return $this->db->delete("DELETE FROM driver_documents WHERE id = ?", [$id]);
    }

    // ============================================
    // Availability Scheduling
    // ============================================

    // Get driver availability schedule
    public function getAvailabilitySchedule($id) {
        return $this->db->getRows(
            "SELECT * FROM driver_availability_schedule 
             WHERE driver_id = ? 
             ORDER BY day_of_week ASC, start_time ASC",
            [$id]
        );
    }

    // Set availability schedule
    public function setAvailabilitySchedule($driverId, $dayOfWeek, $startTime, $endTime, $isAvailable = true, $notes = null) {
        // Check if schedule exists
        $existing = $this->db->getRow(
            "SELECT id FROM driver_availability_schedule 
             WHERE driver_id = ? AND day_of_week = ? AND start_time = ?",
            [$driverId, $dayOfWeek, $startTime]
        );

        if ($existing) {
            // Update existing
            return $this->db->update(
                "UPDATE driver_availability_schedule 
                 SET end_time = ?, is_available = ?, notes = ?, updated_at = NOW()
                 WHERE id = ?",
                [$endTime, $isAvailable, $notes, $existing['id']]
            );
        } else {
            // Insert new
            return $this->db->insert(
                "INSERT INTO driver_availability_schedule 
                 (driver_id, day_of_week, start_time, end_time, is_available, notes)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$driverId, $dayOfWeek, $startTime, $endTime, $isAvailable, $notes]
            );
        }
    }

    // Delete availability schedule
    public function deleteAvailabilitySchedule($id) {
        return $this->db->delete("DELETE FROM driver_availability_schedule WHERE id = ?", [$id]);
    }

    // Check if driver is scheduled to be available now
    public function isScheduledAvailable($id) {
        $now = new DateTime();
        $dayOfWeek = (int)$now->format('w'); // 0 = Sunday
        $currentTime = $now->format('H:i:s');

        $schedule = $this->db->getRow(
            "SELECT * FROM driver_availability_schedule 
             WHERE driver_id = ? 
             AND day_of_week = ? 
             AND start_time <= ? 
             AND end_time >= ?
             AND is_available = 1",
            [$id, $dayOfWeek, $currentTime, $currentTime]
        );

        return !empty($schedule);
    }

    // ============================================
    // Workload Balancing
    // ============================================

    // Get driver workload
    public function getWorkload($id) {
        $activeRequests = (int)$this->db->getValue(
            "SELECT COUNT(*) FROM service_requests 
             WHERE driver_id = ? AND status IN ('assigned', 'in_progress')",
            [$id]
        );

        $driver = $this->getById($id);
        $maxWorkload = $driver['max_workload'] ?? 3;

        return [
            'current' => $activeRequests,
            'max' => $maxWorkload,
            'available_capacity' => max(0, $maxWorkload - $activeRequests),
            'utilization_percentage' => $maxWorkload > 0 ? ($activeRequests / $maxWorkload * 100) : 0
        ];
    }

    // Update driver workload
    public function updateWorkload($id) {
        $activeRequests = (int)$this->db->getValue(
            "SELECT COUNT(*) FROM service_requests 
             WHERE driver_id = ? AND status IN ('assigned', 'in_progress')",
            [$id]
        );

        return $this->db->update(
            "UPDATE {$this->table} SET current_workload = ?, updated_at = NOW() WHERE id = ?",
            [$activeRequests, $id]
        );
    }

    // Get drivers with available capacity (for workload balancing)
    public function getDriversWithCapacity($latitude = null, $longitude = null, $maxDistance = 50) {
        $query = "SELECT *,
                         (max_workload - current_workload) as available_capacity";
        
        if ($latitude && $longitude) {
            $query .= ",
                      (6371 * acos(cos(radians(?)) * cos(radians(current_latitude)) * 
                       cos(radians(current_longitude) - radians(?)) + 
                       sin(radians(?)) * sin(radians(current_latitude)))) as distance";
        }

        $query .= " FROM {$this->table}
                    WHERE status = 'available'
                    AND (max_workload - current_workload) > 0";

        $params = [];
        if ($latitude && $longitude) {
            $params = [$latitude, $longitude, $latitude];
            $query .= " HAVING distance <= ?";
            $params[] = $maxDistance;
        }

        $query .= " ORDER BY available_capacity DESC, rating DESC";

        return $this->db->getRows($query, $params);
    }

    // Set max workload for driver
    public function setMaxWorkload($id, $maxWorkload) {
        if ($maxWorkload < 1) {
            throw new Exception("Max workload must be at least 1");
        }

        return $this->db->update(
            "UPDATE {$this->table} SET max_workload = ?, updated_at = NOW() WHERE id = ?",
            [$maxWorkload, $id]
        );
    }

    // Get workload distribution across all drivers
    public function getWorkloadDistribution() {
        return $this->db->getRows(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name, 
                    status, current_workload, max_workload,
                    (max_workload - current_workload) as available_capacity,
                    ROUND((current_workload / max_workload * 100), 2) as utilization_percentage
             FROM {$this->table}
             WHERE status IN ('available', 'busy')
             ORDER BY utilization_percentage DESC, rating DESC"
        );
    }
}
?>
