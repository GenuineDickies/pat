<!-- Driver Details Page -->
<div class="container-fluid">
    <!-- Driver Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></h4>
                    <p class="text-muted mb-0">Driver ID: #<?php echo $driver['id']; ?></p>
                </div>
                <div class="btn-group">
                    <a href="<?php echo SITE_URL; ?>drivers" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Drivers
                    </a>
                    <?php if (hasPermission('manage_drivers')): ?>
                    <a href="<?php echo SITE_URL; ?>drivers/edit/<?php echo $driver['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Driver
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Driver Info Cards -->
    <div class="row mb-4">
        <!-- Personal Information -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            <span class="badge bg-<?php 
                                echo $driver['status'] == 'available' ? 'success' : 
                                    ($driver['status'] == 'busy' ? 'warning' : 
                                    ($driver['status'] == 'on_break' ? 'info' : 'secondary')); 
                            ?> fs-6">
                                <?php echo ucfirst(str_replace('_', ' ', $driver['status'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Email</label>
                        <div><?php echo htmlspecialchars($driver['email']); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Phone</label>
                        <div><?php echo formatPhone($driver['phone']); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Rating</label>
                        <div>
                            <i class="bi bi-star-fill text-warning"></i>
                            <?php echo number_format($driver['rating'], 2); ?> / 5.00
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Vehicle Information</label>
                        <div><?php echo htmlspecialchars($driver['vehicle_info'] ?? 'Not specified'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Information -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">License Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">License Number</label>
                        <div><?php echo htmlspecialchars($driver['license_number']); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">License State</label>
                        <div><?php echo htmlspecialchars($driver['license_state']); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">License Expiry</label>
                        <div>
                            <?php 
                            $expiry = new DateTime($driver['license_expiry']);
                            $now = new DateTime();
                            $isExpired = $expiry < $now;
                            $daysUntilExpiry = $now->diff($expiry)->days;
                            ?>
                            <?php echo $expiry->format('M d, Y'); ?>
                            <?php if ($isExpired): ?>
                                <span class="badge bg-danger ms-2">Expired</span>
                            <?php elseif ($daysUntilExpiry < 30): ?>
                                <span class="badge bg-warning ms-2">Expires Soon</span>
                            <?php else: ?>
                                <span class="badge bg-success ms-2">Valid</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (isset($driver['notes']) && !empty($driver['notes'])): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Notes</label>
                        <div><?php echo nl2br(htmlspecialchars($driver['notes'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Location Tracking</h5>
                </div>
                <div class="card-body">
                    <?php if ($driver['current_latitude'] && $driver['current_longitude']): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Current Location</label>
                        <div>
                            Lat: <?php echo number_format($driver['current_latitude'], 6); ?><br>
                            Lng: <?php echo number_format($driver['current_longitude'], 6); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Last Update</label>
                        <div>
                            <?php 
                            if ($driver['last_location_update']) {
                                $lastUpdate = new DateTime($driver['last_location_update']);
                                echo $lastUpdate->format('M d, Y g:i A');
                            } else {
                                echo 'Never';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <a href="https://www.google.com/maps?q=<?php echo $driver['current_latitude']; ?>,<?php echo $driver['current_longitude']; ?>" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-geo-alt"></i> View on Map
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No location data available
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Performance Metrics (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="mb-1"><?php echo $stats['total_requests'] ?? 0; ?></h3>
                                <small class="text-muted">Total Requests</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="mb-1"><?php echo $stats['completed_requests'] ?? 0; ?></h3>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="mb-1">
                                    <?php 
                                    $avgTime = $stats['avg_completion_time'] ?? 0;
                                    echo $avgTime ? number_format($avgTime, 0) . ' min' : 'N/A';
                                    ?>
                                </h3>
                                <small class="text-muted">Avg. Completion Time</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="mb-1">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <?php 
                                    $avgRating = $stats['avg_rating'] ?? 0;
                                    echo $avgRating ? number_format($avgRating, 2) : 'N/A';
                                    ?>
                                </h3>
                                <small class="text-muted">Avg. Customer Rating</small>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($stats['total_earnings']) && $stats['total_earnings'] > 0): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-success mb-0">
                                <strong>Total Earnings (30 days):</strong> $<?php echo number_format($stats['total_earnings'], 2); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Statistics -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Career Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Total Jobs</span>
                        <strong class="fs-5"><?php echo $driver['total_jobs'] ?? 0; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Completed Jobs</span>
                        <strong class="fs-5"><?php echo $driver['completed_jobs'] ?? 0; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Completion Rate</span>
                        <strong class="fs-5">
                            <?php 
                            $totalJobs = $driver['total_jobs'] ?? 0;
                            $completedJobs = $driver['completed_jobs'] ?? 0;
                            $rate = $totalJobs > 0 ? ($completedJobs / $totalJobs * 100) : 0;
                            echo number_format($rate, 1) . '%';
                            ?>
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Member Since</span>
                        <strong>
                            <?php 
                            $created = new DateTime($driver['created_at']);
                            echo $created->format('M d, Y');
                            ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <?php if (hasPermission('manage_drivers')): ?>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="updateDriverStatus(<?php echo $driver['id']; ?>, 'available')">
                            <i class="bi bi-check-circle"></i> Set Available
                        </button>
                        <button class="btn btn-warning" onclick="updateDriverStatus(<?php echo $driver['id']; ?>, 'busy')">
                            <i class="bi bi-clock"></i> Set Busy
                        </button>
                        <button class="btn btn-info" onclick="updateDriverStatus(<?php echo $driver['id']; ?>, 'on_break')">
                            <i class="bi bi-cup"></i> Set On Break
                        </button>
                        <button class="btn btn-secondary" onclick="updateDriverStatus(<?php echo $driver['id']; ?>, 'offline')">
                            <i class="bi bi-x-circle"></i> Set Offline
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        You don't have permission to change driver status.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateDriverStatus(driverId, status) {
    if (!confirm('Are you sure you want to change the driver status to ' + status + '?')) {
        return;
    }

    fetch('<?php echo SITE_URL; ?>drivers/updateStatus/' + driverId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'status=' + encodeURIComponent(status)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error updating status: ' + error);
    });
}
</script>
