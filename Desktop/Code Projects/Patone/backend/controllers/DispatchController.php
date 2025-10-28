<?php
/**
 * Roadside Assistance Admin Platform - Dispatch Controller
 * Manages automated and manual dispatch operations
 */

require_once __DIR__ . '/../models/DispatchQueue.php';
require_once __DIR__ . '/../models/DispatchAlgorithm.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/Driver.php';

class DispatchController extends Controller {
    private $queueModel;
    private $algorithm;
    private $requestModel;
    private $driverModel;

    public function __construct() {
        parent::__construct();
        $this->queueModel = new DispatchQueue();
        $this->algorithm = new DispatchAlgorithm();
        $this->requestModel = new ServiceRequest();
        $this->driverModel = new Driver();
    }

    /**
     * Display dispatch dashboard
     */
    public function index() {
        $this->requireAuth(['admin', 'manager', 'dispatcher']);

        // Get pending requests in queue
        $pendingRequests = $this->queueModel->getPending(20);
        
        // Get queue statistics
        $queueStats = $this->queueModel->getStats();
        
        // Get available drivers
        $availableDrivers = $this->driverModel->getAvailable();
        
        // Get recent dispatch history
        $recentDispatches = $this->getRecentDispatches(10);

        $this->render('dispatch/dashboard', [
            'title' => 'Dispatch Dashboard',
            'pendingRequests' => $pendingRequests,
            'queueStats' => $queueStats,
            'availableDrivers' => $availableDrivers,
            'recentDispatches' => $recentDispatches
        ]);
    }

    /**
     * Auto-dispatch next request in queue
     */
    public function autoDispatch() {
        $this->requireAuth(['admin', 'manager', 'dispatcher']);
        
        try {
            // Get next request from queue
            $queueItem = $this->queueModel->getNext();
            
            if (!$queueItem) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No pending requests in queue'
                ]);
                return;
            }

            // Mark as processing
            $this->queueModel->markProcessing($queueItem['id']);

            // Find best driver
            $result = $this->algorithm->findBestDriver($queueItem['request_id']);

            if (!$result) {
                // No suitable driver found
                $this->queueModel->markFailed($queueItem['id'], 'No available drivers');
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No available drivers found for this request'
                ]);
                return;
            }

            // Dispatch to driver
            $driver = $result['driver'];
            $dispatched = $this->algorithm->dispatch($queueItem['request_id'], $driver['id'], true);

            if ($dispatched) {
                // Mark queue item as dispatched
                $this->queueModel->markDispatched($queueItem['id'], $driver['id']);

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Request dispatched successfully',
                    'data' => [
                        'request_id' => $queueItem['request_id'],
                        'driver_id' => $driver['id'],
                        'driver_name' => $driver['first_name'] . ' ' . $driver['last_name'],
                        'score' => $result['score'],
                        'score_breakdown' => $result['score_breakdown']
                    ]
                ]);
            } else {
                $this->queueModel->markFailed($queueItem['id'], 'Dispatch failed');
                throw new Exception('Failed to assign driver to request');
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Manual dispatch with driver selection
     */
    public function manualDispatch() {
        $this->requireAuth(['admin', 'manager', 'dispatcher']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid request method']);
            return;
        }

        try {
            $requestId = intval($_POST['request_id'] ?? 0);
            $driverId = intval($_POST['driver_id'] ?? 0);

            if (!$requestId || !$driverId) {
                throw new Exception('Request ID and Driver ID are required');
            }

            // Verify request exists and is pending
            $request = $this->requestModel->getById($requestId);
            if (!$request || $request['status'] !== 'pending') {
                throw new Exception('Invalid or non-pending request');
            }

            // Verify driver exists and is available
            $driver = $this->driverModel->getById($driverId);
            if (!$driver) {
                throw new Exception('Driver not found');
            }

            // Dispatch with manual override
            $dispatched = $this->algorithm->dispatch($requestId, $driverId, false);

            if ($dispatched) {
                // Remove from queue if exists
                $this->queueModel->removeRequest($requestId);

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Request manually dispatched successfully',
                    'data' => [
                        'request_id' => $requestId,
                        'driver_id' => $driverId,
                        'driver_name' => $driver['first_name'] . ' ' . $driver['last_name']
                    ]
                ]);
            } else {
                throw new Exception('Failed to dispatch request');
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Find best driver for a specific request
     */
    public function findDriver($requestId) {
        $this->requireAuth(['admin', 'manager', 'dispatcher']);
        
        try {
            $requestId = intval($requestId);
            
            if (!$requestId) {
                throw new Exception('Invalid request ID');
            }

            // Find best driver
            $result = $this->algorithm->findBestDriver($requestId);

            if (!$result) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No suitable driver found'
                ]);
                return;
            }

            $driver = $result['driver'];
            
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'driver_id' => $driver['id'],
                    'driver_name' => $driver['first_name'] . ' ' . $driver['last_name'],
                    'score' => $result['score'],
                    'score_breakdown' => $result['score_breakdown'],
                    'driver' => $driver
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get available drivers for a request with scores
     */
    public function getDriverOptions($requestId) {
        $this->requireAuth(['admin', 'manager', 'dispatcher']);
        
        try {
            $requestId = intval($requestId);
            
            if (!$requestId) {
                throw new Exception('Invalid request ID');
            }

            $request = $this->requestModel->getById($requestId);
            if (!$request) {
                throw new Exception('Request not found');
            }

            // Get available drivers
            $availableDrivers = $this->driverModel->getAvailable(
                $request['location_latitude'],
                $request['location_longitude']
            );

            // Score each driver
            $driverOptions = [];
            foreach ($availableDrivers as $driver) {
                $score = $this->algorithm->calculateDriverScore($driver, $request);
                $driverOptions[] = [
                    'id' => $driver['id'],
                    'name' => $driver['first_name'] . ' ' . $driver['last_name'],
                    'score' => $score['total'],
                    'score_breakdown' => $score,
                    'distance' => $driver['distance'] ?? null,
                    'active_requests' => $driver['active_requests'] ?? 0,
                    'rating' => $driver['rating']
                ];
            }

            // Sort by score
            usort($driverOptions, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            $this->jsonResponse([
                'success' => true,
                'data' => $driverOptions
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Add request to dispatch queue
     */
    public function enqueue() {
        $this->requireAuth(['admin', 'manager', 'dispatcher']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid request method']);
            return;
        }

        try {
            $requestId = intval($_POST['request_id'] ?? 0);
            $priority = $_POST['priority'] ?? 'normal';

            if (!$requestId) {
                throw new Exception('Request ID is required');
            }

            // Verify request exists
            $request = $this->requestModel->getById($requestId);
            if (!$request || $request['status'] !== 'pending') {
                throw new Exception('Invalid or non-pending request');
            }

            // Add to queue
            $this->queueModel->enqueue($requestId, $priority);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Request added to dispatch queue'
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process emergency requests with highest priority
     */
    public function handleEmergency() {
        $this->requireAuth(['admin', 'manager', 'dispatcher']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid request method']);
            return;
        }

        try {
            $requestId = intval($_POST['request_id'] ?? 0);

            if (!$requestId) {
                throw new Exception('Request ID is required');
            }

            // Update request priority to emergency
            $this->requestModel->update($requestId, ['priority' => 'emergency']);

            // Add to queue with emergency priority (will be processed first)
            $this->queueModel->enqueue($requestId, 'emergency');

            // Try immediate dispatch
            $result = $this->algorithm->findBestDriver($requestId);

            if ($result) {
                $driver = $result['driver'];
                $this->algorithm->dispatch($requestId, $driver['id'], true);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Emergency request dispatched immediately',
                    'driver' => $driver['first_name'] . ' ' . $driver['last_name']
                ]);
            } else {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Emergency request queued (no available drivers)',
                    'queued' => true
                ]);
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get dispatch history
     */
    public function history() {
        $this->requireAuth(['admin', 'manager', 'dispatcher']);

        $limit = intval($_GET['limit'] ?? 50);
        $offset = intval($_GET['offset'] ?? 0);

        $history = $this->getDispatchHistory($limit, $offset);

        $this->render('dispatch/history', [
            'title' => 'Dispatch History',
            'history' => $history['records'],
            'total' => $history['total'],
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get recent dispatch records
     */
    private function getRecentDispatches($limit = 10) {
        return $this->db->getRows(
            "SELECT dh.*, 
                    sr.location_address, sr.priority,
                    d.first_name as driver_first_name, d.last_name as driver_last_name,
                    c.first_name as customer_first_name, c.last_name as customer_last_name
             FROM dispatch_history dh
             LEFT JOIN service_requests sr ON dh.request_id = sr.id
             LEFT JOIN drivers d ON dh.driver_id = d.id
             LEFT JOIN customers c ON sr.customer_id = c.id
             ORDER BY dh.dispatched_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get dispatch history with pagination
     */
    private function getDispatchHistory($limit, $offset) {
        $total = $this->db->getValue(
            "SELECT COUNT(*) FROM dispatch_history"
        );

        $records = $this->db->getRows(
            "SELECT dh.*, 
                    sr.location_address, sr.priority, sr.status as request_status,
                    d.first_name as driver_first_name, d.last_name as driver_last_name,
                    c.first_name as customer_first_name, c.last_name as customer_last_name
             FROM dispatch_history dh
             LEFT JOIN service_requests sr ON dh.request_id = sr.id
             LEFT JOIN drivers d ON dh.driver_id = d.id
             LEFT JOIN customers c ON sr.customer_id = c.id
             ORDER BY dh.dispatched_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );

        return [
            'records' => $records,
            'total' => $total
        ];
    }

    /**
     * Get queue statistics
     */
    public function queueStats() {
        $this->requireAuth(['admin', 'manager', 'dispatcher']);
        
        $stats = $this->queueModel->getStats();
        
        $this->jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }
}
?>
