<?php
/**
 * Roadside Assistance Admin Platform - Base Model
 * Provides common functionality for all models
 */

class Model {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Get all records
    public function all() {
        return $this->db->getRows("SELECT * FROM {$this->table} ORDER BY id DESC");
    }

    // Find record by ID
    public function find($id) {
        return $this->db->getRow("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    // Create record
    public function create($data) {
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        return $this->db->insert(
            "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)",
            array_values($data)
        );
    }

    // Update record
    public function update($id, $data) {
        $fields = implode(' = ?, ', array_keys($data)) . ' = ?';

        return $this->db->update(
            "UPDATE {$this->table} SET $fields WHERE id = ?",
            array_merge(array_values($data), [$id])
        );
    }

    // Delete record
    public function delete($id) {
        return $this->db->delete("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    // Count records
    public function count() {
        return (int)$this->db->getValue("SELECT COUNT(*) FROM {$this->table}");
    }

    // Check if record exists
    public function exists($id) {
        return (bool)$this->db->getValue("SELECT COUNT(*) FROM {$this->table} WHERE id = ?", [$id]);
    }

    // Get records with pagination
    public function paginate($page = 1, $perPage = 25) {
        $offset = ($page - 1) * $perPage;

        $total = $this->count();
        $items = $this->db->getRows(
            "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
}
?>
