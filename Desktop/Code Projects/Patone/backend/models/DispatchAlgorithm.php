<?php
/**
 * Roadside Assistance Admin Platform - Dispatch Algorithm
 * Intelligent driver selection based on multiple factors
 */

require_once __DIR__ . '/Driver.php';
require_once __DIR__ . '/ServiceRequest.php';

class DispatchAlgorithm {
    private $db;
    private $driverModel;
    private $requestModel;

    // Weight factors for scoring
    private $weights = [
        'proximity' => 0.40,      // 40% weight on distance
        'workload' => 0.25,       // 25% weight on current workload
        'rating' => 0.20,         // 20% weight on driver rating
        'availability' => 0.15    // 15% weight on availability score
    ];

    public function __construct() {
        $this->db = Database::getInstance();
        $this->driverModel = new Driver();
        $this->requestModel = new ServiceRequest();
    }

    /**
     * Find the best driver for a service request
     * 
     * @param int $requestId Service request ID
     * @param array $options Additional options (manual_override, preferred_driver_id)
     * @return array|null Driver data with score, or null if no suitable driver
     */
    public function findBestDriver($requestId, $options = []) {
        // Get request details
        $request = $this->requestModel->getById($requestId);
        if (!$request) {
            throw new Exception("Request not found");
        }

        // Check for manual override
        if (!empty($options['manual_override']) && !empty($options['preferred_driver_id'])) {
            $driver = $this->driverModel->getById($options['preferred_driver_id']);
            if ($driver && $driver['status'] === 'available') {
                return [
                    'driver' => $driver,
                    'score' => 100,
                    'reason' => 'manual_override'
                ];
            }
        }

        // Get available drivers
        $availableDrivers = $this->getAvailableDrivers($request);
        
        if (empty($availableDrivers)) {
            return null;
        }

        // Score each driver
        $scoredDrivers = [];
        foreach ($availableDrivers as $driver) {
            $score = $this->calculateDriverScore($driver, $request);
            $scoredDrivers[] = [
                'driver' => $driver,
                'score' => $score['total'],
                'score_breakdown' => $score
            ];
        }

        // Sort by score (highest first)
        usort($scoredDrivers, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $scoredDrivers[0] ?? null;
    }

    /**
     * Get all available drivers for a request
     */
    private function getAvailableDrivers($request) {
        $latitude = $request['location_latitude'];
        $longitude = $request['location_longitude'];
        
        // If no coordinates, get all available drivers
        if (!$latitude || !$longitude) {
            return $this->driverModel->getAvailable();
        }

        // Get drivers within reasonable distance (50km default)
        $maxDistance = 50;
        return $this->driverModel->getAvailable($latitude, $longitude, $maxDistance);
    }

    /**
     * Calculate comprehensive score for a driver
     */
    private function calculateDriverScore($driver, $request) {
        $scores = [
            'proximity' => $this->calculateProximityScore($driver, $request),
            'workload' => $this->calculateWorkloadScore($driver),
            'rating' => $this->calculateRatingScore($driver),
            'availability' => $this->calculateAvailabilityScore($driver)
        ];

        // Calculate weighted total
        $total = 0;
        foreach ($scores as $key => $score) {
            $total += $score * $this->weights[$key];
        }

        $scores['total'] = round($total, 2);
        return $scores;
    }

    /**
     * Calculate proximity score (0-100)
     * Closer drivers get higher scores
     */
    private function calculateProximityScore($driver, $request) {
        // If distance is already calculated (from getAvailable query)
        if (isset($driver['distance'])) {
            $distance = $driver['distance'];
        } else {
            // Calculate distance manually
            $distance = $this->calculateDistance(
                $request['location_latitude'],
                $request['location_longitude'],
                $driver['current_latitude'],
                $driver['current_longitude']
            );
        }

        // No location data
        if ($distance === null) {
            return 50; // Neutral score
        }

        // Score decreases with distance
        // 0km = 100 points, 50km = 0 points
        $maxDistance = 50;
        $score = max(0, 100 - ($distance / $maxDistance * 100));
        
        return round($score, 2);
    }

    /**
     * Calculate workload score (0-100)
     * Drivers with fewer active jobs get higher scores
     */
    private function calculateWorkloadScore($driver) {
        $activeRequests = $driver['active_requests'] ?? 0;
        
        // Maximum concurrent jobs before score hits zero
        $maxJobs = 5;
        
        // Score decreases with more active jobs
        $score = max(0, 100 - ($activeRequests / $maxJobs * 100));
        
        return round($score, 2);
    }

    /**
     * Calculate rating score (0-100)
     * Based on driver's performance rating
     */
    private function calculateRatingScore($driver) {
        $rating = floatval($driver['rating'] ?? 0);
        
        // Convert 0-5 rating to 0-100 score
        $score = ($rating / 5) * 100;
        
        return round($score, 2);
    }

    /**
     * Calculate availability score (0-100)
     * Based on driver status and recent activity
     */
    private function calculateAvailabilityScore($driver) {
        $status = $driver['status'];
        
        // Base score on status
        $statusScores = [
            'available' => 100,
            'busy' => 30,
            'on_break' => 20,
            'offline' => 0
        ];
        
        $score = $statusScores[$status] ?? 50;
        
        // Reduce score if location hasn't been updated recently
        if ($driver['last_location_update']) {
            $lastUpdate = strtotime($driver['last_location_update']);
            $hoursSinceUpdate = (time() - $lastUpdate) / 3600;
            
            // Reduce score if location is stale (older than 1 hour)
            if ($hoursSinceUpdate > 1) {
                $stalePenalty = min(30, $hoursSinceUpdate * 5);
                $score = max(0, $score - $stalePenalty);
            }
        }
        
        return round($score, 2);
    }

    /**
     * Calculate distance between two points using Haversine formula
     * Returns distance in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            return null;
        }

        $earthRadius = 6371; // kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * Dispatch a request to a driver
     */
    public function dispatch($requestId, $driverId, $automated = true) {
        // Assign driver to request
        $result = $this->requestModel->assignDriver($requestId, $driverId);
        
        if ($result) {
            // Log dispatch action
            $this->logDispatch($requestId, $driverId, $automated);
        }
        
        return $result;
    }

    /**
     * Log dispatch action for history tracking
     */
    private function logDispatch($requestId, $driverId, $automated) {
        $this->db->insert(
            "INSERT INTO dispatch_history 
             (request_id, driver_id, dispatch_method, dispatched_at) 
             VALUES (?, ?, ?, NOW())",
            [$requestId, $driverId, $automated ? 'automated' : 'manual']
        );
    }

    /**
     * Set custom weights for scoring factors
     */
    public function setWeights($weights) {
        $this->weights = array_merge($this->weights, $weights);
    }

    /**
     * Get current weights
     */
    public function getWeights() {
        return $this->weights;
    }
}
?>
