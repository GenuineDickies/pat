<?php
/**
 * Roadside Assistance Admin Platform - Setting Model
 * Handles system settings and configuration
 */

class Setting extends Model {
    protected $table = 'settings';

    // Get setting by key
    public function getByKey($key) {
        return $this->db->getRow(
            "SELECT * FROM {$this->table} WHERE setting_key = ?",
            [$key]
        );
    }

    // Get setting value by key
    public function getValue($key, $default = null) {
        $setting = $this->getByKey($key);
        
        if (!$setting) {
            return $default;
        }

        // Cast value based on type
        switch ($setting['setting_type']) {
            case 'integer':
                return (int)$setting['setting_value'];
            case 'boolean':
                return filter_var($setting['setting_value'], FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($setting['setting_value'], true);
            default:
                return $setting['setting_value'];
        }
    }

    // Set setting value
    public function setValue($key, $value, $type = 'string', $description = null) {
        // Convert value based on type
        switch ($type) {
            case 'boolean':
                $value = $value ? 'true' : 'false';
                break;
            case 'json':
                $value = json_encode($value);
                break;
            default:
                $value = (string)$value;
        }

        $existing = $this->getByKey($key);

        if ($existing) {
            // Update existing setting
            return $this->db->update(
                "UPDATE {$this->table} SET setting_value = ?, setting_type = ?, updated_at = NOW() WHERE setting_key = ?",
                [$value, $type, $key]
            );
        } else {
            // Create new setting
            return $this->db->insert(
                "INSERT INTO {$this->table} (setting_key, setting_value, setting_type, description, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())",
                [$key, $value, $type, $description]
            );
        }
    }

    // Get all settings
    public function getAll($publicOnly = false) {
        $query = "SELECT * FROM {$this->table}";
        
        if ($publicOnly) {
            $query .= " WHERE is_public = 1";
        }
        
        $query .= " ORDER BY setting_key ASC";

        $settings = $this->db->getRows($query);

        // Convert to key-value array with proper types
        $result = [];
        foreach ($settings as $setting) {
            $value = $setting['setting_value'];
            
            // Cast value based on type
            switch ($setting['setting_type']) {
                case 'integer':
                    $value = (int)$value;
                    break;
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }

            $result[$setting['setting_key']] = [
                'value' => $value,
                'type' => $setting['setting_type'],
                'description' => $setting['description'],
                'is_public' => (bool)$setting['is_public']
            ];
        }

        return $result;
    }

    // Update multiple settings
    public function updateMultiple($settings) {
        $this->db->beginTransaction();

        try {
            foreach ($settings as $key => $data) {
                if (is_array($data)) {
                    $value = $data['value'];
                    $type = $data['type'] ?? 'string';
                } else {
                    $value = $data;
                    $type = 'string';
                }

                $this->setValue($key, $value, $type);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Delete setting
    public function deleteByKey($key) {
        return $this->db->delete(
            "DELETE FROM {$this->table} WHERE setting_key = ?",
            [$key]
        );
    }

    // Get settings by pattern
    public function getByPattern($pattern) {
        $settings = $this->db->getRows(
            "SELECT * FROM {$this->table} WHERE setting_key LIKE ? ORDER BY setting_key ASC",
            [$pattern]
        );

        $result = [];
        foreach ($settings as $setting) {
            $value = $setting['setting_value'];
            
            switch ($setting['setting_type']) {
                case 'integer':
                    $value = (int)$value;
                    break;
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }

            $result[$setting['setting_key']] = $value;
        }

        return $result;
    }
}
?>
