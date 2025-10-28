<!-- Revenue & Profitability Report View -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Revenue & Profitability Report</h2>
                    <p class="text-muted">
                        Report period: <?php echo date('M d, Y', strtotime($dateFrom)); ?> to <?php echo date('M d, Y', strtotime($dateTo)); ?>
                    </p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>reports" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Reports
                    </a>
                    <a href="<?php echo SITE_URL; ?>reports/export?type=revenue&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="btn btn-success">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Selector -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?php echo SITE_URL; ?>reports/revenueReport" class="row g-3">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo $dateFrom; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $dateTo; ?>" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Update Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Revenue</h6>
                    <h2>$<?php echo number_format($revenueStats['total_revenue'] ?? 0, 2); ?></h2>
                    <small><?php echo $revenueStats['completed_services'] ?? 0; ?> completed services</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Average Revenue per Service</h6>
                    <h2>$<?php echo number_format($revenueStats['avg_revenue_per_service'] ?? 0, 2); ?></h2>
                    <small>Per completed service</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Services</h6>
                    <h2><?php echo $revenueStats['total_services'] ?? 0; ?></h2>
                    <small>All service requests</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Range Stats -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Highest Service Cost</h6>
                    <h3 class="text-success">$<?php echo number_format($revenueStats['max_service_cost'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Lowest Service Cost</h6>
                    <h3 class="text-info">$<?php echo number_format($revenueStats['min_service_cost'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Revenue Trend -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Revenue Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyRevenueChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Service Type -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Revenue by Service Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="serviceTypeRevenueChart" height="250"></canvas>
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
                                    <th>Requests</th>
                                    <th>Completed</th>
                                    <th>Revenue</th>
                                    <th>Avg Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($revenueByType)): ?>
                                    <?php foreach ($revenueByType as $type): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($type['service_type']); ?></td>
                                        <td><?php echo $type['total_requests']; ?></td>
                                        <td><?php echo $type['completed']; ?></td>
                                        <td class="text-success"><strong>$<?php echo number_format($type['revenue'] ?? 0, 2); ?></strong></td>
                                        <td>$<?php echo number_format($type['avg_cost'] ?? 0, 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Revenue Generating Drivers -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Revenue Generating Drivers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Driver</th>
                                    <th>Services Completed</th>
                                    <th>Total Revenue</th>
                                    <th>Avg Revenue per Service</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($revenueByDriver)): ?>
                                    <?php $rank = 1; ?>
                                    <?php foreach ($revenueByDriver as $driver): ?>
                                    <tr>
                                        <td>
                                            <?php if ($rank == 1): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill"></i> #<?php echo $rank; ?></span>
                                            <?php elseif ($rank <= 3): ?>
                                                <span class="badge bg-secondary">#<?php echo $rank; ?></span>
                                            <?php else: ?>
                                                #<?php echo $rank; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></td>
                                        <td><?php echo $driver['services_completed']; ?></td>
                                        <td class="text-success"><strong>$<?php echo number_format($driver['total_revenue'], 2); ?></strong></td>
                                        <td>$<?php echo number_format($driver['avg_revenue_per_service'], 2); ?></td>
                                        <td>
                                            <?php
                                            $revenue = $driver['total_revenue'];
                                            if ($revenue >= 1000) {
                                                echo '<span class="badge bg-success">Excellent</span>';
                                            } elseif ($revenue >= 500) {
                                                echo '<span class="badge bg-info">Good</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">Fair</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php $rank++; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No revenue data available</td>
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
// Daily Revenue Trend
const dailyData = <?php echo json_encode($dailyRevenue); ?>;
const dailyLabels = dailyData.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
const dailyRevenue = dailyData.map(d => parseFloat(d.revenue || 0));
const dailyRequests = dailyData.map(d => parseInt(d.requests || 0));

const dailyRevenueCtx = document.getElementById('dailyRevenueChart').getContext('2d');
new Chart(dailyRevenueCtx, {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Revenue ($)',
            data: dailyRevenue,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y'
        }, {
            label: 'Requests',
            data: dailyRequests,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(0);
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.dataset.yAxisID === 'y') {
                            label += '$' + context.parsed.y.toFixed(2);
                        } else {
                            label += context.parsed.y;
                        }
                        return label;
                    }
                }
            }
        }
    }
});

// Service Type Revenue Chart
const revenueByType = <?php echo json_encode($revenueByType); ?>;
const serviceTypes = revenueByType.map(t => t.service_type);
const serviceRevenues = revenueByType.map(t => parseFloat(t.revenue || 0));

const serviceTypeRevenueCtx = document.getElementById('serviceTypeRevenueChart').getContext('2d');
new Chart(serviceTypeRevenueCtx, {
    type: 'bar',
    data: {
        labels: serviceTypes,
        datasets: [{
            label: 'Revenue ($)',
            data: serviceRevenues,
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
        indexAxis: 'y',
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(0);
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
                        return '$' + context.parsed.x.toFixed(2);
                    }
                }
            }
        }
    }
});
</script>
