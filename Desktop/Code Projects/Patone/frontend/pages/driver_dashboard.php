<!-- Driver Dashboard - Comprehensive Metrics -->
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?> - Dashboard</h4>
                    <p class="text-muted mb-0">
                        <span class="badge bg-<?php 
                            echo $driver['status'] == 'available' ? 'success' : 
                                ($driver['status'] == 'busy' ? 'warning' : 
                                ($driver['status'] == 'on_break' ? 'info' : 'secondary')); 
                        ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $driver['status'])); ?>
                        </span>
                        <?php if ($is_scheduled_available): ?>
                        <span class="badge bg-info ms-2">
                            <i class="bi bi-calendar-check"></i> Scheduled
                        </span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="btn-group">
                    <a href="<?php echo SITE_URL; ?>drivers" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <a href="<?php echo SITE_URL; ?>drivers/view/<?php echo $driver['id']; ?>" class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> Details
                    </a>
                    <?php if (hasPermission('manage_drivers')): ?>
                    <a href="<?php echo SITE_URL; ?>drivers/edit/<?php echo $driver['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <?php endif; ?>
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
                            <h6 class="card-title">Current Workload</h6>
                            <h2 class="mb-0"><?php echo $workload['current']; ?> / <?php echo $workload['max']; ?></h2>
                            <small class="opacity-75">Active requests</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-speedometer fs-1 opacity-75"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 5px;">
                        <div class="progress-bar bg-white" role="progressbar" 
                             style="width: <?php echo min(100, $workload['utilization_percentage']); ?>%">
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
                            <h6 class="card-title">Completed (30d)</h6>
                            <h2 class="mb-0"><?php echo $stats['completed_requests'] ?? 0; ?></h2>
                            <small class="opacity-75">Requests completed</small>
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
                            <h6 class="card-title">Avg Rating</h6>
                            <h2 class="mb-0">
                                <i class="bi bi-star-fill"></i>
                                <?php 
                                $avgRating = $stats['avg_rating'] ?? $driver['rating'];
                                echo $avgRating ? number_format($avgRating, 2) : 'N/A';
                                ?>
                            </h2>
                            <small class="opacity-75">Customer feedback</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-emoji-smile fs-1 opacity-75"></i>
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
                            <h6 class="card-title">Completion Rate</h6>
                            <h2 class="mb-0">
                                <?php 
                                $totalJobs = $driver['total_jobs'] ?? 0;
                                $completedJobs = $driver['completed_jobs'] ?? 0;
                                $rate = $totalJobs > 0 ? ($completedJobs / $totalJobs * 100) : 0;
                                echo number_format($rate, 1) . '%';
                                ?>
                            </h2>
                            <small class="opacity-75"><?php echo $completedJobs; ?> / <?php echo $totalJobs; ?> jobs</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-graph-up fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Performance Metrics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Performance Metrics (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="mb-1"><?php echo $stats['total_requests'] ?? 0; ?></h3>
                                <small class="text-muted">Total Requests</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="mb-1">
                                    <?php 
                                    $avgTime = $stats['avg_completion_time'] ?? 0;
                                    echo $avgTime ? number_format($avgTime, 0) . ' min' : 'N/A';
                                    ?>
                                </h3>
                                <small class="text-muted">Avg Completion Time</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="mb-1">
                                    <?php 
                                    $earnings = $stats['total_earnings'] ?? 0;
                                    echo $earnings ? '$' . number_format($earnings, 2) : '$0.00';
                                    ?>
                                </h3>
                                <small class="text-muted">Total Earnings</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certifications Status -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Certifications</h5>
                    <a href="<?php echo SITE_URL; ?>drivers/certifications/<?php echo $driver['id']; ?>" class="btn btn-sm btn-outline-primary">
                        Manage <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($certifications)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($certifications, 0, 5) as $cert): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cert['certification_type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $cert['status'] == 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($cert['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($cert['expiry_date']) {
                                            $expiry = new DateTime($cert['expiry_date']);
                                            echo $expiry->format('M d, Y');
                                            
                                            // Check if expiring soon
                                            $now = new DateTime();
                                            $diff = $now->diff($expiry);
                                            if ($expiry < $now) {
                                                echo ' <span class="badge bg-danger">Expired</span>';
                                            } elseif ($diff->days <= 30) {
                                                echo ' <span class="badge bg-warning">Expires Soon</span>';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No certifications on file
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Documents Status -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Documents</h5>
                    <a href="<?php echo SITE_URL; ?>drivers/documents/<?php echo $driver['id']; ?>" class="btn btn-sm btn-outline-primary">
                        Manage <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($documents)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($documents, 0, 5) as $doc): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-file-earmark-text"></i>
                                <?php echo htmlspecialchars($doc['document_name']); ?>
                            </div>
                            <span class="badge bg-<?php 
                                echo $doc['status'] == 'active' ? 'success' : 
                                    ($doc['status'] == 'expired' ? 'danger' : 'warning'); 
                            ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $doc['status'])); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No documents uploaded
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Alerts and Notifications -->
            <?php if (!empty($expiring_certifications)): ?>
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Alerts</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <strong>Expiring Certifications:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($expiring_certifications as $cert): ?>
                            <li>
                                <?php echo htmlspecialchars($cert['certification_type']); ?>
                                (<?php 
                                $expiry = new DateTime($cert['expiry_date']);
                                echo $expiry->format('M d, Y'); 
                                ?>)
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Availability Schedule Summary -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Schedule</h5>
                    <?php if (hasPermission('manage_drivers')): ?>
                    <a href="<?php echo SITE_URL; ?>drivers/schedule/<?php echo $driver['id']; ?>" class="btn btn-sm btn-outline-primary">
                        Edit <i class="bi bi-pencil"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($schedule)): ?>
                    <div class="small">
                        <?php 
                        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        $scheduleByDay = [];
                        foreach ($schedule as $s) {
                            $scheduleByDay[$s['day_of_week']][] = $s;
                        }
                        
                        foreach ($daysOfWeek as $dayNum => $dayName):
                            if (isset($scheduleByDay[$dayNum])):
                        ?>
                        <div class="mb-2 pb-2 border-bottom">
                            <strong><?php echo $dayName; ?>:</strong><br>
                            <?php foreach ($scheduleByDay[$dayNum] as $slot): ?>
                            <span class="text-muted">
                                <?php echo date('g:i A', strtotime($slot['start_time'])); ?> - 
                                <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                                <?php if (!$slot['is_available']): ?>
                                <span class="badge bg-secondary">Unavailable</span>
                                <?php endif; ?>
                            </span><br>
                            <?php endforeach; ?>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No schedule configured
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>drivers/view/<?php echo $driver['id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> View Full Details
                        </a>
                        <?php if (hasPermission('manage_drivers')): ?>
                        <a href="<?php echo SITE_URL; ?>drivers/certifications/<?php echo $driver['id']; ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-award"></i> Manage Certifications
                        </a>
                        <a href="<?php echo SITE_URL; ?>drivers/documents/<?php echo $driver['id']; ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-file-earmark-text"></i> Manage Documents
                        </a>
                        <a href="<?php echo SITE_URL; ?>drivers/schedule/<?php echo $driver['id']; ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-calendar"></i> Edit Schedule
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Location Info -->
            <?php if ($driver['current_latitude'] && $driver['current_longitude']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Current Location</h5>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Coordinates:</strong><br>
                        Lat: <?php echo number_format($driver['current_latitude'], 6); ?><br>
                        Lng: <?php echo number_format($driver['current_longitude'], 6); ?>
                    </p>
                    <p class="small mb-2">
                        <strong>Last Update:</strong><br>
                        <?php 
                        if ($driver['last_location_update']) {
                            $lastUpdate = new DateTime($driver['last_location_update']);
                            echo $lastUpdate->format('M d, Y g:i A');
                        } else {
                            echo 'Never';
                        }
                        ?>
                    </p>
                    <a href="https://www.google.com/maps?q=<?php echo $driver['current_latitude']; ?>,<?php echo $driver['current_longitude']; ?>" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-geo-alt"></i> View on Map
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
