<!-- Driver Documents Management -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Documents - <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></h4>
                    <p class="text-muted mb-0">Manage driver documents and files</p>
                </div>
                <div class="btn-group">
                    <a href="<?php echo SITE_URL; ?>drivers/dashboard/<?php echo $driver['id']; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    <?php if (hasPermission('manage_drivers')): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="bi bi-upload"></i> Upload Document
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Driver Documents</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($documents)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Document Name</th>
                                    <th>Upload Date</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-file-earmark-text text-primary"></i>
                                        <?php echo htmlspecialchars($doc['document_type']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                                    <td>
                                        <?php 
                                        $created = new DateTime($doc['created_at']);
                                        echo $created->format('M d, Y');
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($doc['expiry_date']) {
                                            $expiry = new DateTime($doc['expiry_date']);
                                            $now = new DateTime();
                                            
                                            echo $expiry->format('M d, Y');
                                            
                                            if ($expiry < $now) {
                                                echo ' <span class="badge bg-danger">Expired</span>';
                                            } elseif ($now->diff($expiry)->days <= 30) {
                                                echo ' <span class="badge bg-warning">Expires Soon</span>';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $doc['status'] == 'active' ? 'success' : 
                                                ($doc['status'] == 'expired' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $doc['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($doc['file_size']) {
                                            $size = $doc['file_size'];
                                            if ($size < 1024) {
                                                echo $size . ' B';
                                            } elseif ($size < 1048576) {
                                                echo round($size / 1024, 2) . ' KB';
                                            } else {
                                                echo round($size / 1048576, 2) . ' MB';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if (file_exists(UPLOAD_PATH . $doc['file_path'])): ?>
                                            <a href="<?php echo SITE_URL . 'uploads/' . $doc['file_path']; ?>" 
                                               target="_blank" 
                                               class="btn btn-outline-primary" 
                                               title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?php echo SITE_URL . 'uploads/' . $doc['file_path']; ?>" 
                                               download 
                                               class="btn btn-outline-success" 
                                               title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (hasPermission('manage_drivers')): ?>
                                            <a href="<?php echo SITE_URL; ?>drivers/deleteDocument/<?php echo $driver['id']; ?>/<?php echo $doc['id']; ?>" 
                                               class="btn btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this document?')"
                                               title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No documents uploaded yet. Upload the first document using the button above.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Types Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recommended Document Types</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> Vehicle Insurance</li>
                                <li><i class="bi bi-check-circle text-success"></i> Vehicle Registration</li>
                                <li><i class="bi bi-check-circle text-success"></i> Background Check</li>
                                <li><i class="bi bi-check-circle text-success"></i> Drug Test Results</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> Medical Certificate</li>
                                <li><i class="bi bi-check-circle text-success"></i> W-9 Form</li>
                                <li><i class="bi bi-check-circle text-success"></i> Emergency Contact Form</li>
                                <li><i class="bi bi-check-circle text-success"></i> Safety Training Certificate</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo SITE_URL; ?>drivers/uploadDocument/<?php echo $driver['id']; ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="document_type" class="form-label">Document Type *</label>
                        <select class="form-select" id="document_type" name="document_type" required>
                            <option value="">Select type...</option>
                            <option value="Vehicle Insurance">Vehicle Insurance</option>
                            <option value="Vehicle Registration">Vehicle Registration</option>
                            <option value="Background Check">Background Check</option>
                            <option value="Drug Test">Drug Test Results</option>
                            <option value="Medical Certificate">Medical Certificate</option>
                            <option value="W-9 Form">W-9 Form</option>
                            <option value="Emergency Contact">Emergency Contact Form</option>
                            <option value="Safety Training">Safety Training Certificate</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="document_name" class="form-label">Document Name *</label>
                        <input type="text" class="form-control" id="document_name" name="document_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="document_file" class="form-label">File *</label>
                        <input type="file" class="form-control" id="document_file" name="document_file" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Accepted: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date (if applicable)</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Document</button>
                </div>
            </form>
        </div>
    </div>
</div>
