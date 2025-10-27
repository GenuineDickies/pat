<?php
/**
 * Roadside Assistance Admin Platform - Service Type Model
 * Handles service type definitions and pricing
 */

class ServiceType extends Model {
    protected $table = 'service_types';

    // Get all active service types
    public function getActive() {
        return $this->db->getRows(
            "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY priority DESC, name ASC"
        );
    }

    // Get service type by ID
    public function getById($id) {
        return $this->db->getRow(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    // Get all service types with request counts
    public function getAllWithStats() {
        return $this->db->getRows(
            "SELECT st.*, 
                    (SELECT COUNT(*) FROM service_requests WHERE service_type_id = st.id) as total_requests,
                    (SELECT COUNT(*) FROM service_requests WHERE service_type_id = st.id AND status = 'completed') as completed_requests,
                    (SELECT AVG(final_cost) FROM service_requests WHERE service_type_id = st.id AND status = 'completed') as avg_cost
             FROM {$this->table} st
             ORDER BY st.priority DESC, st.name ASC"
        );
    }

    // Create new service type
    public function create($data) {
        $requiredFields = ['name', 'base_price', 'estimated_duration'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Required field missing: $field");
            }
        }

        $data['is_active'] = isset($data['is_active']) ? 1 : 0;
        $data['priority'] = $data['priority'] ?? 0;

        return $this->db->insert(
            "INSERT INTO {$this->table}
             (name, description, base_price, estimated_duration, is_active, priority, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['name'], $data['description'] ?? null, $data['base_price'],
                $data['estimated_duration'], $data['is_active'], $data['priority']
            ]
        );
    }

    // Update service type
    public function update($id, $data) {
        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        return $this->db->update(
            "UPDATE {$this->table} SET
             name = ?, description = ?, base_price = ?, estimated_duration = ?,
             is_active = ?, priority = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['name'], $data['description'] ?? null, $data['base_price'],
                $data['estimated_duration'], $data['is_active'], $data['priority'] ?? 0, $id
            ]
        );
    }

    // Toggle active status
    public function toggleActive($id) {
        return $this->db->update(
            "UPDATE {$this->table} SET is_active = NOT is_active, updated_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    // Get service type statistics
    public function getStats($id) {
        $stats = [];

        $stats['total_requests'] = (int)$this->db->getValue(
            "SELECT COUNT(*) FROM service_requests WHERE service_type_id = ?",
            [$id]
        );

        $stats['completed_requests'] = (int)$this->db->getValue(
            "SELECT COUNT(*) FROM service_requests WHERE service_type_id = ? AND status = 'completed'",
            [$id]
        );

        $stats['avg_cost'] = $this->db->getValue(
            "SELECT AVG(final_cost) FROM service_requests WHERE service_type_id = ? AND status = 'completed'",
            [$id]
        );

        $stats['avg_duration'] = $this->db->getValue(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) 
             FROM service_requests WHERE service_type_id = ? AND status = 'completed' 
             AND started_at IS NOT NULL AND completed_at IS NOT NULL",
            [$id]
        );

        $stats['total_revenue'] = $this->db->getValue(
            "SELECT SUM(final_cost) FROM service_requests WHERE service_type_id = ? AND status = 'completed'",
            [$id]
        );

        return $stats;
    }
}
?>
