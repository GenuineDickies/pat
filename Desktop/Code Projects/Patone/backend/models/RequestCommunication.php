<?php
/**
 * Roadside Assistance Admin Platform - Request Communication Model
 * Handles communication logs for service requests
 */

class RequestCommunication extends Model {
    protected $table = 'request_communications';

    // Add communication log
    public function addLog($requestId, $type, $message, $direction = 'internal', $subject = null, $recipient = null) {
        $userId = $_SESSION['user_id'] ?? null;

        return $this->db->insert(
            "INSERT INTO {$this->table} 
             (request_id, user_id, communication_type, direction, subject, message, recipient, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'sent', NOW())",
            [$requestId, $userId, $type, $direction, $subject, $message, $recipient]
        );
    }

    // Get communications for a request
    public function getByRequest($requestId) {
        return $this->db->getRows(
            "SELECT rc.*, 
                    u.username, u.first_name as user_first_name, u.last_name as user_last_name
             FROM {$this->table} rc
             LEFT JOIN users u ON rc.user_id = u.id
             WHERE rc.request_id = ?
             ORDER BY rc.created_at DESC",
            [$requestId]
        );
    }

    // Add note
    public function addNote($requestId, $message) {
        return $this->addLog($requestId, 'note', $message, 'internal');
    }

    // Log email sent
    public function logEmail($requestId, $recipient, $subject, $message) {
        return $this->addLog($requestId, 'email', $message, 'outbound', $subject, $recipient);
    }

    // Log SMS sent
    public function logSMS($requestId, $recipient, $message) {
        return $this->addLog($requestId, 'sms', $message, 'outbound', null, $recipient);
    }

    // Log phone call
    public function logCall($requestId, $direction, $notes) {
        return $this->addLog($requestId, 'call', $notes, $direction);
    }

    // Log system message
    public function logSystem($requestId, $message) {
        return $this->addLog($requestId, 'system', $message, 'internal');
    }
}
?>
