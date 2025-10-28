<?php
/**
 * Roadside Assistance Admin Platform - Dispatch Queue Model
 * Manages priority-based request queuing for automated dispatch
 */

class DispatchQueue extends Model {
    protected $table = 'dispatch_queue';

    /**
     * Add a request to the dispatch queue
     */
    public function enqueue($requestId, $priority = 'normal') {
        $priorityValues = [
            'emergency' => 1,
            'high' => 2,
            'normal' => 3,
            'low' => 4
        ];
        
        $priorityOrder = $priorityValues[$priority] ?? 3;
        
        return $this->db->insert(
            "INSERT INTO {$this->table} 
             (request_id, priority, priority_order, status, created_at, updated_at) 
             VALUES (?, ?, ?, 'pending', NOW(), NOW())",
            [$requestId, $priority, $priorityOrder]
        );
    }

    /**
     * Get next request from queue based on priority
     */
    public function getNext() {
        return $this->db->getRow(
            "SELECT dq.*, sr.*, 
                    c.first_name as customer_first_name, c.last_name as customer_last_name,
                    st.name as service_type_name
             FROM {$this->table} dq
             INNER JOIN service_requests sr ON dq.request_id = sr.id
             LEFT JOIN customers c ON sr.customer_id = c.id
             LEFT JOIN service_types st ON sr.service_type_id = st.id
             WHERE dq.status = 'pending' AND sr.status = 'pending'
             ORDER BY dq.priority_order ASC, dq.created_at ASC
             LIMIT 1"
        );
    }

    /**
     * Get all pending requests in queue
     */
    public function getPending($limit = null) {
        $query = "SELECT dq.*, sr.*,
                         c.first_name as customer_first_name, c.last_name as customer_last_name,
                         st.name as service_type_name
                  FROM {$this->table} dq
                  INNER JOIN service_requests sr ON dq.request_id = sr.id
                  LEFT JOIN customers c ON sr.customer_id = c.id
                  LEFT JOIN service_types st ON sr.service_type_id = st.id
                  WHERE dq.status = 'pending' AND sr.status = 'pending'
                  ORDER BY dq.priority_order ASC, dq.created_at ASC";
        
        if ($limit) {
            $query .= " LIMIT ?";
            return $this->db->getRows($query, [$limit]);
        }
        
        return $this->db->getRows($query);
    }

    /**
     * Mark a queue item as processing
     */
    public function markProcessing($queueId) {
        return $this->db->update(
            "UPDATE {$this->table} 
             SET status = 'processing', processing_at = NOW(), updated_at = NOW() 
             WHERE id = ?",
            [$queueId]
        );
    }

    /**
     * Mark a queue item as dispatched
     */
    public function markDispatched($queueId, $driverId) {
        return $this->db->update(
            "UPDATE {$this->table} 
             SET status = 'dispatched', driver_id = ?, dispatched_at = NOW(), updated_at = NOW() 
             WHERE id = ?",
            [$driverId, $queueId]
        );
    }

    /**
     * Mark a queue item as failed
     */
    public function markFailed($queueId, $reason = null) {
        return $this->db->update(
            "UPDATE {$this->table} 
             SET status = 'failed', failure_reason = ?, updated_at = NOW() 
             WHERE id = ?",
            [$reason, $queueId]
        );
    }

    /**
     * Remove a request from the queue
     */
    public function removeRequest($requestId) {
        return $this->db->delete(
            "DELETE FROM {$this->table} WHERE request_id = ?",
            [$requestId]
        );
    }

    /**
     * Get queue statistics
     */
    public function getStats() {
        $stats = $this->db->getRow(
            "SELECT 
                COUNT(*) as total_queued,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'dispatched' THEN 1 ELSE 0 END) as dispatched,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN priority = 'emergency' THEN 1 ELSE 0 END) as emergency_requests
             FROM {$this->table}"
        );

        return $stats ?: [
            'total_queued' => 0,
            'pending' => 0,
            'processing' => 0,
            'dispatched' => 0,
            'failed' => 0,
            'emergency_requests' => 0
        ];
    }
}
?>
