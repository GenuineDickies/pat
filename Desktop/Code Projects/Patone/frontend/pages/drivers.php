<!-- Drivers Management Page -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Driver Management</h5>
                    <?php if (hasPermission('manage_drivers')): ?>
                    <a href="<?php echo SITE_URL; ?>drivers/add" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Driver
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="<?php echo SITE_URL; ?>drivers" class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search drivers..." value="<?php echo $search ?? ''; ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" action="<?php echo SITE_URL; ?>drivers">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="available" <?php echo ($status ?? '') == 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="busy" <?php echo ($status ?? '') == 'busy' ? 'selected' : ''; ?>>Busy</option>
                                    <option value="offline" <?php echo ($status ?? '') == 'offline' ? 'selected' : ''; ?>>Offline</option>
                                    <option value="on_break" <?php echo ($status ?? '') == 'on_break' ? 'selected' : ''; ?>>On Break</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Drivers Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Rating</th>
                                    <th>Total Jobs</th>
                                    <th>Completed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($drivers)): ?>
                                    <?php foreach ($drivers as $driver): ?>
                                    <tr>
                                        <td><?php echo $driver['id']; ?></td>
                                        <td><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($driver['email']); ?></td>
                                        <td><?php echo formatPhone($driver['phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $driver['status'] == 'available' ? 'success' : 
                                                    ($driver['status'] == 'busy' ? 'warning' : 
                                                    ($driver['status'] == 'on_break' ? 'info' : 'secondary')); 
                                            ?>">
                                                <?php echo ucfirst($driver['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <?php echo number_format($driver['rating'], 2); ?>
                                        </td>
                                        <td><?php echo $driver['total_jobs']; ?></td>
                                        <td><?php echo $driver['completed_jobs']; ?></td>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>drivers/view/<?php echo $driver['id']; ?>" class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (hasPermission('manage_drivers')): ?>
                                            <a href="<?php echo SITE_URL; ?>drivers/edit/<?php echo $driver['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?php echo SITE_URL; ?>drivers/delete/<?php echo $driver['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this driver?')" 
                                               title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No drivers found.</td>
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
                                <a class="page-link" href="<?php echo SITE_URL; ?>drivers?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?>">
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
