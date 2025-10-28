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
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-day"></i> Daily Report</h5>
                </div>
                <div class="card-body">
                    <p>View daily operations summary including requests, revenue, and performance metrics.</p>
                    <form method="GET" action="<?php echo SITE_URL; ?>reports/daily" class="mb-3">
                        <div class="input-group">
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            <button type="submit" class="btn btn-primary">Generate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Monthly Report Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-month"></i> Monthly Report</h5>
                </div>
                <div class="card-body">
                    <p>View monthly performance summary with trends and service type breakdown.</p>
                    <form method="GET" action="<?php echo SITE_URL; ?>reports/monthly" class="mb-3">
                        <div class="row">
                            <div class="col-6">
                                <select name="month" class="form-select form-select-sm" required>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo (date('m') == $m) ? 'selected' : ''; ?>>
                                        <?php echo date('M', mktime(0, 0, 0, $m, 1)); ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <select name="year" class="form-select form-select-sm" required>
                                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-2 w-100">Generate</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Driver Performance Report Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Driver Performance</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Analyze individual driver performance metrics, ratings, and efficiency.</p>
                    <a href="<?php echo SITE_URL; ?>reports/driverPerformance" class="btn btn-info w-100">
                        View Driver Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Customer Report Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Customer Analysis</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">View customer service history, behavior patterns, and loyalty metrics.</p>
                    <a href="<?php echo SITE_URL; ?>reports/customerReport" class="btn btn-warning w-100">
                        View Customer Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Revenue Report Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Revenue & Profitability</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Analyze revenue trends, profitability, and financial performance.</p>
                    <a href="<?php echo SITE_URL; ?>reports/revenueReport" class="btn btn-success w-100">
                        View Revenue Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Demand Forecast Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Demand Forecast</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Predict service demand patterns and optimize resource allocation.</p>
                    <a href="<?php echo SITE_URL; ?>reports/demandForecast" class="btn btn-dark w-100">
                        View Forecast
                    </a>
                </div>
            </div>
        </div>

        <!-- Custom Report Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-sliders"></i> Custom Report</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Create custom reports with specific filters and parameters.</p>
                    <a href="<?php echo SITE_URL; ?>reports/customReport" class="btn btn-secondary w-100">
                        Generate Custom Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Quick Statistics - Current Month</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card">
                                <h3 class="text-primary">
                                    <?php 
                                    $db = Database::getInstance();
                                    echo $db->getValue("SELECT COUNT(*) FROM service_requests WHERE MONTH(created_at) = MONTH(CURRENT_DATE())") ?? 0; 
                                    ?>
                                </h3>
                                <p class="text-muted mb-0">Total Requests</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card">
                                <h3 class="text-success">
                                    <?php 
                                    echo $db->getValue("SELECT COUNT(*) FROM service_requests WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE())") ?? 0; 
                                    ?>
                                </h3>
                                <p class="text-muted mb-0">Completed</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card">
                                <h3 class="text-info">
                                    $<?php 
                                    echo number_format($db->getValue("SELECT SUM(final_cost) FROM service_requests WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE())") ?? 0, 2); 
                                    ?>
                                </h3>
                                <p class="text-muted mb-0">Revenue</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card">
                                <h3 class="text-warning">
                                    <?php 
                                    echo number_format($db->getValue("SELECT AVG(rating) FROM service_requests WHERE rating IS NOT NULL AND MONTH(created_at) = MONTH(CURRENT_DATE())") ?? 0, 2); 
                                    ?>
                                    <i class="bi bi-star-fill"></i>
                                </h3>
                                <p class="text-muted mb-0">Avg Rating</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Scheduling Info -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Report Scheduling</h5>
                <p class="mb-0">
                    Automated reports can be scheduled to run daily, weekly, or monthly. 
                    Contact your administrator to set up scheduled report delivery via email.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    padding: 20px;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stat-card h3 {
    margin-bottom: 10px;
    font-weight: bold;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}
</style>
