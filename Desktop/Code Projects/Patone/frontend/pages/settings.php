<!-- Enhanced Settings Page with Tabbed Interface -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>System Settings</h2>
            <p class="text-muted">Manage system configuration, users, roles, and services</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'general' ? 'active' : ''; ?>" 
               href="<?php echo SITE_URL; ?>settings?tab=general">
                <i class="bi bi-gear"></i> General
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'users' ? 'active' : ''; ?>" 
               href="<?php echo SITE_URL; ?>settings?tab=users">
                <i class="bi bi-people"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'roles' ? 'active' : ''; ?>" 
               href="<?php echo SITE_URL; ?>settings?tab=roles">
                <i class="bi bi-shield-lock"></i> Roles & Permissions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'services' ? 'active' : ''; ?>" 
               href="<?php echo SITE_URL; ?>settings?tab=services">
                <i class="bi bi-tools"></i> Service Types
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'backup' ? 'active' : ''; ?>" 
               href="<?php echo SITE_URL; ?>settings?tab=backup">
                <i class="bi bi-download"></i> Backup & Restore
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        
        <?php if ($activeTab === 'general'): ?>
        <!-- General Settings Tab -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">General Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo SITE_URL; ?>settings">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="row">
                        <?php if (!empty($settings)): ?>
                            <?php foreach ($settings as $key => $setting): ?>
                                <div class="col-md-6 mb-3">
                                    <label for="<?php echo $key; ?>" class="form-label">
                                        <?php echo ucwords(str_replace('_', ' ', $key)); ?>
                                    </label>
                                    
                                    <?php if ($setting['type'] === 'boolean'): ?>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="<?php echo $key; ?>" 
                                                   name="<?php echo $key; ?>" 
                                                   value="true"
                                                   <?php echo $setting['value'] ? 'checked' : ''; ?>>
                                        </div>
                                    <?php elseif ($setting['type'] === 'integer'): ?>
                                        <input type="number" 
                                               class="form-control" 
                                               id="<?php echo $key; ?>" 
                                               name="<?php echo $key; ?>" 
                                               value="<?php echo htmlspecialchars($setting['value']); ?>">
                                    <?php else: ?>
                                        <input type="text" 
                                               class="form-control" 
                                               id="<?php echo $key; ?>" 
                                               name="<?php echo $key; ?>" 
                                               value="<?php echo htmlspecialchars($setting['value']); ?>">
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($setting['description'])): ?>
                                        <small class="form-text text-muted"><?php echo htmlspecialchars($setting['description']); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <hr class="my-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Settings
                    </button>
                    <a href="<?php echo SITE_URL; ?>dashboard" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'users'): ?>
        <!-- User Management Tab -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">User Management</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle"></i> Add User
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($user['role']); ?></span></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editUser(<?php echo $user['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="<?php echo SITE_URL; ?>settings/user/delete/<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No users found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'roles'): ?>
        <!-- Roles & Permissions Tab -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Roles & Permissions</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($roles)): ?>
                    <?php foreach ($roles as $roleKey => $roleData): ?>
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><?php echo $roleData['name']; ?> Role</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($roleKey === 'admin'): ?>
                                    <p class="text-muted">
                                        <i class="bi bi-info-circle"></i> 
                                        Admin role has all permissions and cannot be modified.
                                    </p>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo SITE_URL; ?>settings/role/permissions">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="role" value="<?php echo $roleKey; ?>">
                                        
                                        <?php if (!empty($permissionGroups)): ?>
                                            <div class="row">
                                                <?php foreach ($permissionGroups as $category => $permissions): ?>
                                                    <div class="col-md-6 mb-3">
                                                        <h6><?php echo htmlspecialchars($category); ?></h6>
                                                        <?php foreach ($permissions as $permission): ?>
                                                            <div class="form-check">
                                                                <input type="checkbox" 
                                                                       class="form-check-input" 
                                                                       name="permissions[]" 
                                                                       value="<?php echo $permission['id']; ?>"
                                                                       id="perm_<?php echo $roleKey; ?>_<?php echo $permission['id']; ?>"
                                                                       <?php echo in_array($permission['permission_key'], $roleData['permissions']) ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" 
                                                                       for="perm_<?php echo $roleKey; ?>_<?php echo $permission['id']; ?>">
                                                                    <?php echo htmlspecialchars($permission['name']); ?>
                                                                    <br><small class="text-muted"><?php echo htmlspecialchars($permission['description']); ?></small>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <hr>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Update Permissions
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'services'): ?>
        <!-- Service Types Tab -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Service Types</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-circle"></i> Add Service Type
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Base Price</th>
                                <th>Duration (min)</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($serviceTypes)): ?>
                                <?php foreach ($serviceTypes as $service): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                                        <td><?php echo htmlspecialchars($service['description'] ?? '-'); ?></td>
                                        <td><?php echo formatCurrency($service['base_price']); ?></td>
                                        <td><?php echo $service['estimated_duration']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $service['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $service['priority']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editService(<?php echo $service['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="<?php echo SITE_URL; ?>settings/service/delete/<?php echo $service['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this service type?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No service types found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'backup'): ?>
        <!-- Backup & Restore Tab -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Backup & Restore Settings</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bi bi-download"></i> Export Settings</h6>
                            </div>
                            <div class="card-body">
                                <p>Download all system settings as a JSON file for backup purposes.</p>
                                <a href="<?php echo SITE_URL; ?>settings/export" class="btn btn-primary">
                                    <i class="bi bi-download"></i> Export Settings
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bi bi-upload"></i> Import Settings</h6>
                            </div>
                            <div class="card-body">
                                <p>Restore settings from a previously exported JSON file.</p>
                                <form method="POST" action="<?php echo SITE_URL; ?>settings/import" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name="settings_file" accept=".json" required>
                                    </div>
                                    <button type="submit" class="btn btn-warning" 
                                            onclick="return confirm('Warning: This will overwrite existing settings. Continue?')">
                                        <i class="bi bi-upload"></i> Import Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <h6><i class="bi bi-info-circle"></i> Backup Information</h6>
                    <ul class="mb-0">
                        <li>Regular backups are recommended before making significant changes</li>
                        <li>Exported files contain all system configuration settings</li>
                        <li>User passwords are not included in backups for security</li>
                        <li>Keep backup files in a secure location</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo SITE_URL; ?>settings/user/add">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required 
                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                        <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="dispatcher">Dispatcher</option>
                            <option value="driver">Driver</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Service Type Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo SITE_URL; ?>settings/service/add">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Service Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Service Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Base Price ($) *</label>
                            <input type="number" class="form-control" name="base_price" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (minutes) *</label>
                            <input type="number" class="form-control" name="estimated_duration" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <input type="number" class="form-control" name="priority" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Placeholder functions for edit modals (to be implemented with JavaScript)
function editUser(userId) {
    alert('Edit user functionality - ID: ' + userId);
    // TODO: Implement edit user modal
}

function editService(serviceId) {
    alert('Edit service functionality - ID: ' + serviceId);
    // TODO: Implement edit service modal
}
</script>
