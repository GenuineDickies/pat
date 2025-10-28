<!-- Service Request Details Page -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Service Request #<?php echo $request['id']; ?></h5>
                    <div>
                        <a href="<?php echo SITE_URL; ?>requests" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Requests
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Request Status and Actions -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3">
                                <h6 class="mb-0">Status:</h6>
                                <span class="badge bg-<?php 
                                    echo $request['status'] == 'completed' ? 'success' : 
                                        ($request['status'] == 'in_progress' ? 'info' : 
                                        ($request['status'] == 'assigned' ? 'primary' :
                                        ($request['status'] == 'cancelled' ? 'danger' : 'warning'))); 
                                ?> fs-6">
                                    <?php echo ucwords(str_replace('_', ' ', $request['status'])); ?>
                                </span>
                                <span class="badge bg-<?php 
                                    echo $request['priority'] == 'emergency' ? 'danger' : 
                                        ($request['priority'] == 'high' ? 'warning' : 
                                        ($request['priority'] == 'low' ? 'secondary' : 'info')); 
                                ?> fs-6">
                                    <?php echo ucfirst($request['priority']); ?> Priority
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if (hasPermission('manage_requests') && $request['status'] != 'completed' && $request['status'] != 'cancelled'): ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    Update Status
                                </button>
                                <ul class="dropdown-menu">
                                    <?php if ($request['status'] == 'pending'): ?>
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus('assigned')">Mark as Assigned</a></li>
                                    <?php endif; ?>
                                    <?php if ($request['status'] == 'assigned' || $request['status'] == 'pending'): ?>
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus('in_progress')">Mark In Progress</a></li>
                                    <?php endif; ?>
                                    <?php if ($request['status'] == 'in_progress' || $request['status'] == 'assigned'): ?>
                                    <li><a class="dropdown-item" href="#" onclick="showCompleteModal()">Mark as Completed</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="showCancelModal()">Cancel Request</a></li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Request Information Grid -->
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-person-circle"></i> Customer Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Name:</th>
                                            <td><?php echo htmlspecialchars($request['customer_first_name'] . ' ' . $request['customer_last_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><a href="mailto:<?php echo htmlspecialchars($request['customer_email']); ?>"><?php echo htmlspecialchars($request['customer_email']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <th>Phone:</th>
                                            <td><a href="tel:<?php echo htmlspecialchars($request['customer_phone']); ?>"><?php echo htmlspecialchars($request['customer_phone']); ?></a></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Driver Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="bi bi-truck"></i> Driver Assignment</h6>
                                    <?php if (hasPermission('dispatch_requests') && !$request['driver_id'] && $request['status'] != 'completed' && $request['status'] != 'cancelled'): ?>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignDriverModal">
                                        <i class="bi bi-plus-circle"></i> Assign
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if ($request['driver_id']): ?>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Name:</th>
                                            <td><?php echo htmlspecialchars($request['driver_first_name'] . ' ' . $request['driver_last_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Phone:</th>
                                            <td><a href="tel:<?php echo htmlspecialchars($request['driver_phone']); ?>"><?php echo htmlspecialchars($request['driver_phone']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td><span class="badge bg-<?php echo $request['driver_status'] == 'available' ? 'success' : 'warning'; ?>"><?php echo ucfirst($request['driver_status']); ?></span></td>
                                        </tr>
                                        <?php if ($request['assigned_at']): ?>
                                        <tr>
                                            <th>Assigned:</th>
                                            <td><?php echo date('m/d/Y H:i', strtotime($request['assigned_at'])); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                    <?php else: ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="bi bi-exclamation-triangle"></i> No driver assigned yet
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Service Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-tools"></i> Service Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Service Type:</th>
                                            <td><?php echo htmlspecialchars($request['service_type_name']); ?></td>
                                        </tr>
                                        <?php if ($request['vehicle_make']): ?>
                                        <tr>
                                            <th>Vehicle:</th>
                                            <td><?php echo htmlspecialchars($request['vehicle_year'] . ' ' . $request['vehicle_make'] . ' ' . $request['vehicle_model']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th>Base Price:</th>
                                            <td>$<?php echo number_format($request['service_base_price'], 2); ?></td>
                                        </tr>
                                        <?php if ($request['estimated_cost']): ?>
                                        <tr>
                                            <th>Estimated Cost:</th>
                                            <td>$<?php echo number_format($request['estimated_cost'], 2); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($request['final_cost']): ?>
                                        <tr>
                                            <th>Final Cost:</th>
                                            <td><strong>$<?php echo number_format($request['final_cost'], 2); ?></strong></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Location</h6>
                                </div>
                                <div class="card-body">
                                    <address>
                                        <strong><?php echo htmlspecialchars($request['location_address']); ?></strong><br>
                                        <?php echo htmlspecialchars($request['location_city'] . ', ' . $request['location_state']); ?>
                                    </address>
                                    <?php if ($request['location_latitude'] && $request['location_longitude']): ?>
                                    <a href="https://www.google.com/maps?q=<?php echo $request['location_latitude']; ?>,<?php echo $request['location_longitude']; ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-map"></i> View on Map
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> Timeline</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="timeline">
                                        <li>
                                            <strong>Created:</strong> <?php echo date('m/d/Y H:i', strtotime($request['created_at'])); ?>
                                        </li>
                                        <?php if ($request['assigned_at']): ?>
                                        <li>
                                            <strong>Assigned:</strong> <?php echo date('m/d/Y H:i', strtotime($request['assigned_at'])); ?>
                                        </li>
                                        <?php endif; ?>
                                        <?php if ($request['started_at']): ?>
                                        <li>
                                            <strong>Started:</strong> <?php echo date('m/d/Y H:i', strtotime($request['started_at'])); ?>
                                        </li>
                                        <?php endif; ?>
                                        <?php if ($request['completed_at']): ?>
                                        <li>
                                            <strong>Completed:</strong> <?php echo date('m/d/Y H:i', strtotime($request['completed_at'])); ?>
                                        </li>
                                        <?php endif; ?>
                                        <?php if ($request['cancelled_at']): ?>
                                        <li class="text-danger">
                                            <strong>Cancelled:</strong> <?php echo date('m/d/Y H:i', strtotime($request['cancelled_at'])); ?>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Notes and Description -->
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-chat-left-text"></i> Notes and Details</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($request['description']): ?>
                                    <div class="mb-3">
                                        <strong>Description:</strong>
                                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($request['description'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($request['customer_notes']): ?>
                                    <div class="mb-3">
                                        <strong>Customer Notes:</strong>
                                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($request['customer_notes'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($request['driver_notes']): ?>
                                    <div class="mb-3">
                                        <strong>Driver Notes:</strong>
                                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($request['driver_notes'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($request['internal_notes']): ?>
                                    <div class="mb-3">
                                        <strong>Internal Notes:</strong>
                                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($request['internal_notes'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($request['cancellation_reason']): ?>
                                    <div class="alert alert-danger">
                                        <strong>Cancellation Reason:</strong>
                                        <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($request['cancellation_reason'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Rating -->
                        <?php if ($request['status'] == 'completed'): ?>
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-star"></i> Customer Rating</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($request['rating']): ?>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $request['rating'] ? '-fill' : ''; ?> text-warning"></i>
                                            <?php endfor; ?>
                                            <span class="ms-2"><?php echo $request['rating']; ?>/5</span>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No rating provided yet</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Driver Modal -->
<div class="modal fade" id="assignDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignDriverForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="mb-3">
                        <label for="driver_id" class="form-label">Select Driver</label>
                        <select class="form-select" id="driver_id" name="driver_id" required>
                            <option value="">Choose available driver...</option>
                            <?php if (!empty($drivers)): ?>
                                <?php foreach ($drivers as $driver): ?>
                                <option value="<?php echo $driver['id']; ?>">
                                    <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                                    (<?php echo ucfirst($driver['status']); ?>)
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignDriver()">Assign Driver</button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Request Modal -->
<div class="modal fade" id="completeRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo SITE_URL; ?>requests/complete/<?php echo $request['id']; ?>">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="mb-3">
                        <label for="final_cost" class="form-label">Final Cost</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="final_cost" name="final_cost" 
                                   step="0.01" min="0" value="<?php echo $request['estimated_cost'] ?? ''; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="driver_notes" class="form-label">Driver Notes</label>
                        <textarea class="form-control" id="driver_notes" name="driver_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Request Modal -->
<div class="modal fade" id="cancelRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo SITE_URL; ?>requests/cancel/<?php echo $request['id']; ?>">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Cancellation Reason</label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" 
                                  rows="3" required placeholder="Please provide a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    list-style: none;
    padding-left: 0;
}
.timeline li {
    padding: 8px 0;
    border-left: 2px solid #0d6efd;
    padding-left: 20px;
    margin-left: 10px;
    position: relative;
}
.timeline li:before {
    content: '';
    width: 12px;
    height: 12px;
    background: #0d6efd;
    border: 2px solid #fff;
    border-radius: 50%;
    position: absolute;
    left: -7px;
    top: 12px;
}
.rating i {
    font-size: 1.5rem;
}
</style>

<script>
function assignDriver() {
    const driverId = document.getElementById('driver_id').value;
    if (!driverId) {
        alert('Please select a driver');
        return;
    }

    const formData = new FormData();
    formData.append('driver_id', driverId);
    formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

    fetch('<?php echo SITE_URL; ?>api/requests/<?php echo $request['id']; ?>/assign-driver', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to assign driver');
        }
    })
    .catch(error => {
        alert('Error assigning driver');
        console.error(error);
    });
}

function updateStatus(status) {
    if (!confirm('Are you sure you want to update the status?')) {
        return;
    }

    const formData = new FormData();
    formData.append('status', status);
    formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

    fetch('<?php echo SITE_URL; ?>api/requests/<?php echo $request['id']; ?>/status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to update status');
        }
    })
    .catch(error => {
        alert('Error updating status');
        console.error(error);
    });
}

function showCompleteModal() {
    const modal = new bootstrap.Modal(document.getElementById('completeRequestModal'));
    modal.show();
}

function showCancelModal() {
    const modal = new bootstrap.Modal(document.getElementById('cancelRequestModal'));
    modal.show();
}
</script>
