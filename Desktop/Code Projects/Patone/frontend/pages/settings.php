<!-- Settings Page -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo SITE_URL; ?>settings">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <!-- General Settings -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">General Settings</h6>
                            </div>

                            <?php if (!empty($settings)): ?>
                                <?php foreach ($settings as $key => $setting): ?>
                                    <?php if ($setting['is_public'] || hasPermission('manage_settings')): ?>
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
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-md-12">
                                    <p class="text-muted">No settings available.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Save Button -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Settings
                                </button>
                                <a href="<?php echo SITE_URL; ?>dashboard" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Settings Cards -->
    <div class="row">
        <!-- Dispatch Settings -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-truck"></i> Dispatch Settings</h6>
                </div>
                <div class="card-body">
                    <p><strong>Max Dispatch Distance:</strong> 
                        <?php echo isset($settings['max_dispatch_distance']) ? $settings['max_dispatch_distance']['value'] : 50; ?> miles
                    </p>
                    <p><strong>Default Service Radius:</strong> 
                        <?php echo isset($settings['default_service_radius']) ? $settings['default_service_radius']['value'] : 25; ?> miles
                    </p>
                    <small class="text-muted">These settings control automated driver dispatch and service coverage area.</small>
                </div>
            </div>
        </div>

        <!-- GPS & Tracking -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-geo-alt"></i> GPS & Tracking</h6>
                </div>
                <div class="card-body">
                    <p><strong>GPS Tracking:</strong> 
                        <span class="badge bg-<?php echo (isset($settings['enable_gps_tracking']) && $settings['enable_gps_tracking']['value']) ? 'success' : 'secondary'; ?>">
                            <?php echo (isset($settings['enable_gps_tracking']) && $settings['enable_gps_tracking']['value']) ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </p>
                    <small class="text-muted">Enable real-time GPS tracking for drivers and service requests.</small>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-bell"></i> Notifications</h6>
                </div>
                <div class="card-body">
                    <p><strong>Notifications:</strong> 
                        <span class="badge bg-<?php echo (isset($settings['enable_notifications']) && $settings['enable_notifications']['value']) ? 'success' : 'secondary'; ?>">
                            <?php echo (isset($settings['enable_notifications']) && $settings['enable_notifications']['value']) ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </p>
                    <p><strong>Notification Email:</strong> 
                        <?php echo isset($settings['notification_email']) ? htmlspecialchars($settings['notification_email']['value']) : 'Not set'; ?>
                    </p>
                    <small class="text-muted">Configure email and SMS notifications for status updates.</small>
                </div>
            </div>
        </div>

        <!-- Business Hours -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-clock"></i> Business Hours</h6>
                </div>
                <div class="card-body">
                    <p><strong>Start Time:</strong> 
                        <?php echo isset($settings['business_hours_start']) ? $settings['business_hours_start']['value'] : '08:00'; ?>
                    </p>
                    <p><strong>End Time:</strong> 
                        <?php echo isset($settings['business_hours_end']) ? $settings['business_hours_end']['value'] : '20:00'; ?>
                    </p>
                    <p><strong>Timezone:</strong> 
                        <?php echo isset($settings['timezone']) ? $settings['timezone']['value'] : 'America/New_York'; ?>
                    </p>
                    <small class="text-muted">Define your standard operating hours and timezone.</small>
                </div>
            </div>
        </div>
    </div>
</div>
