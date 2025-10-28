<!-- Custom Report Generator View -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Custom Report Generator</h2>
                    <p class="text-muted">Create custom reports with specific filters and parameters</p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>reports" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-sliders"></i> Report Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo SITE_URL; ?>reports/customReport">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_from" class="form-label">From Date *</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_to" class="form-label">To Date *</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="report_type" class="form-label">Report Type *</label>
                                <select name="report_type" id="report_type" class="form-select" required>
                                    <option value="summary">Summary Report</option>
                                    <option value="detailed">Detailed Report</option>
                                    <option value="financial">Financial Report</option>
                                    <option value="operational">Operational Report</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="group_by" class="form-label">Group By</label>
                                <select name="group_by" id="group_by" class="form-select">
                                    <option value="date">Date</option>
                                    <option value="service_type">Service Type</option>
                                    <option value="driver">Driver</option>
                                    <option value="status">Status</option>
                                </select>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Filters (Optional)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="filter_status" class="form-label">Status</label>
                                        <select name="filters[status]" id="filter_status" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="pending">Pending</option>
                                            <option value="assigned">Assigned</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="filter_service_type" class="form-label">Service Type</label>
                                        <select name="filters[service_type_id]" id="filter_service_type" class="form-select">
                                            <option value="">All Service Types</option>
                                            <?php foreach ($serviceTypes as $type): ?>
                                            <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="filter_priority" class="form-label">Priority</label>
                                        <select name="filters[priority]" id="filter_priority" class="form-select">
                                            <option value="">All Priorities</option>
                                            <option value="emergency">Emergency</option>
                                            <option value="high">High</option>
                                            <option value="normal">Normal</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Include in Report</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include[customer_info]" id="include_customer" checked>
                                            <label class="form-check-label" for="include_customer">
                                                Customer Information
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include[driver_info]" id="include_driver" checked>
                                            <label class="form-check-label" for="include_driver">
                                                Driver Information
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include[location]" id="include_location" checked>
                                            <label class="form-check-label" for="include_location">
                                                Location Details
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include[financial]" id="include_financial" checked>
                                            <label class="form-check-label" for="include_financial">
                                                Financial Data
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-play-fill"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Templates Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Quick Report Templates</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Today's Activity</h6>
                                    <p class="text-muted small">All requests from today</p>
                                    <a href="<?php echo SITE_URL; ?>reports/daily?date=<?php echo date('Y-m-d'); ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Completed Services</h6>
                                    <p class="text-muted small">This month's completed</p>
                                    <button class="btn btn-sm btn-success" onclick="fillTemplate('completed')">Generate</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="bi bi-cash-stack text-info" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Revenue Report</h6>
                                    <p class="text-muted small">This month's revenue</p>
                                    <a href="<?php echo SITE_URL; ?>reports/revenueReport" class="btn btn-sm btn-info">View</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Pending Requests</h6>
                                    <p class="text-muted small">Currently pending</p>
                                    <button class="btn btn-sm btn-warning" onclick="fillTemplate('pending')">Generate</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillTemplate(type) {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const reportType = document.getElementById('report_type');
    const filterStatus = document.getElementById('filter_status');
    
    dateFrom.value = firstDay.toISOString().split('T')[0];
    dateTo.value = today.toISOString().split('T')[0];
    
    if (type === 'completed') {
        reportType.value = 'summary';
        filterStatus.value = 'completed';
    } else if (type === 'pending') {
        reportType.value = 'detailed';
        filterStatus.value = 'pending';
    }
    
    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
