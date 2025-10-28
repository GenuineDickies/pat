<!-- Customer Report View -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Customer Analysis Report</h2>
                    <p class="text-muted">View customer service history and behavior analysis</p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>reports" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Selection -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Select Customer</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo SITE_URL; ?>reports/customerReport" class="row g-3">
                        <div class="col-md-10">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-select" required>
                                <option value="">Select a customer...</option>
                                <?php foreach ($customers as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($customerId == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name'] . ' - ' . $c['email']); ?>
                                </option>
                                <?php endforeach; ?>
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

    <?php if ($customer): ?>
    <!-- Customer Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle"></i> 
                        <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                        <?php if ($customer['is_vip']): ?>
                        <span class="badge bg-warning text-dark ms-2">
                            <i class="bi bi-star-fill"></i> VIP
                        </span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                            <p><strong>Address:</strong> 
                                <?php 
                                $address = trim(($customer['address'] ?? '') . ' ' . ($customer['city'] ?? '') . ', ' . ($customer['state'] ?? ''));
                                echo $address ? htmlspecialchars($address) : 'N/A';
                                ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($customer['created_at'])); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php echo $customer['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($customer['status'] ?? 'active'); ?>
                                </span>
                            </p>
                            <p><strong>VIP Status:</strong> <?php echo $customer['is_vip'] ? 'Yes' : 'No'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Statistics -->
    <?php
    $totalServices = count($serviceHistory);
    $completedServices = count(array_filter($serviceHistory, fn($s) => $s['status'] == 'completed'));
    $totalSpent = array_sum(array_map(fn($s) => $s['final_cost'] ?? 0, $serviceHistory));
    $avgRating = $completedServices > 0 ? array_sum(array_map(fn($s) => $s['rating'] ?? 0, $serviceHistory)) / $completedServices : 0;
    ?>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Services</h6>
                    <h2><?php echo $totalServices; ?></h2>
                    <small>All time requests</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Completed</h6>
                    <h2><?php echo $completedServices; ?></h2>
                    <small><?php echo $totalServices > 0 ? round(($completedServices / $totalServices) * 100) : 0; ?>% completion rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Spent</h6>
                    <h2>$<?php echo number_format($totalSpent, 2); ?></h2>
                    <small>Lifetime value</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Avg Rating Given</h6>
                    <h2><?php echo number_format($avgRating, 1); ?> <i class="bi bi-star-fill"></i></h2>
                    <small>Out of 5.0</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Type Breakdown and Timeline -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service Type Usage</h5>
                </div>
                <div class="card-body">
                    <canvas id="serviceTypeChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Monthly Service Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Service History Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Service Type</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Cost</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($serviceHistory)): ?>
                                    <?php foreach ($serviceHistory as $service): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>requests/<?php echo $service['id']; ?>">
                                                #<?php echo $service['id']; ?>
                                            </a>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($service['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($service['service_type_name']); ?></td>
                                        <td>
                                            <?php 
                                            if ($service['driver_first_name']) {
                                                echo htmlspecialchars($service['driver_first_name'] . ' ' . $service['driver_last_name']);
                                            } else {
                                                echo '<span class="text-muted">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'assigned' => 'info',
                                                'in_progress' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $color = $statusColors[$service['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $service['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($service['final_cost']) {
                                                echo '$' . number_format($service['final_cost'], 2);
                                            } else {
                                                echo '<span class="text-muted">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($service['rating']): ?>
                                                <?php echo number_format($service['rating'], 1); ?> <i class="bi bi-star-fill text-warning"></i>
                                            <?php else: ?>
                                                <span class="text-muted">Not rated</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No service history available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($customerId > 0): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Customer not found or no data available.
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Please select a customer to view their service history and analysis.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($customer && !empty($serviceHistory)): ?>
<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Service Type Distribution
<?php
$serviceTypeCounts = [];
foreach ($serviceHistory as $service) {
    $type = $service['service_type_name'];
    if (!isset($serviceTypeCounts[$type])) {
        $serviceTypeCounts[$type] = 0;
    }
    $serviceTypeCounts[$type]++;
}
?>
const serviceTypeCtx = document.getElementById('serviceTypeChart').getContext('2d');
new Chart(serviceTypeCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($serviceTypeCounts)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($serviceTypeCounts)); ?>,
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#17a2b8',
                '#6c757d',
                '#fd7e14',
                '#6f42c1'
            ]
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

// Monthly Trend
<?php
$monthlyData = [];
foreach ($serviceHistory as $service) {
    $month = date('Y-m', strtotime($service['created_at']));
    if (!isset($monthlyData[$month])) {
        $monthlyData[$month] = 0;
    }
    $monthlyData[$month]++;
}
ksort($monthlyData);
$months = array_keys($monthlyData);
$counts = array_values($monthlyData);
$monthLabels = array_map(function($m) {
    return date('M Y', strtotime($m . '-01'));
}, $months);
?>
const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
new Chart(monthlyTrendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($monthLabels); ?>,
        datasets: [{
            label: 'Service Requests',
            data: <?php echo json_encode($counts); ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
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
