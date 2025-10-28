<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Roadside Assistance Admin Platform">
    <meta name="author" content="Roadside Assistance Company">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Roadside Admin">
    <link rel="manifest" href="<?php echo SITE_URL; ?>manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo SITE_URL; ?>assets/images/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo SITE_URL; ?>assets/images/icon-192x192.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>assets/images/icon-72x72.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>assets/images/icon-72x72.png">

    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>

    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><?php echo SITE_NAME; ?></h4>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == '') ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/customers') !== false) ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>customers">
                    <i class="bi bi-people"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/requests') !== false) ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>requests">
                    <i class="bi bi-truck"></i>
                    <span>Service Requests</span>
                </a>
            </li>
            <li class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/drivers') !== false) ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>drivers">
                    <i class="bi bi-person-badge"></i>
                    <span>Drivers</span>
                </a>
            </li>
            <li class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/reports') !== false) ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>reports">
                    <i class="bi bi-bar-chart"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/settings') !== false) ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>settings">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?php echo SITE_URL; ?>logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <button class="sidebar-toggle" id="sidebarToggle">
                            <i class="bi bi-list"></i>
                        </button>
                        <h5 class="page-title mb-0"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="user-info">
                            <span class="user-name">Welcome, <?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>
                            <small class="user-role text-muted"><?php echo $_SESSION['user_role'] ?? 'Administrator'; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            <?php
            // Display flash messages
            $flashMessage = getFlashMessage();
            if ($flashMessage):
            ?>
            <div class="container-fluid mb-4">
                <div class="alert alert-<?php echo $flashMessage['type'] == 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flashMessage['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </main>
    </div>
    <?php else: ?>
    <!-- Login Layout -->
    <div class="login-container">
        <div class="login-wrapper">
            <?php
            // Display flash messages for login page
            $flashMessage = getFlashMessage();
            if ($flashMessage):
            ?>
            <div class="alert alert-<?php echo $flashMessage['type'] == 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                <?php echo $flashMessage['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php echo $content ?? ''; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>frontend/js/app.js"></script>

    <!-- Service Worker Registration -->
    <script>
        // Register service worker for PWA functionality
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo SITE_URL; ?>service-worker.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful:', registration.scope);
                        
                        // Check for updates periodically
                        setInterval(function() {
                            registration.update();
                        }, 60000); // Check every minute

                        // Handle service worker updates
                        registration.addEventListener('updatefound', function() {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', function() {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New service worker available, show update notification
                                    if (confirm('A new version is available! Click OK to update.')) {
                                        newWorker.postMessage({ type: 'SKIP_WAITING' });
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(function(error) {
                        console.log('ServiceWorker registration failed:', error);
                    });

                // Handle service worker controlling the page
                let refreshing = false;
                navigator.serviceWorker.addEventListener('controllerchange', function() {
                    if (!refreshing) {
                        refreshing = true;
                        window.location.reload();
                    }
                });
            });
        }

        // Install prompt for PWA
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', function(e) {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            
            // Show install button or notification (optional)
            console.log('PWA install prompt available');
            
            // You can show a custom install button here
            // Example: showInstallPromotion();
        });

        // Track PWA installation
        window.addEventListener('appinstalled', function() {
            console.log('PWA was installed');
            deferredPrompt = null;
        });

        // Check if app is running as PWA
        function isPWA() {
            return window.matchMedia('(display-mode: standalone)').matches ||
                   window.navigator.standalone === true;
        }

        if (isPWA()) {
            console.log('Running as PWA');
            // Add PWA-specific behaviors here
        }
    </script>

    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
