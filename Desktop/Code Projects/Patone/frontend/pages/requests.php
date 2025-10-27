<!-- Service Requests Page -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Service Requests</h5>
                    <?php if (hasPermission('manage_requests')): ?>
                    <a href="<?php echo SITE_URL; ?>requests/add" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Request
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form method="GET" action="<?php echo SITE_URL; ?>requests" class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search requests..." value="<?php echo $search ?? ''; ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET" action="<?php echo SITE_URL; ?>requests">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo ($status ?? '') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="assigned" <?php echo ($status ?? '') == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                    <option value="in_progress" <?php echo ($status ?? '') == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo ($status ?? '') == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo ($status ?? '') == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET" action="<?php echo SITE_URL; ?>requests">
                                <select name="priority" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Priorities</option>
                                    <option value="emergency" <?php echo ($priority ?? '') == 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                                    <option value="high" <?php echo ($priority ?? '') == 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="normal" <?php echo ($priority ?? '') == 'normal' ? 'selected' : ''; ?>>Normal</option>
                                    <option value="low" <?php echo ($priority ?? '') == 'low' ? 'selected' : ''; ?>>Low</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Requests Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Service Type</th>
                                    <th>Driver</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($requests)): ?>
                                    <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td>#<?php echo $request['id']; ?></td>
                                        <td><?php echo htmlspecialchars($request['customer_first_name'] . ' ' . $request['customer_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['service_type_name']); ?></td>
                                        <td>
                                            <?php if ($request['driver_id']): ?>
                                                <?php echo htmlspecialchars($request['driver_first_name'] . ' ' . $request['driver_last_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['location_city'] . ', ' . $request['location_state']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $request['status'] == 'completed' ? 'success' : 
                                                    ($request['status'] == 'in_progress' ? 'info' : 
                                                    ($request['status'] == 'assigned' ? 'primary' :
                                                    ($request['status'] == 'cancelled' ? 'danger' : 'warning'))); 
                                            ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $request['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $request['priority'] == 'emergency' ? 'danger' : 
                                                    ($request['priority'] == 'high' ? 'warning' : 
                                                    ($request['priority'] == 'low' ? 'secondary' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($request['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('m/d/Y H:i', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>requests/<?php echo $request['id']; ?>" class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No requests found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($currentPage == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo SITE_URL; ?>requests?page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
