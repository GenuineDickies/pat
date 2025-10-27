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

    // Export report as CSV
    public function export() {
        $this->requirePermission('export_reports');

        try {
            $type = sanitize($_GET['type'] ?? 'daily');
            $date = sanitize($_GET['date'] ?? date('Y-m-d'));

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
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            error_log("Report export error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'reports', 'Error exporting report.');
        }
    }
}
?>
