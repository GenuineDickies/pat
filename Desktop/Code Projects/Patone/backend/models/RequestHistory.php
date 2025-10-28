<?php
/**
 * Roadside Assistance Admin Platform - Request History Model
 * Handles request history tracking and audit trail
 */

class RequestHistory extends Model {
    protected $table = 'request_history';

    // Add history entry
    public function addEntry($requestId, $actionType, $oldValue = null, $newValue = null, $notes = null) {
        $userId = $_SESSION['user_id'] ?? null;

        return $this->db->insert(
            "INSERT INTO {$this->table} (request_id, user_id, action_type, old_value, new_value, notes, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$requestId, $userId, $actionType, $oldValue, $newValue, $notes]
        );
    }

    // Get history for a request
    public function getByRequest($requestId) {
        return $this->db->getRows(
            "SELECT rh.*, 
                    u.username, u.first_name as user_first_name, u.last_name as user_last_name
             FROM {$this->table} rh
             LEFT JOIN users u ON rh.user_id = u.id
             WHERE rh.request_id = ?
             ORDER BY rh.created_at DESC",
            [$requestId]
        );
    }

    // Log status change
    public function logStatusChange($requestId, $oldStatus, $newStatus, $notes = null) {
        return $this->addEntry($requestId, 'status_change', $oldStatus, $newStatus, $notes);
    }

    // Log driver assignment
    public function logDriverAssignment($requestId, $driverId, $driverName = null) {
        return $this->addEntry($requestId, 'driver_assigned', null, $driverId, $driverName);
    }

    // Log note addition
    public function logNote($requestId, $note) {
        return $this->addEntry($requestId, 'note_added', null, null, $note);
    }

    // Log completion
    public function logCompletion($requestId, $finalCost = null) {
        return $this->addEntry($requestId, 'completed', null, $finalCost, "Request marked as completed");
    }

    // Log cancellation
    public function logCancellation($requestId, $reason) {
        return $this->addEntry($requestId, 'cancelled', null, null, $reason);
    }
}
?>
