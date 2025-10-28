<!-- Driver Form (Add/Edit) -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo ($action ?? 'add') == 'add' ? 'Add New Driver' : 'Edit Driver'; ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($_SESSION['form_errors'])): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['form_errors']); ?>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo SITE_URL; ?>drivers/<?php echo ($action ?? 'add') == 'add' ? 'add' : 'edit/' . $driver['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($driver['first_name'] ?? $_SESSION['form_data']['first_name'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($driver['last_name'] ?? $_SESSION['form_data']['last_name'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($driver['email'] ?? $_SESSION['form_data']['email'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($driver['phone'] ?? $_SESSION['form_data']['phone'] ?? ''); ?>" required>
                            </div>

                            <!-- License Information -->
                            <div class="col-md-6 mb-3">
                                <label for="license_number" class="form-label">License Number *</label>
                                <input type="text" class="form-control" id="license_number" name="license_number" 
                                       value="<?php echo htmlspecialchars($driver['license_number'] ?? $_SESSION['form_data']['license_number'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="license_state" class="form-label">License State *</label>
                                <input type="text" class="form-control" id="license_state" name="license_state" 
                                       value="<?php echo htmlspecialchars($driver['license_state'] ?? $_SESSION['form_data']['license_state'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="license_expiry" class="form-label">License Expiry *</label>
                                <input type="date" class="form-control" id="license_expiry" name="license_expiry" 
                                       value="<?php echo htmlspecialchars($driver['license_expiry'] ?? $_SESSION['form_data']['license_expiry'] ?? ''); ?>" required>
                            </div>

                            <!-- Vehicle Information -->
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_info" class="form-label">Vehicle Information</label>
                                <input type="text" class="form-control" id="vehicle_info" name="vehicle_info" 
                                       value="<?php echo htmlspecialchars($driver['vehicle_info'] ?? $_SESSION['form_data']['vehicle_info'] ?? ''); ?>" 
                                       placeholder="e.g., 2020 Ford F-150 White">
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="offline" <?php echo (($driver['status'] ?? 'offline') == 'offline') ? 'selected' : ''; ?>>Offline</option>
                                    <option value="available" <?php echo (($driver['status'] ?? '') == 'available') ? 'selected' : ''; ?>>Available</option>
                                    <option value="busy" <?php echo (($driver['status'] ?? '') == 'busy') ? 'selected' : ''; ?>>Busy</option>
                                    <option value="on_break" <?php echo (($driver['status'] ?? '') == 'on_break') ? 'selected' : ''; ?>>On Break</option>
                                </select>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($driver['notes'] ?? $_SESSION['form_data']['notes'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo SITE_URL; ?>drivers" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Driver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php unset($_SESSION['form_data']); ?>
