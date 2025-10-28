<!-- Driver Performance Report View -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Driver Performance Report</h2>
                    <p class="text-muted">Analyze driver performance metrics and efficiency</p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>reports" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Driver Selection -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Select Driver and Time Period</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo SITE_URL; ?>reports/driverPerformance" class="row g-3">
                        <div class="col-md-6">
                            <label for="driver_id" class="form-label">Driver</label>
                            <select name="driver_id" id="driver_id" class="form-select" required>
                                <option value="">Select a driver...</option>
                                <?php foreach ($drivers as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo ($driverId == $d['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="days" class="form-label">Time Period (Days)</label>
                            <select name="days" id="days" class="form-select">
                                <option value="7" <?php echo ($days == 7) ? 'selected' : ''; ?>>Last 7 days</option>
                                <option value="30" <?php echo ($days == 30) ? 'selected' : ''; ?>>Last 30 days</option>
                                <option value="60" <?php echo ($days == 60) ? 'selected' : ''; ?>>Last 60 days</option>
                                <option value="90" <?php echo ($days == 90) ? 'selected' : ''; ?>>Last 90 days</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($driver && $stats): ?>
    <!-- Driver Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> 
                        <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($driver['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($driver['phone']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php echo $driver['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($driver['status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>License Number:</strong> <?php echo htmlspecialchars($driver['license_number'] ?? 'N/A'); ?></p>
                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($driver['vehicle_info'] ?? 'N/A'); ?></p>
                            <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($driver['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Services</h6>
                    <h2><?php echo $stats['total_services'] ?? 0; ?></h2>
                    <small>Services completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Revenue Generated</h6>
                    <h2>$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h2>
                    <small>Total earnings</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Average Rating</h6>
                    <h2><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?> <i class="bi bi-star-fill"></i></h2>
                    <small>Out of 5.0</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Completion Rate</h6>
                    <h2><?php echo round($stats['completion_rate'] ?? 0); ?>%</h2>
                    <small>Successfully completed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="serviceStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Detailed Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Value</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Total Services Completed</strong></td>
                                    <td><?php echo $stats['completed_services'] ?? 0; ?></td>
                                    <td>
                                        <?php 
                                        $completed = $stats['completed_services'] ?? 0;
                                        if ($completed >= 50) {
                                            echo '<span class="badge bg-success">Excellent</span>';
                                        } elseif ($completed >= 20) {
                                            echo '<span class="badge bg-info">Good</span>';
                                        } else {
                                            echo '<span class="badge bg-warning">Fair</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Average Service Time</strong></td>
                                    <td><?php echo round($stats['avg_service_time'] ?? 0); ?> minutes</td>
                                    <td>
                                        <?php 
                                        $avgTime = $stats['avg_service_time'] ?? 0;
                                        if ($avgTime > 0 && $avgTime <= 45) {
                                            echo '<span class="badge bg-success">Fast</span>';
                                        } elseif ($avgTime <= 60) {
                                            echo '<span class="badge bg-info">Average</span>';
                                        } else {
                                            echo '<span class="badge bg-warning">Needs Improvement</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Customer Satisfaction</strong></td>
                                    <td><?php echo number_format($stats['avg_rating'] ?? 0, 2); ?> / 5.0</td>
                                    <td>
                                        <?php 
                                        $rating = $stats['avg_rating'] ?? 0;
                                        if ($rating >= 4.5) {
                                            echo '<span class="badge bg-success">Outstanding</span>';
                                        } elseif ($rating >= 4.0) {
                                            echo '<span class="badge bg-info">Very Good</span>';
                                        } elseif ($rating >= 3.5) {
                                            echo '<span class="badge bg-warning">Good</span>';
                                        } else {
                                            echo '<span class="badge bg-danger">Needs Improvement</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Average Revenue per Service</strong></td>
                                    <td>$<?php echo number_format($stats['avg_revenue_per_service'] ?? 0, 2); ?></td>
                                    <td>
                                        <?php 
                                        $avgRev = $stats['avg_revenue_per_service'] ?? 0;
                                        if ($avgRev >= 100) {
                                            echo '<span class="badge bg-success">High Value</span>';
                                        } elseif ($avgRev >= 60) {
                                            echo '<span class="badge bg-info">Average</span>';
                                        } else {
                                            echo '<span class="badge bg-warning">Low Value</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Response Time</strong></td>
                                    <td><?php echo round($stats['avg_response_time'] ?? 0); ?> minutes</td>
                                    <td>
                                        <?php 
                                        $respTime = $stats['avg_response_time'] ?? 0;
                                        if ($respTime > 0 && $respTime <= 15) {
                                            echo '<span class="badge bg-success">Excellent</span>';
                                        } elseif ($respTime <= 30) {
                                            echo '<span class="badge bg-info">Good</span>';
                                        } else {
                                            echo '<span class="badge bg-warning">Needs Improvement</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($driverId > 0): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> No performance data available for the selected driver and time period.
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Please select a driver and time period to view performance metrics.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($driver && $stats): ?>
<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Service Status Chart
const statusCtx = document.getElementById('serviceStatusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'Cancelled', 'In Progress'],
        datasets: [{
            data: [
                <?php echo $stats['completed_services'] ?? 0; ?>,
                <?php echo $stats['cancelled_services'] ?? 0; ?>,
                <?php echo $stats['in_progress_services'] ?? 0; ?>
            ],
            backgroundColor: ['#28a745', '#dc3545', '#ffc107']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Performance Metrics Chart
const perfCtx = document.getElementById('performanceChart').getContext('2d');
new Chart(perfCtx, {
    type: 'radar',
    data: {
        labels: ['Response Time', 'Service Speed', 'Customer Rating', 'Completion Rate', 'Revenue'],
        datasets: [{
            label: 'Performance Score',
            data: [
                <?php 
                // Normalize metrics to 0-100 scale
                $respTimeScore = min(100, max(0, 100 - (($stats['avg_response_time'] ?? 30) / 60 * 100)));
                $serviceTimeScore = min(100, max(0, 100 - (($stats['avg_service_time'] ?? 60) / 120 * 100)));
                $ratingScore = (($stats['avg_rating'] ?? 0) / 5) * 100;
                $completionScore = $stats['completion_rate'] ?? 0;
                $revenueScore = min(100, (($stats['total_revenue'] ?? 0) / 1000) * 100);
                
                echo round($respTimeScore) . ',';
                echo round($serviceTimeScore) . ',';
                echo round($ratingScore) . ',';
                echo round($completionScore) . ',';
                echo round($revenueScore);
                ?>
            ],
            backgroundColor: 'rgba(0, 123, 255, 0.2)',
            borderColor: '#007bff',
            pointBackgroundColor: '#007bff',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#007bff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            r: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    stepSize: 20
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
<?php endif; ?>
