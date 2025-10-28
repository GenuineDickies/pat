<!-- Monthly Report View -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Monthly Performance Report</h2>
                    <p class="text-muted">Report for <?php echo date('F Y', strtotime(sprintf('%04d-%02d-01', $year, $month))); ?></p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>reports" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Reports
                    </a>
                    <a href="<?php echo SITE_URL; ?>reports/export?type=monthly&year=<?php echo $year; ?>&month=<?php echo $month; ?>" class="btn btn-success">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Key Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Requests</h6>
                    <h2><?php echo $stats['total'] ?? 0; ?></h2>
                    <small>For the entire month</small>
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
                    <h6 class="card-title">Total Revenue</h6>
                    <h2>$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h2>
                    <small>From completed services</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Avg Daily Requests</h6>
                    <h2><?php echo $stats['total'] > 0 ? round($stats['total'] / count($dailyStats)) : 0; ?></h2>
                    <small>Requests per day</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Trend Chart -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Request Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Revenue Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Type Breakdown -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service Type Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="serviceTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service Type Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Service Type</th>
                                    <th>Total</th>
                                    <th>Completed</th>
                                    <th>Avg Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($serviceTypeStats)): ?>
                                    <?php foreach ($serviceTypeStats as $service): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                                        <td><?php echo $service['total']; ?></td>
                                        <td><?php echo $service['completed']; ?></td>
                                        <td>$<?php echo number_format($service['avg_cost'] ?? 0, 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Statistics Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Total Requests</th>
                                    <th>Completed</th>
                                    <th>Revenue</th>
                                    <th>Completion Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($dailyStats)): ?>
                                    <?php foreach ($dailyStats as $day): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                        <td><?php echo $day['total']; ?></td>
                                        <td><?php echo $day['completed']; ?></td>
                                        <td>$<?php echo number_format($day['revenue'] ?? 0, 2); ?></td>
                                        <td>
                                            <?php 
                                            $rate = $day['total'] > 0 ? round(($day['completed'] / $day['total']) * 100) : 0;
                                            $colorClass = $rate >= 80 ? 'success' : ($rate >= 60 ? 'warning' : 'danger');
                                            ?>
                                            <span class="badge bg-<?php echo $colorClass; ?>"><?php echo $rate; ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No data available for this month.</td>
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
// Prepare daily data
const dailyData = <?php echo json_encode($dailyStats); ?>;
const dailyLabels = dailyData.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
const dailyTotals = dailyData.map(d => d.total);
const dailyCompleted = dailyData.map(d => d.completed);
const dailyRevenue = dailyData.map(d => parseFloat(d.revenue || 0));

// Daily Trend Chart
const dailyTrendCtx = document.getElementById('dailyTrendChart').getContext('2d');
new Chart(dailyTrendCtx, {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Total Requests',
            data: dailyTotals,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Completed',
            data: dailyCompleted,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
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
                position: 'top'
            }
        }
    }
});

// Revenue Trend Chart
const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
new Chart(revenueTrendCtx, {
    type: 'bar',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Daily Revenue ($)',
            data: dailyRevenue,
            backgroundColor: '#17a2b8',
            borderColor: '#117a8b',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(2);
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '$' + context.parsed.y.toFixed(2);
                    }
                }
            }
        }
    }
});

// Service Type Chart
const serviceTypeCtx = document.getElementById('serviceTypeChart').getContext('2d');
const serviceTypes = <?php echo json_encode(array_column($serviceTypeStats, 'name')); ?>;
const serviceCounts = <?php echo json_encode(array_column($serviceTypeStats, 'total')); ?>;

new Chart(serviceTypeCtx, {
    type: 'pie',
    data: {
        labels: serviceTypes,
        datasets: [{
            data: serviceCounts,
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
</script>
