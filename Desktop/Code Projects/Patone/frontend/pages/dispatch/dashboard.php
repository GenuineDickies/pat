<?php
/**
 * Dispatch Dashboard View
 * Main interface for automated dispatch system
 */

// Ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$title = $title ?? 'Dispatch Dashboard';
$pendingRequests = $pendingRequests ?? [];
$queueStats = $queueStats ?? [];
$availableDrivers = $availableDrivers ?? [];
$recentDispatches = $recentDispatches ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Roadside Assistance</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .dispatch-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px;
        }
        
        .dispatch-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0;
            font-size: 2em;
            color: #333;
        }
        
        .stat-card p {
            margin: 5px 0 0;
            color: #666;
            font-size: 0.9em;
        }
        
        .stat-card.emergency {
            background: #fee;
            border-left: 4px solid #e74c3c;
        }
        
        .panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .panel h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .request-item {
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .request-item.emergency {
            border-left: 4px solid #e74c3c;
            background: #fff5f5;
        }
        
        .request-item.high {
            border-left: 4px solid #f39c12;
        }
        
        .request-info {
            flex: 1;
        }
        
        .request-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .driver-item {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }
        
        .driver-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.85em;
        }
        
        .driver-status.available {
            background: #d4edda;
            color: #155724;
        }
        
        .driver-status.busy {
            background: #fff3cd;
            color: #856404;
        }
        
        .priority-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .priority-badge.emergency {
            background: #e74c3c;
            color: white;
        }
        
        .priority-badge.high {
            background: #f39c12;
            color: white;
        }
        
        .priority-badge.normal {
            background: #3498db;
            color: white;
        }
        
        .priority-badge.low {
            background: #95a5a6;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .dispatch-history {
            grid-column: 1 / -1;
        }
        
        .history-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dispatch-method {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.75em;
        }
        
        .dispatch-method.automated {
            background: #d4edda;
            color: #155724;
        }
        
        .dispatch-method.manual {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <button class="btn btn-success" onclick="autoDispatchNext()">
                🚀 Auto-Dispatch Next Request
            </button>
        </header>

        <!-- Statistics Dashboard -->
        <div class="dispatch-stats">
            <div class="stat-card">
                <h3><?php echo $queueStats['pending'] ?? 0; ?></h3>
                <p>Pending in Queue</p>
            </div>
            <div class="stat-card emergency">
                <h3><?php echo $queueStats['emergency_requests'] ?? 0; ?></h3>
                <p>Emergency Requests</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($availableDrivers); ?></h3>
                <p>Available Drivers</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $queueStats['dispatched'] ?? 0; ?></h3>
                <p>Dispatched Today</p>
            </div>
        </div>

        <div class="dispatch-container">
            <!-- Pending Requests Queue -->
            <div class="panel">
                <h2>📋 Dispatch Queue</h2>
                <?php if (empty($pendingRequests)): ?>
                    <div class="empty-state">
                        <p>No pending requests in queue</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingRequests as $request): ?>
                        <div class="request-item <?php echo $request['priority']; ?>">
                            <div class="request-info">
                                <span class="priority-badge <?php echo $request['priority']; ?>">
                                    <?php echo strtoupper($request['priority']); ?>
                                </span>
                                <strong>
                                    <?php echo htmlspecialchars($request['customer_first_name'] . ' ' . $request['customer_last_name']); ?>
                                </strong>
                                <br>
                                <small><?php echo htmlspecialchars($request['location_address']); ?></small>
                                <br>
                                <small><?php echo htmlspecialchars($request['service_type_name']); ?></small>
                            </div>
                            <div class="request-actions">
                                <button class="btn btn-primary" onclick="findBestDriver(<?php echo $request['request_id']; ?>)">
                                    🔍 Find Driver
                                </button>
                                <button class="btn btn-success" onclick="manualDispatch(<?php echo $request['request_id']; ?>)">
                                    ✋ Manual Assign
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Available Drivers -->
            <div class="panel">
                <h2>👥 Available Drivers</h2>
                <?php if (empty($availableDrivers)): ?>
                    <div class="empty-state">
                        <p>No drivers currently available</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($availableDrivers as $driver): ?>
                        <div class="driver-item">
                            <div>
                                <strong><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></strong>
                                <br>
                                <small>
                                    Rating: <?php echo number_format($driver['rating'], 1); ?>/5.0 |
                                    Active: <?php echo $driver['active_requests'] ?? 0; ?> requests
                                </small>
                            </div>
                            <div>
                                <span class="driver-status <?php echo $driver['status']; ?>">
                                    <?php echo ucfirst($driver['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Recent Dispatch History -->
            <div class="panel dispatch-history">
                <h2>📜 Recent Dispatches</h2>
                <?php if (empty($recentDispatches)): ?>
                    <div class="empty-state">
                        <p>No recent dispatch activity</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentDispatches as $dispatch): ?>
                        <div class="history-item">
                            <div>
                                <span class="dispatch-method <?php echo $dispatch['dispatch_method']; ?>">
                                    <?php echo strtoupper($dispatch['dispatch_method']); ?>
                                </span>
                                <strong>
                                    <?php echo htmlspecialchars($dispatch['customer_first_name'] . ' ' . $dispatch['customer_last_name']); ?>
                                </strong>
                                → <?php echo htmlspecialchars($dispatch['driver_first_name'] . ' ' . $dispatch['driver_last_name']); ?>
                            </div>
                            <small><?php echo date('M d, H:i', strtotime($dispatch['dispatched_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-dispatch next request
        function autoDispatchNext() {
            if (!confirm('Auto-dispatch the next request in queue?')) return;
            
            fetch('/dispatch/autoDispatch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Success: ' + data.message + '\nDriver: ' + data.data.driver_name + '\nScore: ' + data.data.score);
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || data.error));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        // Find best driver for a request
        function findBestDriver(requestId) {
            fetch('/dispatch/findDriver/' + requestId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const result = data.data;
                    const breakdown = result.score_breakdown;
                    const message = `Best Driver: ${result.driver_name}\n\n` +
                                  `Overall Score: ${result.score}/100\n\n` +
                                  `Score Breakdown:\n` +
                                  `- Proximity: ${breakdown.proximity}\n` +
                                  `- Workload: ${breakdown.workload}\n` +
                                  `- Rating: ${breakdown.rating}\n` +
                                  `- Availability: ${breakdown.availability}\n\n` +
                                  `Dispatch this driver?`;
                    
                    if (confirm(message)) {
                        dispatchToDriver(requestId, result.driver_id);
                    }
                } else {
                    alert('Error: ' + (data.message || data.error));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        // Manual dispatch
        function manualDispatch(requestId) {
            const driverId = prompt('Enter Driver ID for manual assignment:');
            if (!driverId) return;
            
            dispatchToDriver(requestId, driverId);
        }

        // Dispatch request to driver
        function dispatchToDriver(requestId, driverId) {
            const formData = new FormData();
            formData.append('request_id', requestId);
            formData.append('driver_id', driverId);
            
            fetch('/dispatch/manualDispatch', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Success: ' + data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
