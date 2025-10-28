<!-- Driver Certifications Management -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Certifications - <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></h4>
                    <p class="text-muted mb-0">Manage driver certifications and licenses</p>
                </div>
                <div class="btn-group">
                    <a href="<?php echo SITE_URL; ?>drivers/dashboard/<?php echo $driver['id']; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    <?php if (hasPermission('manage_drivers')): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCertificationModal">
                        <i class="bi bi-plus-circle"></i> Add Certification
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Certifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Certifications & Licenses</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($certifications)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Number</th>
                                    <th>Issuing Authority</th>
                                    <th>Issue Date</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($certifications as $cert): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cert['certification_type']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['certification_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($cert['issuing_authority'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        if ($cert['issue_date']) {
                                            $issue = new DateTime($cert['issue_date']);
                                            echo $issue->format('M d, Y');
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($cert['expiry_date']) {
                                            $expiry = new DateTime($cert['expiry_date']);
                                            $now = new DateTime();
                                            $diff = $now->diff($expiry);
                                            
                                            echo $expiry->format('M d, Y');
                                            
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
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $cert['status'] == 'active' ? 'success' : 
                                                ($cert['status'] == 'expired' ? 'danger' : 
                                                ($cert['status'] == 'pending' ? 'warning' : 'secondary')); 
                                        ?>">
                                            <?php echo ucfirst($cert['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (hasPermission('manage_drivers')): ?>
                                        <a href="<?php echo SITE_URL; ?>drivers/deleteCertification/<?php echo $driver['id']; ?>/<?php echo $cert['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this certification?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No certifications on file. Add the first certification using the button above.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Certification Modal -->
<div class="modal fade" id="addCertificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo SITE_URL; ?>drivers/addCertification/<?php echo $driver['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add Certification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="certification_type" class="form-label">Certification Type *</label>
                        <input type="text" class="form-control" id="certification_type" name="certification_type" 
                               placeholder="e.g., CDL, First Aid, Towing License" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="certification_number" class="form-label">Certification Number</label>
                        <input type="text" class="form-control" id="certification_number" name="certification_number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="issuing_authority" class="form-label">Issuing Authority</label>
                        <input type="text" class="form-control" id="issuing_authority" name="issuing_authority"
                               placeholder="e.g., State DMV, Red Cross">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="issue_date" class="form-label">Issue Date</label>
                            <input type="date" class="form-control" id="issue_date" name="issue_date">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="expired">Expired</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Certification</button>
                </div>
            </form>
        </div>
    </div>
</div>
