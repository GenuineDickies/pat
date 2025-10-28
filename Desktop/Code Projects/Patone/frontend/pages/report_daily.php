<!-- Daily Report View -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Daily Operations Report</h2>
                    <p class="text-muted">Report for <?php echo date('F d, Y', strtotime($date)); ?></p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>reports" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Reports
                    </a>
                    <a href="<?php echo SITE_URL; ?>reports/export?type=daily&date=<?php echo $date; ?>" class="btn btn-success">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Requests</h6>
                    <h2><?php echo $stats['total'] ?? 0; ?></h2>
                    <small>All service requests</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Completed</h6>
                    <h2><?php echo $stats['completed'] ?? 0; ?></h2>
                    <small><?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>% completion rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Revenue</h6>
                    <h2>$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h2>
                    <small>From completed services</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Avg Completion Time</h6>
                    <h2><?php echo round($stats['avg_completion_time'] ?? 0); ?> min</h2>
                    <small>Time to complete service</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Breakdown Chart -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Status Breakdown</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service Requests by Priority</h5>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Requests Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service Requests Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Time</th>
                                    <th>Customer</th>
                                    <th>Service Type</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($requests)): ?>
                                    <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>requests/<?php echo $request['id']; ?>">
                                                #<?php echo $request['id']; ?>
                                            </a>
                                        </td>
                                        <td><?php echo date('H:i', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($request['customer_first_name'] . ' ' . $request['customer_last_name']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['service_type_name']); ?></td>
                                        <td>
                                            <?php 
                                            if ($request['driver_first_name']) {
                                                echo htmlspecialchars($request['driver_first_name'] . ' ' . $request['driver_last_name']);
                                            } else {
                                                echo '<span class="text-muted">Unassigned</span>';
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
                                            $color = $statusColors[$request['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityColors = [
                                                'emergency' => 'danger',
                                                'high' => 'warning',
                                                'normal' => 'info',
                                                'low' => 'secondary'
                                            ];
                                            $pColor = $priorityColors[$request['priority']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $pColor; ?>">
                                                <?php echo ucfirst($request['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($request['final_cost']) {
                                                echo '$' . number_format($request['final_cost'], 2);
                                            } else {
                                                echo '<span class="text-muted">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No service requests found for this date.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Status Breakdown Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Assigned', 'In Progress', 'Completed', 'Cancelled'],
        datasets: [{
            data: [
                <?php echo $stats['pending'] ?? 0; ?>,
                <?php echo $stats['assigned'] ?? 0; ?>,
                <?php echo $stats['in_progress'] ?? 0; ?>,
                <?php echo $stats['completed'] ?? 0; ?>,
                <?php echo $stats['cancelled'] ?? 0; ?>
            ],
            backgroundColor: [
                '#ffc107',
                '#17a2b8',
                '#007bff',
                '#28a745',
                '#dc3545'
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

// Priority Chart
const priorityCtx = document.getElementById('priorityChart').getContext('2d');
<?php
// Count requests by priority
$priorityCounts = ['emergency' => 0, 'high' => 0, 'normal' => 0, 'low' => 0];
foreach ($requests as $req) {
    if (isset($priorityCounts[$req['priority']])) {
        $priorityCounts[$req['priority']]++;
    }
}
?>
new Chart(priorityCtx, {
    type: 'bar',
    data: {
        labels: ['Emergency', 'High', 'Normal', 'Low'],
        datasets: [{
            label: 'Number of Requests',
            data: [
                <?php echo $priorityCounts['emergency']; ?>,
                <?php echo $priorityCounts['high']; ?>,
                <?php echo $priorityCounts['normal']; ?>,
                <?php echo $priorityCounts['low']; ?>
            ],
            backgroundColor: [
                '#dc3545',
                '#ffc107',
                '#17a2b8',
                '#6c757d'
            ]
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
