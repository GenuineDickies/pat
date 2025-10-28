<!-- Custom Report Results View -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Custom Report Results</h2>
                    <p class="text-muted">
                        <?php echo ucfirst(str_replace('_', ' ', $reportType)); ?> - 
                        <?php echo date('M d, Y', strtotime($dateFrom)); ?> to <?php echo date('M d, Y', strtotime($dateTo)); ?>
                    </p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>reports/customReport" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> New Report
                    </a>
                    <button onclick="window.print()" class="btn btn-info">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <a href="<?php echo SITE_URL; ?>reports/export?type=custom&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="btn btn-success">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <?php
    $totalRecords = count($reportData);
    $completedRecords = count(array_filter($reportData, fn($r) => $r['status'] == 'completed'));
    $totalRevenue = array_sum(array_map(fn($r) => $r['final_cost'] ?? 0, array_filter($reportData, fn($r) => $r['status'] == 'completed')));
    $avgRevenue = $completedRecords > 0 ? $totalRevenue / $completedRecords : 0;
    ?>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Records</h6>
                    <h2 class="mb-0"><?php echo $totalRecords; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Completed</h6>
                    <h2 class="mb-0 text-success"><?php echo $completedRecords; ?></h2>
                    <small><?php echo $totalRecords > 0 ? round(($completedRecords / $totalRecords) * 100) : 0; ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Revenue</h6>
                    <h2 class="mb-0 text-info">$<?php echo number_format($totalRevenue, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Avg Revenue</h6>
                    <h2 class="mb-0 text-primary">$<?php echo number_format($avgRevenue, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Report Data (<?php echo $totalRecords; ?> records)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="reportTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Service Type</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Location</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData)): ?>
                                    <?php foreach ($reportData as $record): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>requests/<?php echo $record['id']; ?>">
                                                #<?php echo $record['id']; ?>
                                            </a>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($record['created_at'])); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($record['customer_first_name'] . ' ' . $record['customer_last_name']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($record['service_type_name']); ?></td>
                                        <td>
                                            <?php 
                                            if ($record['driver_first_name']) {
                                                echo htmlspecialchars($record['driver_first_name'] . ' ' . $record['driver_last_name']);
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
                                            $color = $statusColors[$record['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?>
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
                                            $pColor = $priorityColors[$record['priority']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $pColor; ?>">
                                                <?php echo ucfirst($record['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            echo htmlspecialchars($record['location_city'] . ', ' . $record['location_state']); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($record['final_cost']) {
                                                echo '$' . number_format($record['final_cost'], 2);
                                            } else {
                                                echo '<span class="text-muted">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No records found matching your criteria.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Footer -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center text-muted">
                    <small>
                        Report generated on <?php echo date('F d, Y \a\t g:i A'); ?> by Patone Reporting System
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header h5 i {
        display: none;
    }
    .card {
        border: 1px solid #dee2e6 !important;
        page-break-inside: avoid;
    }
    body {
        background: white;
    }
}
</style>

<script>
// Optional: Add DataTables for better table functionality
// Include DataTables if needed for sorting, filtering, pagination
</script>
