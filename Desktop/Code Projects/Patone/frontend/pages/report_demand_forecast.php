<!-- Service Demand Forecast View -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Service Demand Forecast</h2>
                    <p class="text-muted">Analyze historical demand patterns and predict future service needs</p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>reports" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Period Selector -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?php echo SITE_URL; ?>reports/demandForecast" class="row g-3">
                        <div class="col-md-10">
                            <label for="days" class="form-label">Analysis Period</label>
                            <select name="days" id="days" class="form-select">
                                <option value="7" <?php echo ($days == 7) ? 'selected' : ''; ?>>Last 7 days</option>
                                <option value="14" <?php echo ($days == 14) ? 'selected' : ''; ?>>Last 14 days</option>
                                <option value="30" <?php echo ($days == 30) ? 'selected' : ''; ?>>Last 30 days</option>
                                <option value="60" <?php echo ($days == 60) ? 'selected' : ''; ?>>Last 60 days</option>
                                <option value="90" <?php echo ($days == 90) ? 'selected' : ''; ?>>Last 90 days</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Update Analysis</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <?php
    $totalRequests = array_sum(array_column($demandData, 'request_count'));
    $avgDailyRequests = count($dailyDemand) > 0 ? $totalRequests / $days : 0;
    $peakHour = !empty($hourlyDemand) ? max(array_column($hourlyDemand, 'request_count')) : 0;
    $peakDay = !empty($dailyDemand) ? max(array_column($dailyDemand, 'request_count')) : 0;
    ?>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Requests</h6>
                    <h2><?php echo $totalRequests; ?></h2>
                    <small>In last <?php echo $days; ?> days</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Avg Daily Requests</h6>
                    <h2><?php echo round($avgDailyRequests, 1); ?></h2>
                    <small>Per day average</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Peak Hour Demand</h6>
                    <h2><?php echo $peakHour; ?></h2>
                    <small>Max requests in an hour</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Peak Day Demand</h6>
                    <h2><?php echo $peakDay; ?></h2>
                    <small>Max requests in a day</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Hourly Demand Pattern -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock"></i> Hourly Demand Pattern</h5>
                </div>
                <div class="card-body">
                    <canvas id="hourlyDemandChart" height="80"></canvas>
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <strong>Peak Hours:</strong>
                            <?php
                            if (!empty($hourlyDemand)) {
                                $sortedHours = $hourlyDemand;
                                usort($sortedHours, fn($a, $b) => $b['request_count'] - $a['request_count']);
                                $topHours = array_slice($sortedHours, 0, 3);
                                foreach ($topHours as $h) {
                                    $hourLabel = date('g A', strtotime($h['hour'] . ':00'));
                                    echo "<span class='badge bg-primary me-2'>{$hourLabel} ({$h['request_count']} requests)</span>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Demand Pattern -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-week"></i> Daily Demand Pattern (by Day of Week)</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyDemandChart" height="80"></canvas>
                    <div class="mt-3">
                        <div class="alert alert-success">
                            <strong>Busiest Days:</strong>
                            <?php
                            if (!empty($dailyDemand)) {
                                $sortedDays = $dailyDemand;
                                usort($sortedDays, fn($a, $b) => $b['request_count'] - $a['request_count']);
                                $topDays = array_slice($sortedDays, 0, 3);
                                foreach ($topDays as $d) {
                                    echo "<span class='badge bg-success me-2'>{$d['day_name']} ({$d['request_count']} requests)</span>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demand Insights and Recommendations -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Key Insights</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php
                        // Generate insights based on data
                        if (!empty($hourlyDemand)) {
                            $maxHourData = array_reduce($hourlyDemand, fn($carry, $item) => ($item['request_count'] > ($carry['request_count'] ?? 0)) ? $item : $carry, []);
                            $peakHourTime = date('g A', strtotime($maxHourData['hour'] . ':00'));
                            echo "<li class='list-group-item'><i class='bi bi-check-circle text-success'></i> Peak demand occurs at {$peakHourTime} with {$maxHourData['request_count']} requests</li>";
                        }

                        if (!empty($dailyDemand)) {
                            $maxDayData = array_reduce($dailyDemand, fn($carry, $item) => ($item['request_count'] > ($carry['request_count'] ?? 0)) ? $item : $carry, []);
                            echo "<li class='list-group-item'><i class='bi bi-check-circle text-success'></i> {$maxDayData['day_name']} is the busiest day with {$maxDayData['request_count']} total requests</li>";
                            
                            $minDayData = array_reduce($dailyDemand, fn($carry, $item) => ($item['request_count'] < ($carry['request_count'] ?? PHP_INT_MAX)) ? $item : $carry, []);
                            echo "<li class='list-group-item'><i class='bi bi-info-circle text-info'></i> {$minDayData['day_name']} is the slowest day with {$minDayData['request_count']} total requests</li>";
                        }

                        if ($avgDailyRequests > 0) {
                            $projectedMonthly = $avgDailyRequests * 30;
                            echo "<li class='list-group-item'><i class='bi bi-graph-up text-primary'></i> Projected monthly demand: " . round($projectedMonthly) . " requests</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-megaphone"></i> Recommendations</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php
                        // Generate recommendations
                        if (!empty($hourlyDemand)) {
                            $sortedHours = $hourlyDemand;
                            usort($sortedHours, fn($a, $b) => $b['request_count'] - $a['request_count']);
                            $topHour = $sortedHours[0];
                            $peakTime = date('g A', strtotime($topHour['hour'] . ':00'));
                            echo "<li class='list-group-item'><i class='bi bi-person-plus text-warning'></i> Schedule more drivers during peak hours ({$peakTime})</li>";
                        }

                        if (!empty($dailyDemand)) {
                            $sortedDays = $dailyDemand;
                            usort($sortedDays, fn($a, $b) => $b['request_count'] - $a['request_count']);
                            $busyDay = $sortedDays[0]['day_name'];
                            echo "<li class='list-group-item'><i class='bi bi-calendar-check text-success'></i> Ensure full driver coverage on {$busyDay}s</li>";
                            
                            $slowDay = end($sortedDays)['day_name'];
                            echo "<li class='list-group-item'><i class='bi bi-calendar-x text-info'></i> Consider reduced staffing on {$slowDay}s</li>";
                        }

                        if ($avgDailyRequests > 15) {
                            echo "<li class='list-group-item'><i class='bi bi-exclamation-triangle text-danger'></i> High demand detected - consider hiring additional drivers</li>";
                        } elseif ($avgDailyRequests < 5) {
                            echo "<li class='list-group-item'><i class='bi bi-info-circle text-primary'></i> Low demand period - focus on marketing and customer retention</li>";
                        }
                        ?>
                        <li class='list-group-item'><i class='bi bi-graph-up-arrow text-success'></i> Monitor trends regularly to optimize resource allocation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Historical Demand Trend -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Historical Demand Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="historicalTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Hourly Demand Chart
const hourlyData = <?php echo json_encode($hourlyDemand); ?>;
const hours = hourlyData.map(h => {
    const hour = parseInt(h.hour);
    return hour === 0 ? '12 AM' : hour < 12 ? hour + ' AM' : hour === 12 ? '12 PM' : (hour - 12) + ' PM';
});
const hourlyCounts = hourlyData.map(h => parseInt(h.request_count));

const hourlyCtx = document.getElementById('hourlyDemandChart').getContext('2d');
new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: hours,
        datasets: [{
            label: 'Requests',
            data: hourlyCounts,
            backgroundColor: '#007bff',
            borderColor: '#0056b3',
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

// Daily Demand Chart
const dailyData = <?php echo json_encode($dailyDemand); ?>;
const days = dailyData.map(d => d.day_name);
const dailyCounts = dailyData.map(d => parseInt(d.request_count));

const dailyCtx = document.getElementById('dailyDemandChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'bar',
    data: {
        labels: days,
        datasets: [{
            label: 'Requests',
            data: dailyCounts,
            backgroundColor: '#28a745',
            borderColor: '#1e7e34',
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

// Historical Trend Chart
<?php
// Aggregate demand data by date
$trendData = [];
foreach ($demandData as $entry) {
    $date = $entry['date'];
    if (!isset($trendData[$date])) {
        $trendData[$date] = 0;
    }
    $trendData[$date] += $entry['request_count'];
}
ksort($trendData);
?>
const trendDates = <?php echo json_encode(array_keys($trendData)); ?>;
const trendCounts = <?php echo json_encode(array_values($trendData)); ?>;
const trendLabels = trendDates.map(d => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));

const trendCtx = document.getElementById('historicalTrendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Daily Requests',
            data: trendCounts,
            borderColor: '#17a2b8',
            backgroundColor: 'rgba(23, 162, 184, 0.1)',
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
