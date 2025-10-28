<?php
/**
 * Roadside Assistance Admin Platform - Dashboard Controller
 * Handles dashboard display and statistics
 */

class DashboardController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    // Show dashboard
    public function index() {
        try {
            // Get dashboard statistics
            $stats = $this->getDashboardStats();

            // Get recent service requests
            $recentRequests = $this->fetchRecentRequests(10);

            // Get driver statistics
            $driverStats = $this->getDriverStats();

            $data = [
                'pageTitle' => 'Dashboard',
                'stats' => $stats,
                'recentRequests' => $recentRequests,
                'driverStats' => $driverStats,
                'content' => $this->renderPartial('dashboard')
            ];

            $this->render('layout', $data);

        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'dashboard', 'Error loading dashboard data.');
        }
    }

    // Get dashboard statistics
    private function getDashboardStats() {
        $stats = [];

        try {
            // Active requests (pending, assigned, in_progress)
            $stats['active_requests'] = (int)$this->db->getValue(
                "SELECT COUNT(*) FROM service_requests
                 WHERE status IN ('pending', 'assigned', 'in_progress')"
            );

            // Completed today
            $stats['completed_today'] = (int)$this->db->getValue(
                "SELECT COUNT(*) FROM service_requests
                 WHERE status = 'completed' AND DATE(completed_at) = CURDATE()"
            );

            // Available drivers
            $stats['available_drivers'] = (int)$this->db->getValue(
                "SELECT COUNT(*) FROM drivers WHERE status = 'available'"
            );

            // Total customers
            $stats['total_customers'] = (int)$this->db->getValue(
                "SELECT COUNT(*) FROM customers WHERE status = 'active'"
            );

            // Revenue today
            $stats['revenue_today'] = (float)$this->db->getValue(
                "SELECT COALESCE(SUM(total_amount), 0) FROM service_requests
                 WHERE status = 'completed' AND DATE(completed_at) = CURDATE()"
            );

            // Average response time (in minutes)
            $stats['avg_response_time'] = (float)$this->db->getValue(
                "SELECT COALESCE(AVG(TIMESTAMPDIFF(MINUTE, created_at, assigned_at)), 0)
                 FROM service_requests WHERE assigned_at IS NOT NULL AND DATE(created_at) = CURDATE()"
            );

        } catch (Exception $e) {
            error_log("Error getting dashboard stats: " . $e->getMessage());
            // Set default values
            $stats = [
                'active_requests' => 0,
                'completed_today' => 0,
                'available_drivers' => 0,
                'total_customers' => 0,
                'revenue_today' => 0.00,
                'avg_response_time' => 0
            ];
        }

        return $stats;
    }

    // Get recent service requests
    private function fetchRecentRequests($limit = 10) {
        try {
            return $this->db->getRows(
                "SELECT
                    sr.id,
                    sr.service_type,
                    sr.location,
                    sr.status,
                    sr.created_at,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    c.phone as customer_phone
                 FROM service_requests sr
                 LEFT JOIN customers c ON sr.customer_id = c.id
                 ORDER BY sr.created_at DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (Exception $e) {
            error_log("Error getting recent requests: " . $e->getMessage());
            return [];
        }
    }

    // Get driver statistics
    private function getDriverStats() {
        try {
            return $this->db->getRows(
                "SELECT
                    first_name,
                    last_name,
                    phone,
                    status,
                    CONCAT(first_name, ' ', last_name) as name
                 FROM drivers
                 ORDER BY status DESC, first_name ASC
                 LIMIT 10"
            );
        } catch (Exception $e) {
            error_log("Error getting driver stats: " . $e->getMessage());
            return [];
        }
    }

    // API endpoint for dashboard data (AJAX refresh)
    public function getStats() {
        $this->requireLogin();

        try {
            $stats = $this->getDashboardStats();
            $this->jsonSuccess($stats);
        } catch (Exception $e) {
            $this->jsonError('Error retrieving dashboard statistics');
        }
    }

    // API endpoint for recent requests (AJAX refresh)
    public function getRecentRequests() {
        $this->requireLogin();

        try {
            $requests = $this->fetchRecentRequests(5);
            $this->jsonSuccess($requests);
        } catch (Exception $e) {
            $this->jsonError('Error retrieving recent requests');
        }
    }

    // API endpoint for driver status data (AJAX refresh)
    public function getDriverStatus() {
        $this->requireLogin();

        try {
            $drivers = $this->getDriverStats();
            $this->jsonSuccess($drivers);
        } catch (Exception $e) {
            $this->jsonError('Error retrieving driver status');
        }
    }

    // API endpoint for chart data
    public function getChartData() {
        $this->requireLogin();

        try {
            $chartData = [
                'requests_timeline' => $this->getRequestsTimelineData(),
                'service_type_distribution' => $this->getServiceTypeDistribution(),
                'driver_performance' => $this->getDriverPerformance(),
                'hourly_requests' => $this->getHourlyRequests()
            ];
            $this->jsonSuccess($chartData);
        } catch (Exception $e) {
            $this->jsonError('Error retrieving chart data');
        }
    }

    // Get requests timeline data (last 7 days)
    private function getRequestsTimelineData() {
        try {
            $data = $this->db->getRows(
                "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                 FROM service_requests
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC"
            );
            
            return $data ?: [];
        } catch (Exception $e) {
            error_log("Error getting timeline data: " . $e->getMessage());
            return [];
        }
    }

    // Get service type distribution
    private function getServiceTypeDistribution() {
        try {
            $data = $this->db->getRows(
                "SELECT 
                    service_type,
                    COUNT(*) as count
                 FROM service_requests
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY service_type
                 ORDER BY count DESC
                 LIMIT 5"
            );
            
            return $data ?: [];
        } catch (Exception $e) {
            error_log("Error getting service type distribution: " . $e->getMessage());
            return [];
        }
    }

    // Get driver performance metrics
    private function getDriverPerformance() {
        try {
            $data = $this->db->getRows(
                "SELECT 
                    CONCAT(d.first_name, ' ', d.last_name) as driver_name,
                    COUNT(sr.id) as total_requests,
                    SUM(CASE WHEN sr.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    AVG(TIMESTAMPDIFF(MINUTE, sr.assigned_at, sr.completed_at)) as avg_time
                 FROM drivers d
                 LEFT JOIN service_requests sr ON d.id = sr.driver_id
                 WHERE sr.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY d.id, d.first_name, d.last_name
                 HAVING total_requests > 0
                 ORDER BY completed DESC
                 LIMIT 5"
            );
            
            return $data ?: [];
        } catch (Exception $e) {
            error_log("Error getting driver performance: " . $e->getMessage());
            return [];
        }
    }

    // Get hourly request distribution (today)
    private function getHourlyRequests() {
        try {
            $data = $this->db->getRows(
                "SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as count
                 FROM service_requests
                 WHERE DATE(created_at) = CURDATE()
                 GROUP BY HOUR(created_at)
                 ORDER BY hour ASC"
            );
            
            return $data ?: [];
        } catch (Exception $e) {
            error_log("Error getting hourly requests: " . $e->getMessage());
            return [];
        }
    }

    // Get recent activity feed
    public function getRecentActivity() {
        $this->requireLogin();

        try {
            $activities = $this->db->getRows(
                "SELECT 
                    'request' as type,
                    sr.id as entity_id,
                    CONCAT('Service request #', sr.id, ' ', sr.status) as description,
                    CONCAT(c.first_name, ' ', c.last_name) as actor,
                    sr.updated_at as timestamp
                 FROM service_requests sr
                 LEFT JOIN customers c ON sr.customer_id = c.id
                 WHERE sr.updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 ORDER BY sr.updated_at DESC
                 LIMIT 10"
            );
            
            $this->jsonSuccess($activities);
        } catch (Exception $e) {
            $this->jsonError('Error retrieving recent activity');
        }
    }

    // Get performance metrics
    public function getPerformanceMetrics() {
        $this->requireLogin();

        try {
            $metrics = [
                'avg_response_time' => $this->getAverageResponseTime(),
                'completion_rate' => $this->getCompletionRate(),
                'customer_satisfaction' => $this->getCustomerSatisfaction(),
                'peak_hours' => $this->getPeakHours()
            ];
            
            $this->jsonSuccess($metrics);
        } catch (Exception $e) {
            $this->jsonError('Error retrieving performance metrics');
        }
    }

    // Calculate average response time
    private function getAverageResponseTime() {
        try {
            $avgTime = $this->db->getValue(
                "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, assigned_at))
                 FROM service_requests 
                 WHERE assigned_at IS NOT NULL 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            
            return round($avgTime ?: 0, 1);
        } catch (Exception $e) {
            return 0;
        }
    }

    // Calculate completion rate
    private function getCompletionRate() {
        try {
            $total = $this->db->getValue(
                "SELECT COUNT(*) FROM service_requests 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            
            $completed = $this->db->getValue(
                "SELECT COUNT(*) FROM service_requests 
                 WHERE status = 'completed' 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            
            return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    // Calculate customer satisfaction (placeholder)
    private function getCustomerSatisfaction() {
        // This would need a ratings table in a real implementation
        return 4.5; // Placeholder
    }

    // Get peak hours
    private function getPeakHours() {
        try {
            $result = $this->db->getRow(
                "SELECT 
                    HOUR(created_at) as peak_hour,
                    COUNT(*) as request_count
                 FROM service_requests
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY HOUR(created_at)
                 ORDER BY request_count DESC
                 LIMIT 1"
            );
            
            if ($result) {
                $hour = $result['peak_hour'];
                $time = date('g A', strtotime("$hour:00"));
                return $time;
            }
            return 'N/A';
        } catch (Exception $e) {
            return 'N/A';
        }
    }
}
?>
