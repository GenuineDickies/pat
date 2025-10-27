// Roadside Assistance Admin Platform - Application JavaScript
// Specific functionality for the roadside assistance system

// Customer management functionality
const CustomerManager = {
    // Initialize customer management
    init: function() {
        this.initSearch();
        this.initFilters();
        this.initBulkActions();
        this.initImportExport();
    },

    // Initialize search functionality
    initSearch: function() {
        const searchInput = document.getElementById('searchFilter');
        if (searchInput) {
            const debouncedSearch = RoadsideApp.utils.debounce(function() {
                this.refreshTable();
            }.bind(this), 500);

            searchInput.addEventListener('input', debouncedSearch);
        }
    },

    // Initialize filters
    initFilters: function() {
        const filters = ['statusFilter', 'stateFilter', 'dateFromFilter', 'dateToFilter'];
        filters.forEach(filterId => {
            const filter = document.getElementById(filterId);
            if (filter) {
                filter.addEventListener('change', () => this.refreshTable());
            }
        });
    },

    // Initialize bulk actions
    initBulkActions: function() {
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.customer-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }
    },

    // Initialize import/export functionality
    initImportExport: function() {
        // Import functionality
        const importBtn = document.querySelector('[data-bs-toggle="modal"][data-bs-target="#importModal"]');
        if (importBtn) {
            importBtn.addEventListener('click', () => this.resetImportForm());
        }
    },

    // Refresh customers table
    refreshTable: function() {
        if (window.customersTable) {
            window.customersTable.ajax.reload();
        }
    },

    // Reset import form
    resetImportForm: function() {
        const importForm = document.getElementById('importForm');
        if (importForm) {
            importForm.reset();
        }
    },

    // Get selected customer IDs
    getSelectedIds: function() {
        return Array.from(document.querySelectorAll('.customer-checkbox:checked'))
            .map(checkbox => checkbox.value);
    },

    // Delete selected customers
    deleteSelected: function() {
        const selectedIds = this.getSelectedIds();
        if (selectedIds.length === 0) {
            RoadsideApp.utils.showNotification('Please select customers to delete', 'warning');
            return;
        }

        RoadsideApp.utils.confirm(
            `Delete ${selectedIds.length} selected customers? This action cannot be undone.`,
            () => {
                RoadsideApp.utils.showLoading('Delete Selected');
                // Implement bulk delete API call
                console.log('Deleting customers:', selectedIds);
                RoadsideApp.utils.showNotification('Customers deleted successfully', 'success');
            }
        );
    },

    // Export customers
    exportCustomers: function() {
        const selectedIds = this.getSelectedIds();
        const url = selectedIds.length > 0
            ? `${window.location.origin}/api/customers/export?ids=${selectedIds.join(',')}`
            : `${window.location.origin}/api/customers/export`;

        window.location.href = url;
    }
};

// Service request management functionality
const RequestManager = {
    // Initialize request management
    init: function() {
        this.initMap();
        this.initLocationServices();
        this.initAutoAssignment();
    },

    // Initialize map for location selection
    initMap: function() {
        // Check if Google Maps API is available
        if (typeof google !== 'undefined') {
            this.map = new google.maps.Map(document.getElementById('locationMap'), {
                center: { lat: 40.7128, lng: -74.0060 }, // Default to NYC
                zoom: 12
            });

            this.marker = new google.maps.Marker({
                position: this.map.getCenter(),
                map: this.map,
                draggable: true
            });
        }
    },

    // Initialize location services
    initLocationServices: function() {
        const locationInput = document.getElementById('location');
        if (locationInput) {
            // Auto-complete for addresses
            if (typeof google !== 'undefined' && google.maps.places) {
                const autocomplete = new google.maps.places.Autocomplete(locationInput);
                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (place.geometry) {
                        this.updateLocation(place.geometry.location.lat(), place.geometry.location.lng());
                    }
                });
            }
        }
    },

    // Update location coordinates
    updateLocation: function(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (this.marker) {
            this.marker.setPosition({ lat, lng });
            this.map.setCenter({ lat, lng });
        }
    },

    // Initialize auto-assignment
    initAutoAssignment: function() {
        const autoAssignToggle = document.getElementById('autoAssign');
        if (autoAssignToggle) {
            autoAssignToggle.addEventListener('change', function() {
                // Update setting via API
                RoadsideApp.api.request('settings/auto-assign', 'POST', {
                    enabled: this.checked
                });
            });
        }
    },

    // Assign driver to request
    assignDriver: function(requestId, driverId) {
        RoadsideApp.api.request(`requests/${requestId}/assign`, 'POST', {
            driver_id: driverId
        }).then(response => {
            if (response.success) {
                RoadsideApp.utils.showNotification('Driver assigned successfully', 'success');
                this.refreshRequests();
            } else {
                RoadsideApp.utils.showNotification('Failed to assign driver', 'error');
            }
        }).catch(error => {
            RoadsideApp.utils.showNotification('Error assigning driver', 'error');
        });
    },

    // Refresh requests list
    refreshRequests: function() {
        if (window.requestsTable) {
            window.requestsTable.ajax.reload();
        }
    }
};

// Driver management functionality
const DriverManager = {
    // Initialize driver management
    init: function() {
        this.initLocationTracking();
        this.initStatusUpdates();
        this.initAvailabilityToggle();
    },

    // Initialize location tracking
    initLocationTracking: function() {
        if (navigator.geolocation) {
            const trackBtn = document.getElementById('trackLocation');
            if (trackBtn) {
                trackBtn.addEventListener('click', () => {
                    RoadsideApp.utils.showLoading(trackBtn);

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.updateDriverLocation(
                                position.coords.latitude,
                                position.coords.longitude
                            );
                            RoadsideApp.utils.hideLoading(trackBtn);
                            RoadsideApp.utils.showNotification('Location updated', 'success');
                        },
                        (error) => {
                            RoadsideApp.utils.hideLoading(trackBtn);
                            RoadsideApp.utils.showNotification('Failed to get location', 'error');
                        }
                    );
                });
            }
        }
    },

    // Update driver location
    updateDriverLocation: function(lat, lng) {
        const driverId = document.getElementById('driverId').value;

        RoadsideApp.api.request(`drivers/${driverId}/location`, 'POST', {
            latitude: lat,
            longitude: lng
        }).then(response => {
            if (response.success) {
                document.getElementById('currentLatitude').value = lat;
                document.getElementById('currentLongitude').value = lng;
            }
        });
    },

    // Initialize status updates
    initStatusUpdates: function() {
        const statusSelect = document.getElementById('driverStatus');
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                const driverId = document.getElementById('driverId').value;
                const status = this.value;

                RoadsideApp.api.request(`drivers/${driverId}/status`, 'POST', {
                    status: status
                }).then(response => {
                    if (response.success) {
                        RoadsideApp.utils.showNotification('Status updated', 'success');
                        this.refreshDrivers();
                    } else {
                        RoadsideApp.utils.showNotification('Failed to update status', 'error');
                    }
                });
            }.bind(this));
        }
    },

    // Initialize availability toggle
    initAvailabilityToggle: function() {
        const availabilityBtns = document.querySelectorAll('.availability-toggle');
        availabilityBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const driverId = this.dataset.driverId;
                const currentStatus = this.dataset.status;
                const newStatus = currentStatus === 'available' ? 'offline' : 'available';

                RoadsideApp.api.request(`drivers/${driverId}/availability`, 'POST', {
                    status: newStatus
                }).then(response => {
                    if (response.success) {
                        this.dataset.status = newStatus;
                        this.innerHTML = `<i class="bi bi-${newStatus === 'available' ? 'check-circle' : 'pause-circle'}"></i> ${newStatus}`;
                        this.className = `btn btn-sm btn-${newStatus === 'available' ? 'success' : 'secondary'} availability-toggle`;
                        RoadsideApp.utils.showNotification(`Driver ${newStatus}`, 'success');
                    } else {
                        RoadsideApp.utils.showNotification('Failed to update availability', 'error');
                    }
                }.bind(this));
            });
        });
    },

    // Refresh drivers list
    refreshDrivers: function() {
        if (window.driversTable) {
            window.driversTable.ajax.reload();
        }
    }
};

// Dashboard functionality
const DashboardManager = {
    // Initialize dashboard
    init: function() {
        this.initRealTimeUpdates();
        this.initQuickActions();
        this.initCharts();
    },

    // Initialize real-time updates
    initRealTimeUpdates: function() {
        // Update dashboard stats every 30 seconds
        setInterval(() => {
            this.refreshStats();
        }, 30000);

        // Update recent requests every 60 seconds
        setInterval(() => {
            this.refreshRecentRequests();
        }, 60000);
    },

    // Refresh dashboard statistics
    refreshStats: function() {
        RoadsideApp.api.dashboard.getStats().then(data => {
            if (data.success) {
                this.updateStatsDisplay(data.data);
            }
        }).catch(error => {
            console.error('Failed to refresh dashboard stats:', error);
        });
    },

    // Update stats display
    updateStatsDisplay: function(stats) {
        const elements = {
            'activeRequests': stats.active_requests,
            'completedToday': stats.completed_today,
            'availableDrivers': stats.available_drivers,
            'totalCustomers': stats.total_customers,
            'revenueToday': RoadsideApp.utils.formatCurrency(stats.revenue_today),
            'avgResponseTime': stats.avg_response_time + ' min'
        };

        Object.keys(elements).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.textContent = elements[key];
            }
        });
    },

    // Refresh recent requests
    refreshRecentRequests: function() {
        RoadsideApp.api.dashboard.getRecentRequests().then(data => {
            if (data.success) {
                this.updateRecentRequests(data.data);
            }
        }).catch(error => {
            console.error('Failed to refresh recent requests:', error);
        });
    },

    // Update recent requests display
    updateRecentRequests: function(requests) {
        const tbody = document.querySelector('#recentRequestsTable tbody');
        if (tbody) {
            tbody.innerHTML = requests.map(request => `
                <tr>
                    <td><strong>#${request.id}</strong></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                ${request.customer_name.charAt(0)}
                            </div>
                            <div>
                                <div class="fw-medium">${request.customer_name}</div>
                                <small class="text-muted">${request.customer_phone}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${request.service_type}</span>
                    </td>
                    <td>
                        <small class="text-muted">${request.location.substring(0, 30)}...</small>
                    </td>
                    <td>
                        <span class="badge ${RoadsideApp.utils.getStatusBadgeClass(request.status)}">
                            ${request.status.charAt(0).toUpperCase() + request.status.slice(1)}
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">${RoadsideApp.utils.formatDateTime(request.created_at)}</small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="/requests/${request.id}" class="btn btn-outline-primary btn-sm" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    },

    // Initialize quick actions
    initQuickActions: function() {
        const quickActionBtns = document.querySelectorAll('.quick-action');
        quickActionBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.dataset.action;
                this.performQuickAction(action);
            }.bind(this));
        });
    },

    // Perform quick action
    performQuickAction: function(action) {
        switch (action) {
            case 'new-request':
                window.location.href = '/requests/add';
                break;
            case 'new-customer':
                window.location.href = '/customers/add';
                break;
            case 'new-driver':
                window.location.href = '/drivers/add';
                break;
            case 'generate-report':
                window.location.href = '/reports';
                break;
            default:
                console.log('Unknown quick action:', action);
        }
    },

    // Initialize charts
    initCharts: function() {
        // Initialize charts if Chart.js is available
        if (typeof Chart !== 'undefined') {
            this.initRequestChart();
            this.initRevenueChart();
        }
    },

    // Initialize request status chart
    initRequestChart: function() {
        const ctx = document.getElementById('requestChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'In Progress', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: [12, 19, 8, 3], // Sample data
                        backgroundColor: [
                            '#ffc107',
                            '#007bff',
                            '#28a745',
                            '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    },

    // Initialize revenue chart
    initRevenueChart: function() {
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [1200, 1900, 800, 1500, 2200, 1800, 900],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
};

// Initialize all modules when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize based on current page
    const currentPath = window.location.pathname;

    if (currentPath.includes('/customers')) {
        CustomerManager.init();
    } else if (currentPath.includes('/requests')) {
        RequestManager.init();
    } else if (currentPath.includes('/drivers')) {
        DriverManager.init();
    } else if (currentPath.includes('/dashboard')) {
        DashboardManager.init();
    }
});

// Global error handler
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
    RoadsideApp.utils.showNotification('An error occurred. Please try again.', 'error');
});

// Global unhandled promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    RoadsideApp.utils.showNotification('An error occurred. Please try again.', 'error');
});
