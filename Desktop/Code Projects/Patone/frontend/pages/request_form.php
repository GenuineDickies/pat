<!-- Service Request Form -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Create Service Request</h5>
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

                    <form method="POST" action="<?php echo SITE_URL; ?>requests/add">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="row">
                            <!-- Customer Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="customer_id" class="form-label">Customer *</label>
                                <select class="form-select" id="customer_id" name="customer_id" required>
                                    <option value="">Select Customer...</option>
                                    <?php if (!empty($customers)): ?>
                                        <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>" <?php echo ($customer['id'] == ($_SESSION['form_data']['customer_id'] ?? '')) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name'] . ' (' . $customer['phone'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Service Type -->
                            <div class="col-md-6 mb-3">
                                <label for="service_type_id" class="form-label">Service Type *</label>
                                <select class="form-select" id="service_type_id" name="service_type_id" required>
                                    <option value="">Select Service...</option>
                                    <?php if (!empty($serviceTypes)): ?>
                                        <?php foreach ($serviceTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>" <?php echo ($type['id'] == ($_SESSION['form_data']['service_type_id'] ?? '')) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['name'] . ' - $' . number_format($type['base_price'], 2)); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Priority -->
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>

                            <!-- Driver (Optional) -->
                            <div class="col-md-6 mb-3">
                                <label for="driver_id" class="form-label">Assign Driver (Optional)</label>
                                <select class="form-select" id="driver_id" name="driver_id">
                                    <option value="">Auto-assign later</option>
                                    <?php if (!empty($drivers)): ?>
                                        <?php foreach ($drivers as $driver): ?>
                                        <option value="<?php echo $driver['id']; ?>">
                                            <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Location Information -->
                            <div class="col-md-12 mb-3">
                                <label for="location_address" class="form-label">Location Address *</label>
                                <input type="text" class="form-control" id="location_address" name="location_address" 
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['location_address'] ?? ''); ?>" 
                                       placeholder="Street address" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="location_city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="location_city" name="location_city" 
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['location_city'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="location_state" class="form-label">State *</label>
                                <input type="text" class="form-control" id="location_state" name="location_state" 
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['location_state'] ?? ''); ?>" 
                                       maxlength="2" placeholder="e.g., NY" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="estimated_cost" class="form-label">Estimated Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="estimated_cost" name="estimated_cost" 
                                           value="<?php echo htmlspecialchars($_SESSION['form_data']['estimated_cost'] ?? ''); ?>" 
                                           step="0.01" min="0">
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          placeholder="Additional details about the service request..."><?php echo htmlspecialchars($_SESSION['form_data']['description'] ?? ''); ?></textarea>
                            </div>

                            <!-- Customer Notes -->
                            <div class="col-md-12 mb-3">
                                <label for="customer_notes" class="form-label">Customer Notes</label>
                                <textarea class="form-control" id="customer_notes" name="customer_notes" rows="2" 
                                          placeholder="Notes from customer..."><?php echo htmlspecialchars($_SESSION['form_data']['customer_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo SITE_URL; ?>requests" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php unset($_SESSION['form_data']); ?>
