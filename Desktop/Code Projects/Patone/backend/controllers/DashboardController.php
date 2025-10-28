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
}
?>
