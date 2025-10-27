<!-- Reports Page -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Reports & Analytics</h2>
            <p class="text-muted">Generate and view various reports for your roadside assistance operations.</p>
        </div>
    </div>

    <div class="row">
        <!-- Daily Report Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-day"></i> Daily Report</h5>
                </div>
                <div class="card-body">
                    <p>View daily operations summary including requests, revenue, and performance metrics.</p>
                    <form method="GET" action="<?php echo SITE_URL; ?>reports/daily" class="mb-3">
                        <div class="input-group">
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Monthly Report Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-month"></i> Monthly Report</h5>
                </div>
                <div class="card-body">
                    <p>View monthly performance summary with trends and service type breakdown.</p>
                    <form method="GET" action="<?php echo SITE_URL; ?>reports/monthly" class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <select name="month" class="form-select" required>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo (date('m') == $m) ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select name="year" class="form-select" required>
                                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-2 w-100">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Driver Performance Report Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Driver Performance</h5>
                </div>
                <div class="card-body">
                    <p>Analyze individual driver performance metrics and ratings.</p>
                    <a href="<?php echo SITE_URL; ?>reports/driverPerformance" class="btn btn-info w-100">
                        View Driver Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Customer Report Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Customer Report</h5>
                </div>
                <div class="card-body">
                    <p>View customer service history and behavior analysis.</p>
                    <a href="<?php echo SITE_URL; ?>reports/customerReport" class="btn btn-warning w-100">
                        View Customer Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Statistics - Current Month</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3 class="text-primary">
                                    <?php 
                                    $db = Database::getInstance();
                                    echo $db->getValue("SELECT COUNT(*) FROM service_requests WHERE MONTH(created_at) = MONTH(CURRENT_DATE())") ?? 0; 
                                    ?>
                                </h3>
                                <p class="text-muted">Total Requests</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3 class="text-success">
                                    <?php 
                                    echo $db->getValue("SELECT COUNT(*) FROM service_requests WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE())") ?? 0; 
                                    ?>
                                </h3>
                                <p class="text-muted">Completed</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3 class="text-info">
                                    $<?php 
                                    echo number_format($db->getValue("SELECT SUM(final_cost) FROM service_requests WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE())") ?? 0, 2); 
                                    ?>
                                </h3>
                                <p class="text-muted">Revenue</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3 class="text-warning">
                                    <?php 
                                    echo number_format($db->getValue("SELECT AVG(rating) FROM service_requests WHERE rating IS NOT NULL AND MONTH(created_at) = MONTH(CURRENT_DATE())") ?? 0, 2); 
                                    ?>
                                    <i class="bi bi-star-fill"></i>
                                </h3>
                                <p class="text-muted">Avg Rating</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    padding: 20px;
    border-radius: 8px;
    background-color: #f8f9fa;
    margin-bottom: 15px;
}

.stat-card h3 {
    margin-bottom: 10px;
    font-weight: bold;
}
</style>
