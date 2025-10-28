<?php
/**
 * Roadside Assistance Admin Platform - Report Controller
 * Handles report generation and viewing
 */

require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/ServiceRequest.php';
require_once BACKEND_PATH . 'models/Customer.php';
require_once BACKEND_PATH . 'models/Driver.php';

class ReportController extends Controller {
    private $requestModel;
    private $customerModel;
    private $driverModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->requestModel = new ServiceRequest();
        $this->customerModel = new Customer();
        $this->driverModel = new Driver();
    }

    // Report dashboard
    public function index() {
        try {
            $data = [
                'pageTitle' => 'Reports & Analytics'
            ];

            $this->render('reports', $data);

        } catch (Exception $e) {
            error_log("Report index error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'dashboard', 'Error loading reports.');
        }
    }

    // Daily report
    public function daily() {
        try {
            $date = sanitize($_GET['date'] ?? date('Y-m-d'));

            $dateStart = $date . ' 00:00:00';
            $dateEnd = $date . ' 23:59:59';

            $stats = $this->requestModel->getStats($dateStart, $dateEnd);
            $requests = $this->db->getRows(
                "SELECT sr.*, 
                        c.first_name as customer_first_name, c.last_name as customer_last_name,
                        d.first_name as driver_first_name, d.last_name as driver_last_name,
                        st.name as service_type_name
                 FROM service_requests sr
                 LEFT JOIN customers c ON sr.customer_id = c.id
                 LEFT JOIN drivers d ON sr.driver_id = d.id
                 LEFT JOIN service_types st ON sr.service_type_id = st.id
                 WHERE sr.created_at BETWEEN ? AND ?
                 ORDER BY sr.created_at DESC",
                [$dateStart, $dateEnd]
            );

            $data = [
                'pageTitle' => 'Daily Report - ' . $date,
                'date' => $date,
                'stats' => $stats,
                'requests' => $requests
            ];

            $this->render('report_daily', $data);

        } catch (Exception $e) {
            error_log("Daily report error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'reports', 'Error generating daily report.');
        }
    }

    // Monthly report
    public function monthly() {
        try {
            $year = intval($_GET['year'] ?? date('Y'));
            $month = intval($_GET['month'] ?? date('m'));

            $dateStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
            $dateEnd = date('Y-m-t 23:59:59', strtotime($dateStart));

            $stats = $this->requestModel->getStats($dateStart, $dateEnd);

            // Get daily breakdown
            $dailyStats = $this->db->getRows(
                "SELECT DATE(created_at) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'completed' THEN final_cost ELSE 0 END) as revenue
                 FROM service_requests
                 WHERE created_at BETWEEN ? AND ?
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC",
                [$dateStart, $dateEnd]
            );

            // Get service type breakdown
            $serviceTypeStats = $this->db->getRows(
                "SELECT st.name,
                        COUNT(sr.id) as total,
                        SUM(CASE WHEN sr.status = 'completed' THEN 1 ELSE 0 END) as completed,
                        AVG(CASE WHEN sr.status = 'completed' THEN sr.final_cost ELSE NULL END) as avg_cost
                 FROM service_types st
                 LEFT JOIN service_requests sr ON st.id = sr.service_type_id 
                     AND sr.created_at BETWEEN ? AND ?
                 GROUP BY st.id, st.name
                 ORDER BY total DESC",
                [$dateStart, $dateEnd]
            );

            $data = [
                'pageTitle' => 'Monthly Report - ' . date('F Y', strtotime($dateStart)),
                'year' => $year,
                'month' => $month,
                'stats' => $stats,
                'dailyStats' => $dailyStats,
                'serviceTypeStats' => $serviceTypeStats
            ];

            $this->render('report_monthly', $data);

        } catch (Exception $e) {
            error_log("Monthly report error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'reports', 'Error generating monthly report.');
        }
    }

    // Driver performance report
    public function driverPerformance() {
        try {
            $driverId = intval($_GET['driver_id'] ?? 0);
            $days = intval($_GET['days'] ?? 30);

            if ($driverId > 0) {
                $driver = $this->driverModel->getById($driverId);
                $stats = $this->driverModel->getPerformanceStats($driverId, $days);
            } else {
                $driver = null;
                $stats = null;
            }

            // Get all drivers for selection
            $drivers = $this->driverModel->all();

            $data = [
                'pageTitle' => 'Driver Performance Report',
                'driver' => $driver,
                'stats' => $stats,
                'drivers' => $drivers,
                'days' => $days
            ];

            $this->render('report_driver_performance', $data);

        } catch (Exception $e) {
            error_log("Driver performance report error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'reports', 'Error generating driver report.');
        }
    }

    // Customer report
    public function customerReport() {
        try {
            $customerId = intval($_GET['customer_id'] ?? 0);

            if ($customerId > 0) {
                $customer = $this->customerModel->getById($customerId);
                $serviceHistory = $this->customerModel->getServiceHistory($customerId, 50);
            } else {
                $customer = null;
                $serviceHistory = [];
            }

            // Get all customers for selection
            $customers = $this->customerModel->all();

            $data = [
                'pageTitle' => 'Customer Report',
                'customer' => $customer,
                'serviceHistory' => $serviceHistory,
                'customers' => $customers
            ];

            $this->render('report_customer', $data);

        } catch (Exception $e) {
            error_log("Customer report error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'reports', 'Error generating customer report.');
        }
    }

    // Revenue and profitability report
    public function revenueReport() {
        try {
            $dateFrom = sanitize($_GET['date_from'] ?? date('Y-m-01'));
            $dateTo = sanitize($_GET['date_to'] ?? date('Y-m-d'));

            $dateStart = $dateFrom . ' 00:00:00';
            $dateEnd = $dateTo . ' 23:59:59';

            // Get revenue statistics
            $revenueStats = $this->db->getRow(
                "SELECT 
                    COUNT(*) as total_services,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_services,
                    SUM(CASE WHEN status = 'completed' THEN final_cost ELSE 0 END) as total_revenue,
                    AVG(CASE WHEN status = 'completed' THEN final_cost ELSE NULL END) as avg_revenue_per_service,
                    MIN(CASE WHEN status = 'completed' THEN final_cost ELSE NULL END) as min_service_cost,
                    MAX(CASE WHEN status = 'completed' THEN final_cost ELSE NULL END) as max_service_cost
                FROM service_requests
                WHERE created_at BETWEEN ? AND ?",
                [$dateStart, $dateEnd]
            );

            // Revenue by service type
            $revenueByType = $this->db->getRows(
                "SELECT st.name as service_type,
                        COUNT(sr.id) as total_requests,
                        SUM(CASE WHEN sr.status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN sr.status = 'completed' THEN sr.final_cost ELSE 0 END) as revenue,
                        AVG(CASE WHEN sr.status = 'completed' THEN sr.final_cost ELSE NULL END) as avg_cost
                FROM service_types st
                LEFT JOIN service_requests sr ON st.id = sr.service_type_id 
                    AND sr.created_at BETWEEN ? AND ?
                WHERE st.is_active = 1
                GROUP BY st.id, st.name
                ORDER BY revenue DESC",
                [$dateStart, $dateEnd]
            );

            // Revenue by driver
            $revenueByDriver = $this->db->getRows(
                "SELECT d.first_name, d.last_name,
                        COUNT(sr.id) as services_completed,
                        SUM(sr.final_cost) as total_revenue,
                        AVG(sr.final_cost) as avg_revenue_per_service
                FROM drivers d
                LEFT JOIN service_requests sr ON d.id = sr.driver_id
                    AND sr.created_at BETWEEN ? AND ?
                    AND sr.status = 'completed'
                GROUP BY d.id, d.first_name, d.last_name
                HAVING services_completed > 0
                ORDER BY total_revenue DESC
                LIMIT 10",
                [$dateStart, $dateEnd]
            );

            // Daily revenue trend
            $dailyRevenue = $this->db->getRows(
                "SELECT DATE(created_at) as date,
                        COUNT(*) as requests,
                        SUM(CASE WHEN status = 'completed' THEN final_cost ELSE 0 END) as revenue
                FROM service_requests
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date ASC",
                [$dateStart, $dateEnd]
            );

            $data = [
                'pageTitle' => 'Revenue & Profitability Report',
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'revenueStats' => $revenueStats,
                'revenueByType' => $revenueByType,
                'revenueByDriver' => $revenueByDriver,
                'dailyRevenue' => $dailyRevenue
            ];

            $this->render('report_revenue', $data);

        } catch (Exception $e) {
            error_log("Revenue report error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'reports', 'Error generating revenue report.');
        }
    }

    // Service demand forecasting
    public function demandForecast() {
        try {
            $days = intval($_GET['days'] ?? 30);
            
            // Get historical demand data
            $demandData = $this->db->getRows(
                "SELECT DATE(created_at) as date,
                        DAYNAME(created_at) as day_name,
                        HOUR(created_at) as hour,
                        COUNT(*) as request_count
                FROM service_requests
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at), DAYNAME(created_at), HOUR(created_at)
                ORDER BY date, hour",
                [$days]
            );

            // Calculate peak hours
            $hourlyDemand = $this->db->getRows(
                "SELECT HOUR(created_at) as hour,
                        COUNT(*) as request_count,
                        AVG(COUNT(*)) OVER () as avg_requests
                FROM service_requests
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY HOUR(created_at)
                ORDER BY hour",
                [$days]
            );

            // Calculate peak days
            $dailyDemand = $this->db->getRows(
                "SELECT DAYNAME(created_at) as day_name,
                        COUNT(*) as request_count
                FROM service_requests
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DAYNAME(created_at)
                ORDER BY FIELD(DAYNAME(created_at), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')",
                [$days]
            );

            $data = [
                'pageTitle' => 'Service Demand Forecast',
                'days' => $days,
                'demandData' => $demandData,
                'hourlyDemand' => $hourlyDemand,
                'dailyDemand' => $dailyDemand
            ];

            $this->render('report_demand_forecast', $data);

        } catch (Exception $e) {
            error_log("Demand forecast error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'reports', 'Error generating demand forecast.');
        }
    }

    // Custom report generation
    public function customReport() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $dateFrom = sanitize($_POST['date_from']);
                $dateTo = sanitize($_POST['date_to']);
                $reportType = sanitize($_POST['report_type']);
                $groupBy = sanitize($_POST['group_by'] ?? 'date');
                $filters = $_POST['filters'] ?? [];

                // Build query based on selections
                $whereConditions = ["sr.created_at BETWEEN ? AND ?"];
                $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];

                if (!empty($filters['status'])) {
                    $whereConditions[] = "sr.status = ?";
                    $params[] = $filters['status'];
                }

                if (!empty($filters['service_type_id'])) {
                    $whereConditions[] = "sr.service_type_id = ?";
                    $params[] = $filters['service_type_id'];
                }

                $whereClause = implode(' AND ', $whereConditions);

                // Execute custom query
                $reportData = $this->db->getRows(
                    "SELECT sr.*, 
                            c.first_name as customer_first_name, c.last_name as customer_last_name,
                            d.first_name as driver_first_name, d.last_name as driver_last_name,
                            st.name as service_type_name
                    FROM service_requests sr
                    LEFT JOIN customers c ON sr.customer_id = c.id
                    LEFT JOIN drivers d ON sr.driver_id = d.id
                    LEFT JOIN service_types st ON sr.service_type_id = st.id
                    WHERE $whereClause
                    ORDER BY sr.created_at DESC",
                    $params
                );

                $data = [
                    'pageTitle' => 'Custom Report Results',
                    'reportData' => $reportData,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'reportType' => $reportType
                ];

                $this->render('report_custom_results', $data);
            } else {
                // Show custom report form
                $serviceTypes = $this->db->getRows("SELECT id, name FROM service_types WHERE is_active = 1");
                
                $data = [
                    'pageTitle' => 'Custom Report Generator',
                    'serviceTypes' => $serviceTypes
                ];

                $this->render('report_custom', $data);
            }

        } catch (Exception $e) {
            error_log("Custom report error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'reports', 'Error generating custom report.');
        }
    }

    // Export report as CSV
    public function export() {
        $this->requirePermission('export_reports');

        try {
            $type = sanitize($_GET['type'] ?? 'daily');
            $date = sanitize($_GET['date'] ?? date('Y-m-d'));
            $format = sanitize($_GET['format'] ?? 'csv');

            if ($format === 'excel') {
                $this->exportExcel($type, $date);
            } else {
                $this->exportCSV($type, $date);
            }

        } catch (Exception $e) {
            error_log("Report export error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'reports', 'Error exporting report.');
        }
    }

    private function exportCSV($type, $date) {
        $filename = "report_{$type}_{$date}.csv";

        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        if ($type === 'daily') {
            $dateStart = $date . ' 00:00:00';
            $dateEnd = $date . ' 23:59:59';

            fputcsv($output, ['ID', 'Customer', 'Service Type', 'Driver', 'Status', 'Priority', 'Created', 'Cost']);

            $requests = $this->db->getRows(
                "SELECT sr.id, 
                        CONCAT(c.first_name, ' ', c.last_name) as customer,
                        st.name as service_type,
                        CONCAT(d.first_name, ' ', d.last_name) as driver,
                        sr.status, sr.priority, sr.created_at, sr.final_cost
                 FROM service_requests sr
                 LEFT JOIN customers c ON sr.customer_id = c.id
                 LEFT JOIN drivers d ON sr.driver_id = d.id
                 LEFT JOIN service_types st ON sr.service_type_id = st.id
                 WHERE sr.created_at BETWEEN ? AND ?
                 ORDER BY sr.created_at DESC",
                [$dateStart, $dateEnd]
            );

            foreach ($requests as $request) {
                fputcsv($output, $request);
            }
        } elseif ($type === 'monthly') {
            $year = intval($_GET['year'] ?? date('Y'));
            $month = intval($_GET['month'] ?? date('m'));
            $dateStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
            $dateEnd = date('Y-m-t 23:59:59', strtotime($dateStart));

            fputcsv($output, ['Date', 'Total Requests', 'Completed', 'Revenue']);

            $dailyStats = $this->db->getRows(
                "SELECT DATE(created_at) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'completed' THEN final_cost ELSE 0 END) as revenue
                 FROM service_requests
                 WHERE created_at BETWEEN ? AND ?
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC",
                [$dateStart, $dateEnd]
            );

            foreach ($dailyStats as $stat) {
                fputcsv($output, $stat);
            }
        }

        fclose($output);
        exit;
    }

    private function exportExcel($type, $date) {
        // Simple Excel export using HTML table format that Excel can read
        $filename = "report_{$type}_{$date}.xls";

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        echo "<html><body><table border='1'>";

        if ($type === 'daily') {
            $dateStart = $date . ' 00:00:00';
            $dateEnd = $date . ' 23:59:59';

            echo "<tr><th>ID</th><th>Customer</th><th>Service Type</th><th>Driver</th><th>Status</th><th>Priority</th><th>Created</th><th>Cost</th></tr>";

            $requests = $this->db->getRows(
                "SELECT sr.id, 
                        CONCAT(c.first_name, ' ', c.last_name) as customer,
                        st.name as service_type,
                        CONCAT(d.first_name, ' ', d.last_name) as driver,
                        sr.status, sr.priority, sr.created_at, sr.final_cost
                 FROM service_requests sr
                 LEFT JOIN customers c ON sr.customer_id = c.id
                 LEFT JOIN drivers d ON sr.driver_id = d.id
                 LEFT JOIN service_types st ON sr.service_type_id = st.id
                 WHERE sr.created_at BETWEEN ? AND ?
                 ORDER BY sr.created_at DESC",
                [$dateStart, $dateEnd]
            );

            foreach ($requests as $request) {
                echo "<tr>";
                foreach ($request as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
        }

        echo "</table></body></html>";
        exit;
    }
}
?>
