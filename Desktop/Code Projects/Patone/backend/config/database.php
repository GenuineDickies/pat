<?php
/**
 * Roadside Assistance Admin Platform - Database Configuration
 * Handles MySQLi database connections and operations
 */

class Database {
    private static $instance = null;
    private $connection;
    private $host = DB_HOST;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $database = DB_NAME;

    private function __construct() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);

            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset(DB_CHARSET);

            // Enable error reporting for mysqli
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    // Execute query with prepared statement
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }

        if (!empty($params)) {
            $types = '';
            $values = [];

            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $values[] = $param;
            }

            $stmt->bind_param($types, ...$values);
        }

        $stmt->execute();
        return $stmt;
    }

    // Get single row
    public function getRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get multiple rows
    public function getRows($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get single value
    public function getValue($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        return $row ? $row[0] : null;
    }

    // Insert and return last insert ID
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->connection->insert_id;
    }

    // Update and return affected rows
    public function update($sql, $params = []) {
        $this->query($sql, $params);
        return $this->connection->affected_rows;
    }

    // Delete and return affected rows
    public function delete($sql, $params = []) {
        $this->query($sql, $params);
        return $this->connection->affected_rows;
    }

    // Escape string (for non-prepared queries)
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    // Get last error
    public function getError() {
        return $this->connection->error;
    }

    // Begin transaction
    public function beginTransaction() {
        $this->connection->begin_transaction();
    }

    // Commit transaction
    public function commit() {
        $this->connection->commit();
    }

    // Rollback transaction
    public function rollback() {
        $this->connection->rollback();
    }

    // Close connection (called automatically on destruct)
    public function close() {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    public function __destruct() {
        $this->close();
    }
}
?>
