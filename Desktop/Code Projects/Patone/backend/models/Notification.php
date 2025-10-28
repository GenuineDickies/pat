<?php
/**
 * Roadside Assistance Admin Platform - Notification Model
 * Handles user notifications for system events
 */

class Notification extends Model {
    protected $table = 'notifications';

    // Create notification
    public function createNotification($userId, $type, $title, $message, $relatedType = null, $relatedId = null) {
        return $this->db->insert(
            "INSERT INTO {$this->table} 
             (user_id, type, title, message, related_type, related_id, is_read, created_at)
             VALUES (?, ?, ?, ?, ?, ?, FALSE, NOW())",
            [$userId, $type, $title, $message, $relatedType, $relatedId]
        );
    }

    // Get unread notifications for user
    public function getUnread($userId) {
        return $this->db->getRows(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? AND is_read = FALSE 
             ORDER BY created_at DESC",
            [$userId]
        );
    }

    // Get all notifications for user
    public function getByUser($userId, $limit = 50) {
        return $this->db->getRows(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        );
    }

    // Mark as read
    public function markAsRead($id) {
        return $this->db->update(
            "UPDATE {$this->table} SET is_read = TRUE, read_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    // Mark all as read for user
    public function markAllAsRead($userId) {
        return $this->db->update(
            "UPDATE {$this->table} SET is_read = TRUE, read_at = NOW() 
             WHERE user_id = ? AND is_read = FALSE",
            [$userId]
        );
    }

    // Get unread count
    public function getUnreadCount($userId) {
        return (int)$this->db->getValue(
            "SELECT COUNT(*) FROM {$this->table} WHERE user_id = ? AND is_read = FALSE",
            [$userId]
        );
    }

    // Notify request assigned
    public function notifyRequestAssigned($userId, $requestId, $requestDetails) {
        return $this->createNotification(
            $userId,
            'request_assigned',
            'New Request Assigned',
            "Request #{$requestId} has been assigned to you: {$requestDetails}",
            'request',
            $requestId
        );
    }

    // Notify status changed
    public function notifyStatusChanged($userId, $requestId, $oldStatus, $newStatus) {
        return $this->createNotification(
            $userId,
            'status_changed',
            'Request Status Updated',
            "Request #{$requestId} status changed from {$oldStatus} to {$newStatus}",
            'request',
            $requestId
        );
    }

    // Notify request completed
    public function notifyRequestCompleted($userId, $requestId) {
        return $this->createNotification(
            $userId,
            'request_completed',
            'Request Completed',
            "Request #{$requestId} has been completed",
            'request',
            $requestId
        );
    }
}
?>
