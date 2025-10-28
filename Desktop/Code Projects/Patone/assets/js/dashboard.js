/**
 * Enhanced Dashboard with Real-time Statistics
 * Handles auto-refresh, charts, and interactive widgets
 */

const DashboardManager = {
    // Configuration
    config: {
        refreshInterval: 30000, // 30 seconds
        chartColors: {
            primary: '#0d6efd',
            success: '#198754',
            warning: '#ffc107',
            danger: '#dc3545',
            info: '#0dcaf0',
            secondary: '#6c757d'
        }
    },

    // Chart instances
    charts: {},

    // Auto-refresh timer
    refreshTimer: null,

    // Initialize dashboard
    init: function() {
        console.log('Dashboard Manager initialized');
        
        // Initial data load
        this.loadDashboardData();
        
        // Initialize charts
        this.initializeCharts();
        
        // Start auto-refresh
        this.startAutoRefresh();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Load additional widgets
        this.loadRecentActivity();
        this.loadPerformanceMetrics();
    },

    // Load all dashboard data
    loadDashboardData: function() {
        this.updateStatistics();
        this.updateRecentRequests();
        this.updateDriverStatus();
        this.updateChartData();
    },

    // Update statistics cards
    updateStatistics: function() {
        fetch(window.location.origin + '/api/dashboard/stats')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    this.animateCounter('activeRequests', data.data.active_requests || 0);
                    this.animateCounter('completedToday', data.data.completed_today || 0);
                    this.animateCounter('availableDrivers', data.data.available_drivers || 0);
                    this.animateCounter('totalCustomers', data.data.total_customers || 0);
                }
            })
            .catch(error => {
                console.error('Error loading statistics:', error);
            });
    },

    // Animate counter with smooth transition
    animateCounter: function(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const currentValue = parseInt(element.textContent) || 0;
        const increment = (targetValue - currentValue) / 20;
        let current = currentValue;
        
        const animation = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= targetValue) || (increment < 0 && current <= targetValue)) {
                element.textContent = targetValue;
                clearInterval(animation);
            } else {
                element.textContent = Math.round(current);
            }
        }, 50);
    },

    // Update recent requests table
    updateRecentRequests: function() {
        fetch(window.location.origin + '/api/dashboard/recent-requests')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    // Update table content if needed
                    console.log('Recent requests updated:', data.data.length);
                }
            })
            .catch(error => {
                console.error('Error loading recent requests:', error);
            });
    },

    // Update driver status widget
    updateDriverStatus: function() {
        fetch(window.location.origin + '/api/dashboard/driver-status')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    this.renderDriverStatus(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading driver status:', error);
            });
    },

    // Render driver status list
    renderDriverStatus: function(drivers) {
        const container = document.getElementById('driverStatus');
        if (!container || !drivers || drivers.length === 0) return;
        
        const html = drivers.map(driver => {
            const statusClass = driver.status === 'available' ? 'success' : 'warning';
            const initial = driver.name ? driver.name.charAt(0).toUpperCase() : 'D';
            
            return `
                <div class="driver-item d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="driver-avatar bg-${statusClass} text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                             style="width: 32px; height: 32px; font-size: 14px;">
                            ${initial}
                        </div>
                        <div>
                            <div class="fw-medium small">${this.escapeHtml(driver.name)}</div>
                            <small class="text-muted">${this.escapeHtml(driver.phone)}</small>
                        </div>
                    </div>
                    <span class="badge bg-${statusClass}">
                        ${driver.status.charAt(0).toUpperCase() + driver.status.slice(1)}
                    </span>
                </div>
            `;
        }).join('');
        
        container.innerHTML = html;
    },

    // Initialize all charts
    initializeCharts: function() {
        this.initRequestsTimelineChart();
        this.initServiceTypeChart();
        this.initDriverPerformanceChart();
        this.initHourlyRequestsChart();
    },

    // Update all chart data
    updateChartData: function() {
        fetch(window.location.origin + '/api/dashboard/chart-data')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    this.updateRequestsTimelineChart(data.data.requests_timeline);
                    this.updateServiceTypeChart(data.data.service_type_distribution);
                    this.updateDriverPerformanceChart(data.data.driver_performance);
                    this.updateHourlyRequestsChart(data.data.hourly_requests);
                }
            })
            .catch(error => {
                console.error('Error loading chart data:', error);
            });
    },

    // Initialize requests timeline chart
    initRequestsTimelineChart: function() {
        const canvas = document.getElementById('requestsTimelineChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        this.charts.timeline = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Total Requests',
                        data: [],
                        borderColor: this.config.chartColors.primary,
                        backgroundColor: this.config.chartColors.primary + '20',
                        tension: 0.4
                    },
                    {
                        label: 'Completed',
                        data: [],
                        borderColor: this.config.chartColors.success,
                        backgroundColor: this.config.chartColors.success + '20',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Requests Timeline (Last 7 Days)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    },

    // Update requests timeline chart
    updateRequestsTimelineChart: function(data) {
        if (!this.charts.timeline || !data) return;
        
        const labels = data.map(item => item.date);
        const totals = data.map(item => item.total);
        const completed = data.map(item => item.completed);
        
        this.charts.timeline.data.labels = labels;
        this.charts.timeline.data.datasets[0].data = totals;
        this.charts.timeline.data.datasets[1].data = completed;
        this.charts.timeline.update();
    },

    // Initialize service type distribution chart
    initServiceTypeChart: function() {
        const canvas = document.getElementById('serviceTypeChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        this.charts.serviceType = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        this.config.chartColors.primary,
                        this.config.chartColors.success,
                        this.config.chartColors.warning,
                        this.config.chartColors.info,
                        this.config.chartColors.secondary
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Service Type Distribution'
                    }
                }
            }
        });
    },

    // Update service type chart
    updateServiceTypeChart: function(data) {
        if (!this.charts.serviceType || !data) return;
        
        const labels = data.map(item => item.service_type);
        const counts = data.map(item => item.count);
        
        this.charts.serviceType.data.labels = labels;
        this.charts.serviceType.data.datasets[0].data = counts;
        this.charts.serviceType.update();
    },

    // Initialize driver performance chart
    initDriverPerformanceChart: function() {
        const canvas = document.getElementById('driverPerformanceChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        this.charts.driverPerformance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Completed Requests',
                    data: [],
                    backgroundColor: this.config.chartColors.success
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Top Driver Performance'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    },

    // Update driver performance chart
    updateDriverPerformanceChart: function(data) {
        if (!this.charts.driverPerformance || !data) return;
        
        const labels = data.map(item => item.driver_name);
        const completed = data.map(item => item.completed);
        
        this.charts.driverPerformance.data.labels = labels;
        this.charts.driverPerformance.data.datasets[0].data = completed;
        this.charts.driverPerformance.update();
    },

    // Initialize hourly requests chart
    initHourlyRequestsChart: function() {
        const canvas = document.getElementById('hourlyRequestsChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        this.charts.hourlyRequests = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Requests',
                    data: [],
                    backgroundColor: this.config.chartColors.info
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Hourly Request Distribution (Today)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    },

    // Update hourly requests chart
    updateHourlyRequestsChart: function(data) {
        if (!this.charts.hourlyRequests || !data) return;
        
        const labels = data.map(item => item.hour + ':00');
        const counts = data.map(item => item.count);
        
        this.charts.hourlyRequests.data.labels = labels;
        this.charts.hourlyRequests.data.datasets[0].data = counts;
        this.charts.hourlyRequests.update();
    },

    // Load recent activity feed
    loadRecentActivity: function() {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        fetch(window.location.origin + '/api/dashboard/recent-activity')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    this.renderRecentActivity(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading recent activity:', error);
            });
    },

    // Render recent activity feed
    renderRecentActivity: function(activities) {
        const container = document.getElementById('recentActivity');
        if (!container || !activities || activities.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">No recent activity</p>';
            return;
        }
        
        const html = activities.map(activity => `
            <div class="activity-item border-bottom pb-2 mb-2">
                <div class="d-flex justify-content-between">
                    <strong>${this.escapeHtml(activity.description)}</strong>
                    <small class="text-muted">${this.timeAgo(activity.timestamp)}</small>
                </div>
                <small class="text-muted">by ${this.escapeHtml(activity.actor)}</small>
            </div>
        `).join('');
        
        container.innerHTML = html;
    },

    // Load performance metrics
    loadPerformanceMetrics: function() {
        fetch(window.location.origin + '/api/dashboard/performance-metrics')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    this.renderPerformanceMetrics(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading performance metrics:', error);
            });
    },

    // Render performance metrics
    renderPerformanceMetrics: function(metrics) {
        if (document.getElementById('avgResponseTime')) {
            document.getElementById('avgResponseTime').textContent = metrics.avg_response_time + ' min';
        }
        if (document.getElementById('completionRate')) {
            document.getElementById('completionRate').textContent = metrics.completion_rate + '%';
        }
        if (document.getElementById('customerSatisfaction')) {
            document.getElementById('customerSatisfaction').textContent = metrics.customer_satisfaction + '/5.0';
        }
        if (document.getElementById('peakHours')) {
            document.getElementById('peakHours').textContent = metrics.peak_hours;
        }
    },

    // Start auto-refresh
    startAutoRefresh: function() {
        this.refreshTimer = setInterval(() => {
            console.log('Auto-refreshing dashboard data...');
            this.loadDashboardData();
        }, this.config.refreshInterval);
    },

    // Stop auto-refresh
    stopAutoRefresh: function() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    },

    // Setup event listeners
    setupEventListeners: function() {
        // Manual refresh button
        const refreshBtn = document.getElementById('refreshDashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadDashboardData();
                RoadsideApp.utils.showNotification('Dashboard refreshed', 'success', 2000);
            });
        }
        
        // Pause auto-refresh when page is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopAutoRefresh();
            } else {
                this.loadDashboardData();
                this.startAutoRefresh();
            }
        });
    },

    // Utility: Escape HTML
    escapeHtml: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    // Utility: Time ago formatter
    timeAgo: function(timestamp) {
        const now = new Date();
        const past = new Date(timestamp);
        const diffMs = now - past;
        const diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return diffMins + ' min ago';
        
        const diffHours = Math.floor(diffMins / 60);
        if (diffHours < 24) return diffHours + ' hour' + (diffHours > 1 ? 's' : '') + ' ago';
        
        const diffDays = Math.floor(diffHours / 24);
        return diffDays + ' day' + (diffDays > 1 ? 's' : '') + ' ago';
    }
};

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on dashboard page
    if (window.location.pathname.includes('/dashboard') || window.location.pathname === '/') {
        DashboardManager.init();
    }
});

// Manual refresh function for backward compatibility
function refreshDashboard() {
    DashboardManager.loadDashboardData();
    RoadsideApp.utils.showNotification('Dashboard refreshed', 'success', 2000);
}

function refreshDrivers() {
    DashboardManager.updateDriverStatus();
    RoadsideApp.utils.showNotification('Driver status updated', 'success', 2000);
}
