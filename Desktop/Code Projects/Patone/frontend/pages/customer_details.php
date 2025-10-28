<div class="container-fluid">
    <!-- Customer Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 80px; height: 80px; font-size: 32px;">
                                <?php echo strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3 class="mb-1">
                                    <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                    <?php if ($customer['is_vip']): ?>
                                        <span class="badge bg-warning text-dark ms-2">
                                            <i class="bi bi-star-fill"></i> VIP
                                        </span>
                                    <?php endif; ?>
                                </h3>
                                <div class="text-muted">
                                    <span class="badge <?php echo getStatusBadgeClass($customer['status']); ?>">
                                        <?php echo ucfirst($customer['status']); ?>
                                    </span>
                                    <span class="ms-2">Customer ID: #<?php echo $customer['id']; ?></span>
                                </div>
                                <small class="text-muted">
                                    Member since <?php echo formatDate($customer['created_at']); ?>
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="<?php echo SITE_URL; ?>customers/edit/<?php echo $customer['id']; ?>" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Customer
                            </a>
                            <a href="<?php echo SITE_URL; ?>customers" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information Tabs -->
    <div class="row">
        <div class="col-lg-4">
            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Email</label>
                        <div>
                            <i class="bi bi-envelope"></i>
                            <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>">
                                <?php echo htmlspecialchars($customer['email']); ?>
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Phone</label>
                        <div>
                            <i class="bi bi-telephone"></i>
                            <a href="tel:<?php echo $customer['phone']; ?>">
                                <?php echo formatPhoneNumber($customer['phone']); ?>
                            </a>
                        </div>
                    </div>
                    <?php if ($customer['emergency_contact']): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Emergency Contact</label>
                        <div>
                            <i class="bi bi-telephone-forward"></i>
                            <a href="tel:<?php echo $customer['emergency_contact']; ?>">
                                <?php echo formatPhoneNumber($customer['emergency_contact']); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($customer['date_of_birth']): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Date of Birth</label>
                        <div>
                            <i class="bi bi-calendar"></i>
                            <?php echo formatDate($customer['date_of_birth']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Address Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Address</h5>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        <?php echo htmlspecialchars($customer['address']); ?><br>
                        <?php if ($customer['address2']): ?>
                            <?php echo htmlspecialchars($customer['address2']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($customer['city'] . ', ' . $customer['state'] . ' ' . $customer['zip']); ?>
                    </address>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($customer['address'] . ' ' . $customer['city'] . ' ' . $customer['state'] . ' ' . $customer['zip']); ?>" 
                       target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="bi bi-map"></i> View on Map
                    </a>
                </div>
            </div>

            <!-- Notes -->
            <?php if ($customer['notes']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-sticky"></i> Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($customer['notes'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-8">
            <!-- Vehicles -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-car-front"></i> Vehicles (<?php echo count($vehicles); ?>)</h5>
                    <a href="<?php echo SITE_URL; ?>customers/edit/<?php echo $customer['id']; ?>#vehicles" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus"></i> Add Vehicle
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($vehicles)): ?>
                        <div class="row">
                            <?php foreach ($vehicles as $vehicle): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>
                                        </h6>
                                        <div class="mb-2">
                                            <?php if ($vehicle['color']): ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($vehicle['color']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($vehicle['license_plate']): ?>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($vehicle['license_plate']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">
                            <i class="bi bi-car-front fs-1 d-block mb-2"></i>
                            No vehicles registered
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Service History -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Service History</h5>
                    <a href="<?php echo SITE_URL; ?>requests/add?customer_id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-plus"></i> New Service Request
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($serviceHistory)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Service Type</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Cost</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($serviceHistory as $request): ?>
                                    <tr>
                                        <td>#<?php echo $request['id']; ?></td>
                                        <td><?php echo htmlspecialchars($request['service_type_id']); ?></td>
                                        <td><?php echo formatDate($request['created_at']); ?></td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($request['status']); ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($request['final_cost']): ?>
                                                $<?php echo number_format($request['final_cost'], 2); ?>
                                            <?php elseif ($request['estimated_cost']): ?>
                                                ~$<?php echo number_format($request['estimated_cost'], 2); ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>requests/<?php echo $request['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No service history available
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add any page-specific JavaScript here
$(document).ready(function() {
    console.log('Customer detail page loaded');
});
</script>
