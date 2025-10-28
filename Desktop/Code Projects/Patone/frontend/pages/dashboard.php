<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Dashboard Overview</h4>
                <div class="btn-group">
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Active Requests</h6>
                            <h2 class="mb-0" id="activeRequests"><?php echo $stats['active_requests'] ?? '0'; ?></h2>
                            <small class="opacity-75">Currently in progress</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-truck fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Completed Today</h6>
                            <h2 class="mb-0" id="completedToday"><?php echo $stats['completed_today'] ?? '0'; ?></h2>
                            <small class="opacity-75">Services completed</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Available Drivers</h6>
                            <h2 class="mb-0" id="availableDrivers"><?php echo $stats['available_drivers'] ?? '0'; ?></h2>
                            <small class="opacity-75">Ready for dispatch</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-check fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Customers</h6>
                            <h2 class="mb-0" id="totalCustomers"><?php echo $stats['total_customers'] ?? '0'; ?></h2>
                            <small class="opacity-75">Registered customers</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-muted small">Avg Response Time</div>
                    <h4 id="avgResponseTime" class="mb-0">-</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <div class="text-muted small">Completion Rate</div>
                    <h4 id="completionRate" class="mb-0">-</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-info border-4">
                <div class="card-body">
                    <div class="text-muted small">Customer Satisfaction</div>
                    <h4 id="customerSatisfaction" class="mb-0">-</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-muted small">Peak Hours</div>
                    <h4 id="peakHours" class="mb-0">-</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <canvas id="requestsTimelineChart" height="80"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <canvas id="serviceTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <canvas id="driverPerformanceChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <canvas id="hourlyRequestsChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="row">
        <!-- Recent Requests -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Service Requests</h5>
                    <a href="<?php echo SITE_URL; ?>requests" class="btn btn-sm btn-outline-primary">
                        View All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="recentRequestsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Request #</th>
                                    <th>Customer</th>
                                    <th>Service Type</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($recentRequests) && !empty($recentRequests)): ?>
                                    <?php foreach ($recentRequests as $request): ?>
                                    <tr>
                                        <td><strong>#<?php echo $request['id']; ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                                    <?php echo strtoupper(substr($request['customer_name'], 0, 1)); ?>
                                                </div>
                                                <div class="ms-2">
                                                    <div class="fw-medium"><?php echo htmlspecialchars($request['customer_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['customer_phone']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($request['service_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars(substr($request['location'], 0, 30)); ?>...
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($request['status']); ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo formatDateTime($request['created_at']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo SITE_URL; ?>requests/<?php echo $request['id']; ?>"
                                                   class="btn btn-outline-primary btn-sm" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if (hasPermission('edit_requests')): ?>
                                                <a href="<?php echo SITE_URL; ?>requests/<?php echo $request['id']; ?>/edit"
                                                   class="btn btn-outline-secondary btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No recent requests found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>requests/add" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New Service Request
                        </a>
                        <a href="<?php echo SITE_URL; ?>customers/add" class="btn btn-success">
                            <i class="bi bi-person-plus"></i> Add Customer
                        </a>
                        <a href="<?php echo SITE_URL; ?>drivers/add" class="btn btn-info">
                            <i class="bi bi-person-badge"></i> Add Driver
                        </a>
                        <button class="btn btn-warning" onclick="generateReport()">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Driver Status -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Driver Status</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshDrivers()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div id="driverStatus">
                        <?php if (isset($driverStats) && !empty($driverStats)): ?>
                            <?php foreach ($driverStats as $driver): ?>
                            <div class="driver-item d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="driver-avatar bg-<?php echo $driver['status'] == 'available' ? 'success' : 'warning'; ?> text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                         style="width: 32px; height: 32px; font-size: 14px;">
                                        <?php echo strtoupper(substr($driver['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-medium small"><?php echo htmlspecialchars($driver['name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($driver['phone']); ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-<?php echo $driver['status'] == 'available' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($driver['status']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3 text-muted">
                                <i class="bi bi-person-dash fs-1 d-block mb-2"></i>
                                No drivers available
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Feed -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div id="recentActivity">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>assets/js/dashboard.js"></script>
<script>
// Initialize DataTable for recent requests
$(document).ready(function() {
    $('#recentRequestsTable').DataTable({
        responsive: true,
        ordering: false,
        searching: false,
        paging: false,
        info: false,
        columnDefs: [
            { targets: [6], orderable: false }
        ]
    });
});
</script>
