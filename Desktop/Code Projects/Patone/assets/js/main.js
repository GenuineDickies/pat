// Roadside Assistance Admin Platform - Main JavaScript
// Common functionality and utilities

// Global application object
window.RoadsideApp = {
    // API endpoints
    api: {
        baseUrl: window.location.origin + '/api/',

        // Generic API request method
        request: function(endpoint, method = 'GET', data = null) {
            const url = this.baseUrl + endpoint;
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            if (data && (method === 'POST' || method === 'PUT')) {
                options.body = JSON.stringify(data);
            }

            // Add CSRF token if available
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                options.headers['X-CSRF-Token'] = csrfToken.getAttribute('content');
            }

            return fetch(url, options)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('API request failed:', error);
                    throw error;
                });
        },

        // Dashboard API
        dashboard: {
            getStats: function() {
                return this.request('dashboard-stats');
            },

            getRecentRequests: function() {
                return this.request('requests/recent');
            }
        },

        // Customers API
        customers: {
            getAll: function(params = {}) {
                const queryString = new URLSearchParams(params).toString();
                return this.request('customers' + (queryString ? '?' + queryString : ''));
            },

            getById: function(id) {
                return this.request('customers/' + id);
            },

            create: function(data) {
                return this.request('customers', 'POST', data);
            },

            update: function(id, data) {
                return this.request('customers/' + id, 'PUT', data);
            },

            delete: function(id) {
                return this.request('customers/' + id, 'DELETE');
            },

            search: function(query) {
                return this.request('customers/search?q=' + encodeURIComponent(query));
            }
        },

        // Service requests API
        requests: {
            getAll: function(params = {}) {
                const queryString = new URLSearchParams(params).toString();
                return this.request('requests' + (queryString ? '?' + queryString : ''));
            },

            create: function(data) {
                return this.request('requests', 'POST', data);
            },

            update: function(id, data) {
                return this.request('requests/' + id, 'PUT', data);
            }
        }
    },

    // Utility functions
    utils: {
        // Format phone number
        formatPhone: function(phone) {
            const cleaned = phone.replace(/\D/g, '');
            if (cleaned.length === 10) {
                return `(${cleaned.slice(0, 3)}) ${cleaned.slice(3, 6)}-${cleaned.slice(6)}`;
            }
            return phone;
        },

        // Format currency
        formatCurrency: function(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },

        // Format date
        formatDate: function(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            return new Date(date).toLocaleDateString('en-US', { ...defaultOptions, ...options });
        },

        // Format date and time
        formatDateTime: function(datetime) {
            return new Date(datetime).toLocaleString('en-US');
        },

        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Show loading state
        showLoading: function(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (element) {
                element.classList.add('loading');
                element.disabled = true;
            }
        },

        // Hide loading state
        hideLoading: function(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (element) {
                element.classList.remove('loading');
                element.disabled = false;
            }
        },

        // Show notification
        showNotification: function(message, type = 'info', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            // Auto remove after duration
            if (duration > 0) {
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, duration);
            }

            return notification;
        },

        // Confirm action
        confirm: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },

        // Copy to clipboard
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showNotification('Copied to clipboard!', 'success');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                this.showNotification('Copied to clipboard!', 'success');
            }
        }
    },

    // Form utilities
    forms: {
        // Serialize form data
        serialize: function(form) {
            if (typeof form === 'string') {
                form = document.querySelector(form);
            }

            const formData = new FormData(form);
            const data = {};

            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    if (!Array.isArray(data[key])) {
                        data[key] = [data[key]];
                    }
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            }

            return data;
        },

        // Validate email
        validateEmail: function(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        // Validate phone
        validatePhone: function(phone) {
            const phoneRegex = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
            return phoneRegex.test(phone.replace(/\D/g, ''));
        },

        // Validate required fields
        validateRequired: function(form) {
            if (typeof form === 'string') {
                form = document.querySelector(form);
            }

            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            return isValid;
        }
    },

    // Modal utilities
    modals: {
        // Show modal with content
        show: function(modalId, content = null) {
            const modal = document.getElementById(modalId) || document.querySelector(modalId);
            if (modal) {
                if (content) {
                    const modalBody = modal.querySelector('.modal-body');
                    if (modalBody) {
                        modalBody.innerHTML = content;
                    }
                }

                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                return bsModal;
            }
        },

        // Hide modal
        hide: function(modalId) {
            const modal = document.getElementById(modalId) || document.querySelector(modalId);
            if (modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        }
    },

    // Table utilities
    tables: {
        // Initialize DataTable
        initDataTable: function(selector, options = {}) {
            const defaultOptions = {
                responsive: true,
                pageLength: 25,
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            };

            return $(selector).DataTable({ ...defaultOptions, ...options });
        },

        // Refresh table data
        refresh: function(table) {
            if (typeof table === 'string') {
                table = $(table).DataTable();
            }
            table.ajax.reload();
        }
    },

    // Initialize application
    init: function() {
        console.log('Roadside Assistance Admin Platform initialized');

        // Initialize tooltips
        this.initTooltips();

        // Initialize popovers
        this.initPopovers();

        // Initialize form enhancements
        this.initForms();

        // Initialize navigation
        this.initNavigation();

        // Initialize loading states
        this.initLoadingStates();
    },

    // Initialize Bootstrap tooltips
    initTooltips: function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    // Initialize Bootstrap popovers
    initPopovers: function() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    },

    // Initialize form enhancements
    initForms: function() {
        // Auto-format phone numbers
        document.addEventListener('input', function(e) {
            if (e.target.type === 'tel') {
                e.target.value = RoadsideApp.utils.formatPhone(e.target.value);
            }
        });

        // Form validation on submit
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.tagName !== 'FORM') return;

            if (!RoadsideApp.forms.validateRequired(form)) {
                e.preventDefault();
                RoadsideApp.utils.showNotification('Please fill in all required fields', 'error');
            }
        });
    },

    // Initialize navigation
    initNavigation: function() {
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-collapsed');
            });
        }

        // Active menu highlighting
        this.highlightActiveMenu();
    },

    // Highlight active menu item
    highlightActiveMenu: function() {
        const currentPath = window.location.pathname;
        const menuItems = document.querySelectorAll('.sidebar-menu a');

        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.includes(href.replace(window.location.origin, ''))) {
                item.parentElement.classList.add('active');
            }
        });
    },

    // Initialize loading states
    initLoadingStates: function() {
        // Add loading class styles
        const style = document.createElement('style');
        style.textContent = `
            .loading {
                position: relative;
                pointer-events: none;
                opacity: 0.6;
            }

            .loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin: -10px 0 0 -10px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .btn.loading {
                color: transparent !important;
            }

            .sidebar {
                transition: transform 0.3s ease;
            }

            .sidebar-collapsed .sidebar {
                transform: translateX(-100%);
            }

            .sidebar-collapsed .main-content {
                margin-left: 0;
            }
        `;
        document.head.appendChild(style);
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    RoadsideApp.init();
});

// Handle page visibility changes (for real-time updates)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Page became visible, refresh data if needed
        if (window.location.pathname.includes('/dashboard')) {
            // Refresh dashboard data
            RoadsideApp.api.dashboard.getStats().then(data => {
                // Update dashboard elements with fresh data
                console.log('Dashboard data refreshed');
            }).catch(error => {
                console.error('Failed to refresh dashboard data:', error);
            });
        }
    }
});
