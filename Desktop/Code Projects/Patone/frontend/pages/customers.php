<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Customer Management</h4>
                    <small class="text-muted">Manage customer information and service history</small>
                </div>
                <div class="btn-group">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload"></i> Import
                    </button>
                    <button class="btn btn-info" onclick="exportCustomers()">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <a href="<?php echo SITE_URL; ?>customers/add" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="searchFilter" class="form-label">Search</label>
                            <input type="text" class="form-control" id="searchFilter" placeholder="Search by name, email, or phone">
                        </div>
                        <div class="col-md-2">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="vip">VIP</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="stateFilter" class="form-label">State</label>
                            <select class="form-select" id="stateFilter">
                                <option value="">All States</option>
                                <option value="AL">Alabama</option>
                                <option value="AK">Alaska</option>
                                <option value="AZ">Arizona</option>
                                <!-- Add more states as needed -->
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="dateFromFilter" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="dateFromFilter">
                        </div>
                        <div class="col-md-2">
                            <label for="dateToFilter" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="dateToFilter">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button class="btn btn-outline-primary w-100" onclick="applyFilters()">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Customers (<?php echo isset($totalCustomers) ? $totalCustomers : '0'; ?>)</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshTable()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear"></i> Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="bulkDelete()">Delete Selected</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkExport()">Export Selected</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="sendBulkSMS()">Send SMS</a></li>
                                <li><a class="dropdown-item" href="#" onclick="sendBulkEmail()">Send Email</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="customersTable">
                            <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                                    <th>Customer</th>
                                    <th>Contact Info</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Requests</th>
                                    <th>Last Service</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($customers) && !empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><input type="checkbox" class="customer-checkbox" value="<?php echo $customer['id']; ?>"></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                     style="width: 40px; height: 40px; font-size: 16px;">
                                                    <?php echo strtoupper(substr($customer['first_name'] . ' ' . $customer['last_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-medium">
                                                        <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                                        <?php if ($customer['is_vip']): ?>
                                                            <i class="bi bi-star-fill text-warning ms-1" title="VIP Customer"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">ID: <?php echo $customer['id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div><i class="bi bi-envelope text-muted"></i> <?php echo htmlspecialchars($customer['email']); ?></div>
                                            <div><i class="bi bi-telephone text-muted"></i> <?php echo formatPhoneNumber($customer['phone']); ?></div>
                                            <?php if ($customer['emergency_contact']): ?>
                                                <div><small class="text-muted">Emergency: <?php echo formatPhoneNumber($customer['emergency_contact']); ?></small></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($customer['address']); ?></div>
                                            <div><small class="text-muted">
                                                <?php echo htmlspecialchars($customer['city'] . ', ' . $customer['state'] . ' ' . $customer['zip']); ?>
                                            </small></div>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($customer['status']); ?>">
                                                <?php echo ucfirst($customer['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $customer['total_requests'] ?? '0'; ?> requests
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($customer['last_service_date']): ?>
                                                <small><?php echo formatDate($customer['last_service_date']); ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">No services</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo SITE_URL; ?>customers/<?php echo $customer['id']; ?>"
                                                   class="btn btn-outline-primary btn-sm" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?php echo SITE_URL; ?>customers/edit/<?php echo $customer['id']; ?>"
                                                   class="btn btn-outline-secondary btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-outline-info btn-sm" title="Service History"
                                                        onclick="viewServiceHistory(<?php echo $customer['id']; ?>)">
                                                    <i class="bi bi-clock-history"></i>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="sendSMS(<?php echo $customer['id']; ?>)">Send SMS</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="sendEmail(<?php echo $customer['id']; ?>)">Send Email</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteCustomer(<?php echo $customer['id']; ?>)">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                                            No customers found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Customers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" id="importFile" name="importFile" accept=".csv" required>
                        <div class="form-text">Upload a CSV file with customer data</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="skipDuplicates" name="skipDuplicates" checked>
                            <label class="form-check-label" for="skipDuplicates">
                                Skip duplicate records
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="importCustomers()">Import</button>
            </div>
        </div>
    </div>
</div>

<script>
let customersTable;

// Initialize DataTable
$(document).ready(function() {
    customersTable = $('#customersTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            { targets: [0, 7], orderable: false },
            { targets: 0, width: '30px' }
        ]
    });

    // Apply search filter
    $('#searchFilter').on('keyup', function() {
        customersTable.search($(this).val()).draw();
    });
});

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.customer-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function applyFilters() {
    // In a real application, this would apply server-side filters
    const filters = {
        search: $('#searchFilter').val(),
        status: $('#statusFilter').val(),
        state: $('#stateFilter').val(),
        dateFrom: $('#dateFromFilter').val(),
        dateTo: $('#dateToFilter').val()
    };

    // Refresh table with filters
    customersTable.ajax.reload();
}

function refreshTable() {
    customersTable.ajax.reload();
}

function exportCustomers() {
    const selectedIds = getSelectedCustomerIds();
    if (selectedIds.length > 0) {
        window.location.href = `<?php echo SITE_URL; ?>api/customers/export?ids=${selectedIds.join(',')}`;
    } else {
        window.location.href = '<?php echo SITE_URL; ?>api/customers/export';
    }
}

function getSelectedCustomerIds() {
    return Array.from(document.querySelectorAll('.customer-checkbox:checked')).map(cb => cb.value);
}

function bulkDelete() {
    const selectedIds = getSelectedCustomerIds();
    if (selectedIds.length === 0) {
        alert('Please select customers to delete');
        return;
    }

    if (confirm(`Delete ${selectedIds.length} selected customers?`)) {
        // Implement bulk delete
        console.log('Deleting customers:', selectedIds);
    }
}

function viewServiceHistory(customerId) {
    // Implement service history modal
    console.log('View service history for customer:', customerId);
}

function deleteCustomer(customerId) {
    if (confirm('Are you sure you want to delete this customer?')) {
        window.location.href = `<?php echo SITE_URL; ?>customers/delete/${customerId}`;
    }
}

function importCustomers() {
    const formData = new FormData(document.getElementById('importForm'));

    // Implement import functionality
    console.log('Importing customers...');
    $('#importModal').modal('hide');
}

function sendSMS(customerId) {
    // Implement SMS functionality
    console.log('Send SMS to customer:', customerId);
}

function sendEmail(customerId) {
    // Implement email functionality
    console.log('Send email to customer:', customerId);
}
</script>
