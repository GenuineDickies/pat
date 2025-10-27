<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-<?php echo isset($customer) ? 'pencil' : 'person-plus'; ?>"></i>
                        <?php echo isset($customer) ? 'Edit Customer' : 'Add New Customer'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h6>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" id="customerForm">
                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>

                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                           value="<?php echo isset($customer) ? htmlspecialchars($customer['first_name']) : ''; ?>"
                                           required maxlength="50">
                                </div>

                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                           value="<?php echo isset($customer) ? htmlspecialchars($customer['last_name']) : ''; ?>"
                                           required maxlength="50">
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo isset($customer) ? htmlspecialchars($customer['email']) : ''; ?>"
                                           required maxlength="100">
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?php echo isset($customer) ? htmlspecialchars($customer['phone']) : ''; ?>"
                                           required maxlength="20" placeholder="(555) 123-4567">
                                </div>

                                <div class="mb-3">
                                    <label for="emergency_contact" class="form-label">Emergency Contact</label>
                                    <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact"
                                           value="<?php echo isset($customer) ? htmlspecialchars($customer['emergency_contact']) : ''; ?>"
                                           maxlength="20" placeholder="(555) 987-6543">
                                </div>

                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                           value="<?php echo isset($customer) ? htmlspecialchars($customer['date_of_birth']) : ''; ?>">
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_vip" name="is_vip"
                                               <?php echo (isset($customer) && $customer['is_vip']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_vip">
                                            <strong>VIP Customer</strong>
                                            <small class="text-muted d-block">Priority service and special rates</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Address Information</h5>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Street Address *</label>
                                    <input type="text" class="form-control" id="address" name="address"
                                           value="<?php echo isset($customer) ? htmlspecialchars($customer['address']) : ''; ?>"
                                           required maxlength="100">
                                </div>

                                <div class="mb-3">
                                    <label for="address2" class="form-label">Address Line 2</label>
                                    <input type="text" class="form-control" id="address2" name="address2"
                                           value="<?php echo isset($customer) ? htmlspecialchars($customer['address2']) : ''; ?>"
                                           maxlength="100" placeholder="Apartment, suite, etc.">
                                </div>

                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label for="city" class="form-label">City *</label>
                                            <input type="text" class="form-control" id="city" name="city"
                                                   value="<?php echo isset($customer) ? htmlspecialchars($customer['city']) : ''; ?>"
                                                   required maxlength="50">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="state" class="form-label">State *</label>
                                            <select class="form-select" id="state" name="state" required>
                                                <option value="">Select State</option>
                                                <option value="AL" <?php echo (isset($customer) && $customer['state'] == 'AL') ? 'selected' : ''; ?>>Alabama</option>
                                                <option value="AK" <?php echo (isset($customer) && $customer['state'] == 'AK') ? 'selected' : ''; ?>>Alaska</option>
                                                <option value="AZ" <?php echo (isset($customer) && $customer['state'] == 'AZ') ? 'selected' : ''; ?>>Arizona</option>
                                                <option value="AR" <?php echo (isset($customer) && $customer['state'] == 'AR') ? 'selected' : ''; ?>>Arkansas</option>
                                                <option value="CA" <?php echo (isset($customer) && $customer['state'] == 'CA') ? 'selected' : ''; ?>>California</option>
                                                <option value="CO" <?php echo (isset($customer) && $customer['state'] == 'CO') ? 'selected' : ''; ?>>Colorado</option>
                                                <option value="CT" <?php echo (isset($customer) && $customer['state'] == 'CT') ? 'selected' : ''; ?>>Connecticut</option>
                                                <option value="DE" <?php echo (isset($customer) && $customer['state'] == 'DE') ? 'selected' : ''; ?>>Delaware</option>
                                                <option value="FL" <?php echo (isset($customer) && $customer['state'] == 'FL') ? 'selected' : ''; ?>>Florida</option>
                                                <option value="GA" <?php echo (isset($customer) && $customer['state'] == 'GA') ? 'selected' : ''; ?>>Georgia</option>
                                                <option value="HI" <?php echo (isset($customer) && $customer['state'] == 'HI') ? 'selected' : ''; ?>>Hawaii</option>
                                                <option value="ID" <?php echo (isset($customer) && $customer['state'] == 'ID') ? 'selected' : ''; ?>>Idaho</option>
                                                <option value="IL" <?php echo (isset($customer) && $customer['state'] == 'IL') ? 'selected' : ''; ?>>Illinois</option>
                                                <option value="IN" <?php echo (isset($customer) && $customer['state'] == 'IN') ? 'selected' : ''; ?>>Indiana</option>
                                                <option value="IA" <?php echo (isset($customer) && $customer['state'] == 'IA') ? 'selected' : ''; ?>>Iowa</option>
                                                <option value="KS" <?php echo (isset($customer) && $customer['state'] == 'KS') ? 'selected' : ''; ?>>Kansas</option>
                                                <option value="KY" <?php echo (isset($customer) && $customer['state'] == 'KY') ? 'selected' : ''; ?>>Kentucky</option>
                                                <option value="LA" <?php echo (isset($customer) && $customer['state'] == 'LA') ? 'selected' : ''; ?>>Louisiana</option>
                                                <option value="ME" <?php echo (isset($customer) && $customer['state'] == 'ME') ? 'selected' : ''; ?>>Maine</option>
                                                <option value="MD" <?php echo (isset($customer) && $customer['state'] == 'MD') ? 'selected' : ''; ?>>Maryland</option>
                                                <option value="MA" <?php echo (isset($customer) && $customer['state'] == 'MA') ? 'selected' : ''; ?>>Massachusetts</option>
                                                <option value="MI" <?php echo (isset($customer) && $customer['state'] == 'MI') ? 'selected' : ''; ?>>Michigan</option>
                                                <option value="MN" <?php echo (isset($customer) && $customer['state'] == 'MN') ? 'selected' : ''; ?>>Minnesota</option>
                                                <option value="MS" <?php echo (isset($customer) && $customer['state'] == 'MS') ? 'selected' : ''; ?>>Mississippi</option>
                                                <option value="MO" <?php echo (isset($customer) && $customer['state'] == 'MO') ? 'selected' : ''; ?>>Missouri</option>
                                                <option value="MT" <?php echo (isset($customer) && $customer['state'] == 'MT') ? 'selected' : ''; ?>>Montana</option>
                                                <option value="NE" <?php echo (isset($customer) && $customer['state'] == 'NE') ? 'selected' : ''; ?>>Nebraska</option>
                                                <option value="NV" <?php echo (isset($customer) && $customer['state'] == 'NV') ? 'selected' : ''; ?>>Nevada</option>
                                                <option value="NH" <?php echo (isset($customer) && $customer['state'] == 'NH') ? 'selected' : ''; ?>>New Hampshire</option>
                                                <option value="NJ" <?php echo (isset($customer) && $customer['state'] == 'NJ') ? 'selected' : ''; ?>>New Jersey</option>
                                                <option value="NM" <?php echo (isset($customer) && $customer['state'] == 'NM') ? 'selected' : ''; ?>>New Mexico</option>
                                                <option value="NY" <?php echo (isset($customer) && $customer['state'] == 'NY') ? 'selected' : ''; ?>>New York</option>
                                                <option value="NC" <?php echo (isset($customer) && $customer['state'] == 'NC') ? 'selected' : ''; ?>>North Carolina</option>
                                                <option value="ND" <?php echo (isset($customer) && $customer['state'] == 'ND') ? 'selected' : ''; ?>>North Dakota</option>
                                                <option value="OH" <?php echo (isset($customer) && $customer['state'] == 'OH') ? 'selected' : ''; ?>>Ohio</option>
                                                <option value="OK" <?php echo (isset($customer) && $customer['state'] == 'OK') ? 'selected' : ''; ?>>Oklahoma</option>
                                                <option value="OR" <?php echo (isset($customer) && $customer['state'] == 'OR') ? 'selected' : ''; ?>>Oregon</option>
                                                <option value="PA" <?php echo (isset($customer) && $customer['state'] == 'PA') ? 'selected' : ''; ?>>Pennsylvania</option>
                                                <option value="RI" <?php echo (isset($customer) && $customer['state'] == 'RI') ? 'selected' : ''; ?>>Rhode Island</option>
                                                <option value="SC" <?php echo (isset($customer) && $customer['state'] == 'SC') ? 'selected' : ''; ?>>South Carolina</option>
                                                <option value="SD" <?php echo (isset($customer) && $customer['state'] == 'SD') ? 'selected' : ''; ?>>South Dakota</option>
                                                <option value="TN" <?php echo (isset($customer) && $customer['state'] == 'TN') ? 'selected' : ''; ?>>Tennessee</option>
                                                <option value="TX" <?php echo (isset($customer) && $customer['state'] == 'TX') ? 'selected' : ''; ?>>Texas</option>
                                                <option value="UT" <?php echo (isset($customer) && $customer['state'] == 'UT') ? 'selected' : ''; ?>>Utah</option>
                                                <option value="VT" <?php echo (isset($customer) && $customer['state'] == 'VT') ? 'selected' : ''; ?>>Vermont</option>
                                                <option value="VA" <?php echo (isset($customer) && $customer['state'] == 'VA') ? 'selected' : ''; ?>>Virginia</option>
                                                <option value="WA" <?php echo (isset($customer) && $customer['state'] == 'WA') ? 'selected' : ''; ?>>Washington</option>
                                                <option value="WV" <?php echo (isset($customer) && $customer['state'] == 'WV') ? 'selected' : ''; ?>>West Virginia</option>
                                                <option value="WI" <?php echo (isset($customer) && $customer['state'] == 'WI') ? 'selected' : ''; ?>>Wisconsin</option>
                                                <option value="WY" <?php echo (isset($customer) && $customer['state'] == 'WY') ? 'selected' : ''; ?>>Wyoming</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="zip" class="form-label">ZIP Code *</label>
                                            <input type="text" class="form-control" id="zip" name="zip"
                                                   value="<?php echo isset($customer) ? htmlspecialchars($customer['zip']) : ''; ?>"
                                                   required maxlength="10" pattern="[0-9]{5}(-[0-9]{4})?">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Information -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Vehicle Information</h5>
                                <div id="vehicleContainer">
                                    <?php if (isset($customer['vehicles']) && !empty($customer['vehicles'])): ?>
                                        <?php foreach ($customer['vehicles'] as $index => $vehicle): ?>
                                        <div class="vehicle-entry card mb-3">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Vehicle <?php echo $index + 1; ?></h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVehicle(this)">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Make</label>
                                                            <input type="text" class="form-control" name="vehicles[<?php echo $index; ?>][make]"
                                                                   value="<?php echo htmlspecialchars($vehicle['make']); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Model</label>
                                                            <input type="text" class="form-control" name="vehicles[<?php echo $index; ?>][model]"
                                                                   value="<?php echo htmlspecialchars($vehicle['model']); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label">Year</label>
                                                            <input type="number" class="form-control" name="vehicles[<?php echo $index; ?>][year]"
                                                                   value="<?php echo htmlspecialchars($vehicle['year']); ?>" min="1900" max="<?php echo date('Y') + 1; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label">Color</label>
                                                            <input type="text" class="form-control" name="vehicles[<?php echo $index; ?>][color]"
                                                                   value="<?php echo htmlspecialchars($vehicle['color']); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label">License Plate</label>
                                                            <input type="text" class="form-control" name="vehicles[<?php echo $index; ?>][license_plate]"
                                                                   value="<?php echo htmlspecialchars($vehicle['license_plate']); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                    <div class="vehicle-entry card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">Vehicle 1</h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVehicle(this)">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Make</label>
                                                        <input type="text" class="form-control" name="vehicles[0][make]" placeholder="e.g. Toyota">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Model</label>
                                                        <input type="text" class="form-control" name="vehicles[0][model]" placeholder="e.g. Camry">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mb-3">
                                                        <label class="form-label">Year</label>
                                                        <input type="number" class="form-control" name="vehicles[0][year]" min="1900" max="<?php echo date('Y') + 1; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mb-3">
                                                        <label class="form-label">Color</label>
                                                        <input type="text" class="form-control" name="vehicles[0][color]" placeholder="e.g. Blue">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mb-3">
                                                        <label class="form-label">License Plate</label>
                                                        <input type="text" class="form-control" name="vehicles[0][license_plate]" placeholder="ABC-123">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <button type="button" class="btn btn-outline-primary" onclick="addVehicle()">
                                    <i class="bi bi-plus-circle"></i> Add Another Vehicle
                                </button>
                            </div>
                        </div>

                        <!-- Notes and Status -->
                        <div class="row mt-4">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4"
                                              placeholder="Any additional notes or special instructions..."><?php echo isset($customer) ? htmlspecialchars($customer['notes']) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo (isset($customer) && $customer['status'] == 'active') ? 'selected' : 'selected'; ?>>Active</option>
                                        <option value="inactive" <?php echo (isset($customer) && $customer['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="suspended" <?php echo (isset($customer) && $customer['status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo SITE_URL; ?>customers" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to Customers
                                    </a>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i>
                                            <?php echo isset($customer) ? 'Update Customer' : 'Create Customer'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let vehicleCount = <?php echo isset($customer['vehicles']) ? count($customer['vehicles']) : 1; ?>;

function addVehicle() {
    const container = document.getElementById('vehicleContainer');
    const vehicleEntry = document.createElement('div');
    vehicleEntry.className = 'vehicle-entry card mb-3';
    vehicleEntry.innerHTML = `
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Vehicle ${vehicleCount + 1}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVehicle(this)">
                <i class="bi bi-trash"></i> Remove
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Make</label>
                        <input type="text" class="form-control" name="vehicles[${vehicleCount}][make]" placeholder="e.g. Toyota">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <input type="text" class="form-control" name="vehicles[${vehicleCount}][model]" placeholder="e.g. Camry">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" class="form-control" name="vehicles[${vehicleCount}][year]" min="1900" max="${new Date().getFullYear() + 1}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" name="vehicles[${vehicleCount}][color]" placeholder="e.g. Blue">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">License Plate</label>
                        <input type="text" class="form-control" name="vehicles[${vehicleCount}][license_plate]" placeholder="ABC-123">
                    </div>
                </div>
            </div>
        </div>
    `;
    container.appendChild(vehicleEntry);
    vehicleCount++;
}

function removeVehicle(button) {
    if (document.querySelectorAll('.vehicle-entry').length > 1) {
        button.closest('.vehicle-entry').remove();
        updateVehicleNumbers();
    } else {
        alert('At least one vehicle must be specified.');
    }
}

function updateVehicleNumbers() {
    const vehicles = document.querySelectorAll('.vehicle-entry');
    vehicles.forEach((vehicle, index) => {
        const header = vehicle.querySelector('h6');
        header.textContent = `Vehicle ${index + 1}`;

        const inputs = vehicle.querySelectorAll('input');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('vehicles[')) {
                input.setAttribute('name', `vehicles[${index}][${name.split('[')[2].split(']')[0]}]`);
            }
        });
    });
    vehicleCount = vehicles.length;
}

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 10) {
        if (value.length > 6) {
            value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6)}`;
        } else if (value.length > 3) {
            value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
        } else if (value.length > 0) {
            value = `(${value}`;
        }
        e.target.value = value;
    }
});

document.getElementById('emergency_contact').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 10) {
        if (value.length > 6) {
            value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6)}`;
        } else if (value.length > 3) {
            value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
        } else if (value.length > 0) {
            value = `(${value}`;
        }
        e.target.value = value;
    }
});

// Form validation
document.getElementById('customerForm').addEventListener('submit', function(e) {
    const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip'];
    let isValid = true;

    requiredFields.forEach(field => {
        const element = document.getElementById(field);
        if (!element.value.trim()) {
            element.classList.add('is-invalid');
            isValid = false;
        } else {
            element.classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});
</script>
