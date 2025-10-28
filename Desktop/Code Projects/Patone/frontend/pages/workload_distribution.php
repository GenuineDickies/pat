<!-- Workload Distribution Dashboard -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Driver Workload Distribution</h4>
                    <p class="text-muted mb-0">Monitor and balance driver workload across the fleet</p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>drivers" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Drivers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <?php
        $totalCapacity = 0;
        $totalUtilized = 0;
        $activeDrivers = 0;
        
        foreach ($distribution as $driver) {
            if ($driver['status'] == 'available' || $driver['status'] == 'busy') {
                $activeDrivers++;
                $totalCapacity += $driver['max_workload'];
                $totalUtilized += $driver['current_workload'];
            }
        }
        
        $avgUtilization = $totalCapacity > 0 ? ($totalUtilized / $totalCapacity * 100) : 0;
        ?>
        
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1"><?php echo $activeDrivers; ?></h3>
                    <small class="text-muted">Active Drivers</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1"><?php echo $totalCapacity; ?></h3>
                    <small class="text-muted">Total Capacity</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1"><?php echo $totalUtilized; ?></h3>
                    <small class="text-muted">Currently Utilized</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1"><?php echo number_format($avgUtilization, 1); ?>%</h3>
                    <small class="text-muted">Avg Utilization</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Workload Distribution Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Driver Workload Status</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($distribution)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Current Workload</th>
                                    <th>Max Capacity</th>
                                    <th>Available Capacity</th>
                                    <th>Utilization</th>
                                    <th>Workload Bar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($distribution as $driver): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>drivers/dashboard/<?php echo $driver['id']; ?>">
                                            <?php echo htmlspecialchars($driver['name']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $driver['status'] == 'available' ? 'success' : 
                                                ($driver['status'] == 'busy' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($driver['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong><?php echo $driver['current_workload']; ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $driver['max_workload']; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo $driver['available_capacity'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $driver['available_capacity']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $utilization = $driver['utilization_percentage'];
                                        $badgeColor = 'success';
                                        if ($utilization >= 100) {
                                            $badgeColor = 'danger';
                                        } elseif ($utilization >= 80) {
                                            $badgeColor = 'warning';
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $badgeColor; ?>">
                                            <?php echo number_format($utilization, 0); ?>%
                                        </span>
                                    </td>
                                    <td style="width: 200px;">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar <?php 
                                                if ($utilization >= 100) {
                                                    echo 'bg-danger';
                                                } elseif ($utilization >= 80) {
                                                    echo 'bg-warning';
                                                } else {
                                                    echo 'bg-success';
                                                }
                                            ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo min(100, $utilization); ?>%"
                                                 aria-valuenow="<?php echo $utilization; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?php if ($utilization > 15): ?>
                                                    <?php echo number_format($utilization, 0); ?>%
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No active drivers to display.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Workload Balancing Recommendations</h5>
                </div>
                <div class="card-body">
                    <?php
                    $overloaded = [];
                    $underutilized = [];
                    $atCapacity = [];
                    
                    foreach ($distribution as $driver) {
                        $util = $driver['utilization_percentage'];
                        if ($util >= 100) {
                            $atCapacity[] = $driver;
                        } elseif ($util >= 80) {
                            $overloaded[] = $driver;
                        } elseif ($util < 30 && $driver['status'] == 'available') {
                            $underutilized[] = $driver;
                        }
                    }
                    ?>
                    
                    <?php if (!empty($atCapacity)): ?>
                    <div class="alert alert-danger">
                        <strong><i class="bi bi-exclamation-triangle"></i> At Capacity:</strong>
                        <?php foreach ($atCapacity as $driver): ?>
                            <span class="badge bg-danger"><?php echo htmlspecialchars($driver['name']); ?></span>
                        <?php endforeach; ?>
                        <p class="mb-0 mt-2">These drivers are at or over capacity. Consider reassigning new requests to other drivers.</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($overloaded)): ?>
                    <div class="alert alert-warning">
                        <strong><i class="bi bi-exclamation-circle"></i> High Utilization:</strong>
                        <?php foreach ($overloaded as $driver): ?>
                            <span class="badge bg-warning"><?php echo htmlspecialchars($driver['name']); ?></span>
                        <?php endforeach; ?>
                        <p class="mb-0 mt-2">These drivers have high utilization (80%+). Monitor closely and avoid adding more requests.</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($underutilized)): ?>
                    <div class="alert alert-success">
                        <strong><i class="bi bi-check-circle"></i> Available Capacity:</strong>
                        <?php foreach ($underutilized as $driver): ?>
                            <span class="badge bg-success"><?php echo htmlspecialchars($driver['name']); ?> (<?php echo $driver['available_capacity']; ?> slots)</span>
                        <?php endforeach; ?>
                        <p class="mb-0 mt-2">These drivers have available capacity and can take on more requests.</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (empty($atCapacity) && empty($overloaded) && empty($underutilized)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Workload is well balanced across all drivers.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
