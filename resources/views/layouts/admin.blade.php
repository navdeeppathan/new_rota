<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- CSRF Token for AJAX -->
    <title>Marian House Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <!-- Optional Bootstrap for structure if needed -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Add these in your layout file or before </head> -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>

            /* small improvements */
        #admin-notification-dropdown .list-group-item {
            cursor: pointer;
        }
        #admin-notification-dropdown .list-group-item .fw-semibold {
            font-size: 0.95rem;
        }
        #admin-notification-dropdown .mark-read-btn {
            color: #6c757d;
        }

        .sidebar a.active {
            background: #2c4db5;
            color: #fff;
        }

        .sidebar a:hover {
            background: #2c4db5;
            color: #fff;
        }

        
            * {
                box-sizing: border-box;
                font-family: 'Segoe UI', sans-serif;
            }

            body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .header {
                background-color: #17215F;
                color: #FFFFFF;
                padding: 15px 30px;
                font-size: 24px;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 15px; /* space between image and text */
            }
            
            .header-logo {
                height: 40px; /* adjust size as needed */
                width: auto;
            }


            .footer {
                background-color: #f1f1f1;
                padding: 15px 0;
                text-align: center;
                color: #6c757d;
                font-size: 14px;
            }

            .layout-body {
                flex: 1;
                display: flex;
                background-color: #f8f9fa;
            }

        
            .sidebar {
                width: 220px;
                background: #305ED9;
                color: white;
                padding: 20px 0;
                display: flex;
                flex-direction: column;
                align-items: start;
                font-family: Arial, sans-serif;
            }
            
            .sidebar a {
                color: white;
                display: flex;
                align-items: center;
                padding: 10px 20px;
                width: 100%;
                text-decoration: none;
                transition: background 0.3s;
            }
            
            .sidebar a:hover {
                background: #2c4db5;
            }
            
            .sidebar a .icon {
                margin-right: 10px;
                width: 20px;
                display: flex;
                justify-content: center;
            }


            .main-content {
                flex: 1;
                /* padding: 30px; */
                background: #fff;
            }

            .btn-danger {
                background-color: #E85A72;
                color: #fff;
                border: none;
                padding: 8px 12px;
                border-radius: 44px;
                cursor: pointer;
                font-weight: 500;
            }

            .btn-danger:hover {
                background-color: #c82333;
            }
            

            @media (max-width: 768px) {
                .sidebar {
                    display: none;
                }
            }
            
            /* MOBILE-STYLE NOTIFICATION POPUP */
        .notification-popup {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            z-index: 9999;
            opacity: 0;
            transform: translateY(-100%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            border-bottom: 3px solid rgba(255, 255, 255, 0.2);
        }

        .notification-popup.show {
            opacity: 1;
            transform: translateY(0);
        }

        .notification-popup.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .notification-popup.error {
            background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
        }

        .notification-popup.warning {
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            color: #2d3436;
        }

        .notification-popup.info {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        }

        .notification-content {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .notification-icon {
            font-size: 24px;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        .notification-body {
            flex: 1;
            min-width: 0;
        }

        .notification-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .notification-type {
            background: rgba(255, 255, 255, 0.2);
            color: inherit;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .notification-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.8);
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .notification-close:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .notification-message {
            font-size: 16px;
            line-height: 1.4;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .notification-time {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 0;
        }

        .notification-actions {
            display: none; /* Hidden in mobile style */
        }

        /* Progress bar for auto-dismiss */
        .notification-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 0 0 8px 8px;
            animation: progressBar 5s linear forwards;
        }

        @keyframes progressBar {
            from { width: 100%; }
            to { width: 0%; }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .notification-content {
                padding: 15px;
                gap: 12px;
            }
            
            .notification-icon {
                width: 35px;
                height: 35px;
                font-size: 20px;
            }
            
            .notification-message {
                font-size: 14px;
            }
            
            .notification-time {
                font-size: 11px;
            }
        }

        /* Debug styles */
        .debug-info {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            z-index: 10000;
        }

        @media (max-width: 768px) {
            .notification-popup {
                right: 10px;
                left: 10px;
                width: auto;
                max-width: none;
            }
        }
        
        
    
        .user-list-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .top-bar .title {
            font-size: 20px;
            font-weight: 600;
        }

        .right-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-input {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .add-button {
            background: #f1f6ff;
            border: 1px solid #dbe4f7;
            color: #305ed9;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
        }

        .add-button:hover {
            background: #305ed9;
            color: white;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        thead {
            background: transparent;
        }

        thead th {
            text-align: left;
            color: #333;
            font-size: 14px;
            padding: 10px 14px;
        }

        tbody tr {
            background: #fdfdfe;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            border-radius: 8px;
        }

        tbody tr td {
            padding: 14px 14px;
            font-size: 14px;
            color: #333;
        }

        tbody tr td img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status.active {
            background: #d4f6e4;
            color: #1e9e54;
        }

        .status.inactive {
            background: #ffe0e0;
            color: #dc3545;
        }

        .edit-btn, .delete-btn {
            padding: 8px 10px;
            border-radius: 50%;
            text-decoration: none;
            color: white;
            margin-right: 4px;
        }

        .edit-btn {
            background: #6f42c1;
        }

        .delete-btn {
            background: #dc3545;
        }

        .edit-btn i, .delete-btn i {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .filter-input {
                width: 100%;
            }

            .add-button {
                width: 100%;
                justify-content: center;
            }
        }

    </style>

</head>
<body>
      <!-- Full-Width Header -->
    <div class="header">
        <img src="{{ asset('marianhouseicon.png') }}" alt="Logo" class="header-logo">
        Marian House Admin Panel
        <!-- Notification Bell in header -->
        <li class="nav-item dropdown" id="admin-notification-dropdown">
            <a class="nav-link position-relative" href="#" id="adminNotificationToggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell fa-lg"></i>
                <span id="adminNotificationCount" class="badge rounded-pill bg-danger position-absolute" style="top: -6px; right: -6px; display:none;">0</span>
            </a>

            <div class="dropdown-menu dropdown-menu-end p-0" style="width: 360px;" aria-labelledby="adminNotificationToggle">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Notifications</strong>
                        <small id="notificationTimestamp" class="text-muted"></small>
                    </div>

                    <div class="card-body p-0">
                        <div id="adminNotificationsList" class="list-group list-group-flush" style="max-height: 320px; overflow:auto;">
                            <!-- Ajax loaded notifications will be inserted here -->
                            <div class="text-center p-3" id="noNotificationsMsg">No new notifications</div>
                        </div>
                    </div>

                    <!-- <div class="card-footer text-center">
                        <a href="{{ route('admin.notifications.all') ?? '#' }}" class="small">View all notifications</a>
                    </div> -->
                </div>
            </div>
        </li>

    </div>
    

    <div class="layout-body">
       <div class="sidebar">
            <a href="{{ route('dashboard') }}" class="{{ Route::is('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home icon"></i> Dashboard
            </a>
            <a href="{{ route('users') }}" class="{{ Route::is('users') ? 'active' : '' }}">
                <i class="fas fa-users icon"></i> Employees
            </a>
            <a href="{{ route('admin.chat') }}" class="{{ Route::is('admin.chat') ? 'active' : '' }}">
                <i class="fas fa-users icon"></i> Message
            </a>
            <!--<a href="{{ route('schedule.index') }}" class="{{ Route::is('schedule.index') ? 'active' : '' }}">-->
            <!--    <i class="fas fa-calendar-alt icon"></i> Schedules-->
            <!--</a>-->
            <!--<a href="{{ route('task_perform.index') }}" class="{{ Route::is('task_perform.index') ? 'active' : '' }}">-->
            <!--    <i class="fas fa-tasks icon"></i> Task Schedules-->
            <!--</a>-->
            <!--<a href="{{ route('task_perform.assign') }}" class="{{ Route::is('task_perform.assign') ? 'active' : '' }}">-->
            <!--    <i class="fas fa-user-plus icon"></i> Assign Tasks-->
            <!--</a>-->
            <a href="{{ route('leave-requests.index') }}" class="{{ Route::is('leave-requests.index') ? 'active' : '' }}">
                <i class="fas fa-envelope icon"></i> Employees Leave 
            </a>
             <a href="{{ route('availability.index') }}" class="{{ Route::is('availability.index') ? 'active' : '' }}">
                <i class="fas fa-clock icon"></i> Availability
            </a>
            <!--<a href="{{ route('availability.create') }}" class="{{ Route::is('availability.create') ? 'active' : '' }}">-->
            <!--    <i class="fas fa-clock icon"></i> Add Availability-->
            <!--</a>-->
            <!--    <a href="{{ route('shift-definitions.create') }}" class="{{ Route::is('admin.shift-definitions.create') ? 'active' : '' }}">-->
            <!--    <i class="fas fa-sun icon"></i> Create Shifts-->
            <!--</a>-->
        
            <a href="{{ route('shifts.create') }}" class="{{ Route::is('shifts.create') ? 'active' : '' }}">
                <i class="fas fa-sun icon"></i> Shifts
            </a>
            <a href="{{ route('reports.shifts') }}" class="{{ Route::is('reports.shifts') ? 'active' : '' }}">
                <i class="fas fa-sun icon"></i> Report
            </a>
            <a href="{{ route('broadcasts.index') }}" class="{{ Route::is('broadcasts.index') ? 'active' : '' }}">
                <i class="fas fa-sun icon"></i>Broadcast
            </a>
            
            <a href="{{ route('admin.privacy.manage') }}" class="{{ Route::is('admin.privacy.manage') ? 'active' : '' }}">
                <i class="fas fa-clock icon"></i> Privacy Policy
            </a>
        
            <form method="POST" action="{{ route('logout') }}" class="w-100 px-3 mt-4">
                @csrf
                <button type="submit" class="btn btn-danger w-100">Logout</button>
            </form>
</div>
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    
     <!-- Full-Width Footer -->
    <div class="footer">
        &copy; {{ date('Y') }} All rights reserved | Designed & Developed by 
        <a href="http://thenexteck.com/" target="_blank" style="text-decoration: none; font-weight: bold;">Nexteck</a> |
        <a href="tel:+447879175585" style="text-decoration: none; font-weight: bold;">+44 7879 175585</a>
    </div>
    
    <!-- ✅ Load jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ Load SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Your custom scripts -->
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                width: '100%',
                dropdownAutoWidth: true
            });
        });
    </script>

    <script>
           setInterval(function() {
            $.get("{{ route('admin.notifications.list') }}", function(res) {
                if (res.success) {
                    updateCount(res.count || 0);
        
                    // Refresh the list if dropdown is open
                    const isDropdownOpen = $('#adminNotificationToggle').attr('aria-expanded') === 'true';
                    if (isDropdownOpen) {
                        renderNotifications(res.notifications || []);
                    }
                }
            });
        }, 5000);
        $(document).ready(function() {
            const listSelector = '#adminNotificationsList';
            const countSelector = '#adminNotificationCount';
            const noMsgSelector = '#noNotificationsMsg';
            const notificationsUrl = "{{ route('admin.notifications.list') }}";
            const markReadUrl = "{{ route('admin.notifications.markRead') }}";

            // CSRF token for POST
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            // Fetch notifications (used on dropdown show)
            function fetchAdminNotifications() {
                // show loading state
                $(listSelector).html('<div class="p-3 text-center">Loading...</div>');

                $.get(notificationsUrl, function(res) {
                    if (res.success) {
                        renderNotifications(res.notifications || []);
                        updateCount(res.count || 0);
                    } else {
                        $(listSelector).html('<div class="p-3 text-center text-danger">Failed to load</div>');
                    }
                }).fail(function() {
                    $(listSelector).html('<div class="p-3 text-center text-danger">Failed to load</div>');
                });
            }

            // Render notifications in dropdown
            function renderNotifications(notifications) {
                if (!notifications || notifications.length === 0) {
                    $(listSelector).html('<div id="noNotificationsMsg" class="text-center p-3">No new notifications</div>');
                    return;
                }

                let html = '';
                notifications.forEach(function(n) {
                    // sanitize values accordingly in production
                    const created = new Date(n.created_at).toLocaleString();
                    const title = n.alert_type ? (n.alert_type.name || '') : '';
                    const message = n.message || '';
                    const userName = n.user ? n.user.name : '';

                    html += `
                        <a href="#" class="list-group-item list-group-item-action notification-item d-flex" data-id="${n.id}">
                            <div class="flex-grow-1">
                                <div class="small text-muted mb-1">${title} • ${created}</div>
                                <div class="fw-semibold">${message}</div>
                                ${userName ? `<div class="small text-muted">From: ${userName}</div>` : ''}
                            </div>
                            <div class="ps-2 align-self-center">
                                <button class="btn btn-sm btn-link mark-read-btn" data-id="${n.id}" title="Mark as read">
                                    <i class="fa fa-check"></i>
                                </button>
                            </div>
                        </a>
                    `;
                });

                $(listSelector).html(html);
            }

            // Update bell count UI
            function updateCount(count) {
                const $count = $(countSelector);
                if (count && count > 0) {
                    $count.text(count).show();
                } else {
                    $count.hide();
                }
            }

            // Mark as read action
            function markAsRead(notificationId, $item) {
                $.post(markReadUrl, { id: notificationId })
                    .done(function(res) {
                        if (res.success) {
                            // remove item from DOM
                            $item.remove();

                            // update count (decrement)
                            let current = parseInt($(countSelector).text()) || 0;
                            current = Math.max(0, current - 1);
                            updateCount(current);

                            // if no more items, show empty msg
                            if ($(listSelector).find('.notification-item').length === 0) {
                                $(listSelector).html('<div id="noNotificationsMsg" class="text-center p-3">No new notifications</div>');
                            }
                        } else {
                            alert(res.message || 'Could not mark notification as read');
                        }
                    })
                    .fail(function() {
                        alert('Failed to mark notification as read');
                    });
            }

            // Open dropdown: fetch notifications (only when opened)
            $('#adminNotificationToggle').on('click', function(e) {
                // Fetch notifications on click
                fetchAdminNotifications();
            });

            // Handle click on an individual notification item: mark as read and optionally go to link
            $(document).on('click', '.notification-item', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const $this = $(this);
                // mark it as read and remove
                markAsRead(id, $this);
            });

            // Also handle explicit mark-read button inside the item
            $(document).on('click', '.mark-read-btn', function(e) {
                e.stopPropagation();
                e.preventDefault();
                const id = $(this).data('id');
                const $item = $(this).closest('.notification-item');
                markAsRead(id, $item);
            });

            // Initial load: update count when page loads
            (function initialCountLoad(){
                $.get(notificationsUrl, function(res) {
                    if (res.success) {
                        updateCount(res.count || 0);
                    }
                });
            })();
        });
    </script>

    <!-- ✅ SweetAlert session messages -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#3085d6',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
            @endif
    
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ $errors->first() }}',
                    confirmButtonColor: '#d33',
                    timer: 4000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
            @endif
        });
    </script>

    <script>
       
        // FIXED NOTIFICATION SYSTEM
        let notificationPolling = null;
        let shownNotifications = new Set();
        let adminId = 1; // Replace with actual admin ID from your Laravel auth
        let debugMode = false;
        let errorCount = 0;

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Admin ID:', adminId);
            
            // Initialize Select2
            $('.select2').select2({
                width: '100%',
                dropdownAutoWidth: true
            });
            
            // Start notifications if admin ID exists
            if (adminId) {
                console.log('Starting notification polling for admin:', adminId);
                // startNotificationPolling();
            } else {
                console.error('Admin ID not found - notifications disabled');
            }
        });

        // function startNotificationPolling() {
        //     if (notificationPolling) {
        //         clearInterval(notificationPolling);
        //     }
            
        //     console.log('Starting notification polling...');
        //     checkNotifications(); // Check immediately
        //     notificationPolling = setInterval(checkNotifications, 10000); // Check every 10 seconds for testing
            
        //     if (debugMode) {
        //         document.getElementById('pollingStatus').textContent = 'Running';
        //     }
        // }


        // async function checkNotifications() {
        //     try {
        //         console.log('Checking notifications for admin:', adminId);
        //         // updateDebugInfo();
                
        //         // FIXED: Corrected the API URL (was missing 'i' in 'api')
        //         const response = await fetch(`/api/notifications/admin/${adminId}`, {
        //             method: 'GET',
        //             headers: {
        //                 'Content-Type': 'application/json',
        //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        //                 'Accept': 'application/json'
        //             }
        //         });
                
        //         console.log('Response status:', response.status);
                
        //         if (!response.ok) {
        //             throw new Error(`HTTP error! status: ${response.status}`);
        //         }
                
        //         const data = await response.json();
        //         console.log('Notification data received:', data);
                
        //         if (data.success && data.notifications && data.notifications.length > 0) {
        //             processNotifications(data.notifications);
        //         } else {
        //             console.log('No new notifications');
        //         }
                
        //     } catch (error) {
        //         console.error('Error fetching notifications:', error);
        //         errorCount++;
        //         // updateDebugInfo();
                
        //         // If it's a 404 or network error, you might want to show a fallback
        //         if (error.message.includes('404')) {
        //             console.warn('Notification API endpoint not found - using test data');
        //             // Uncomment below for testing without backend
        //             // testNotificationData();
        //         }
        //     }
        // }

        // function processNotifications(notifications) {
        //     console.log('Processing notifications:', notifications);
            
        //     notifications.forEach(notification => {
        //         // FIXED: Added null check for read_at
        //         if ((!notification.read_at || notification.read_at === null) && !shownNotifications.has(notification.id)) {
        //             console.log('Showing notification:', notification.id);
        //             showNotificationPopup(notification);
        //             shownNotifications.add(notification.id);
        //         }
        //     });
            
        //     // updateDebugInfo();
        // }

        // function showNotificationPopup(notification) {
        //     console.log('Creating mobile-style notification popup for:', notification);
            
        //     const popup = document.createElement('div');
        //     popup.className = 'notification-popup';
        //     popup.setAttribute('data-notification-id', notification.id);
            
        //     // FIXED: Added null checks and fallback values
        //     const alertType = notification.alert_type?.type || 'info';
        //     const createdAt = notification.created_at ? new Date(notification.created_at).toLocaleString() : 'Just now';
        //     const message = notification.message || 'No message content';
            
        //     // Add type-specific styling
        //     popup.classList.add(alertType.toLowerCase());
            
        //     // Choose appropriate icon based on type
        //     let iconClass = 'fas fa-info-circle';
        //     switch(alertType.toLowerCase()) {
        //         case 'success':
        //             iconClass = 'fas fa-check-circle';
        //             break;
        //         case 'error':
        //         case 'danger':
        //             iconClass = 'fas fa-exclamation-circle';
        //             break;
        //         case 'warning':
        //             iconClass = 'fas fa-exclamation-triangle';
        //             break;
        //         case 'user':
        //             iconClass = 'fas fa-user';
        //             break;
        //         case 'schedule':
        //             iconClass = 'fas fa-calendar-alt';
        //             break;
        //         case 'task':
        //             iconClass = 'fas fa-tasks';
        //             break;
        //         default:
        //             iconClass = 'fas fa-bell';
        //     }
            
        //     popup.innerHTML = `
        //         <div class="notification-content">
        //             <div class="notification-icon">
        //                 <i class="${iconClass}"></i>
        //             </div>
        //             <div class="notification-body">
        //                 <div class="notification-header">
        //                     <span class="notification-type">${alertType}</span>
        //                     <span class="notification-time">${createdAt}</span>
        //                 </div>
        //                 <div class="notification-message">
        //                     ${message}
        //                 </div>
        //             </div>
        //             <button class="notification-close" onclick="closeNotification(${notification.id})">&times;</button>
        //         </div>
        //         <div class="notification-progress"></div>
        //     `;
            
        //     document.body.appendChild(popup);
            
        //     // Show with animation
        //     setTimeout(() => {
        //         popup.classList.add('show');
        //     }, 100);
            
        //     // Auto-close after 5 seconds
        //     setTimeout(() => {
        //         if (document.querySelector(`[data-notification-id="${notification.id}"]`)) {
        //             closeNotification(notification.id);
        //         }
        //     }, 5000);
        // }

        // function closeNotification(notificationId) {
        //     console.log('Closing notification:', notificationId);
        //     const popup = document.querySelector(`[data-notification-id="${notificationId}"]`);
        //     if (popup) {
        //         popup.classList.remove('show');
        //         setTimeout(() => {
        //             popup.remove();
        //         }, 400);
        //     }
        // }

        async function markAsRead(notificationId) {
            try {
                console.log('Marking notification as read:', notificationId);
                
                // FIXED: Corrected the API URL (was '/ap/' instead of '/api/')
                const response = await fetch(`/api/notifications/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({id: notificationId})
                });
                
                if (response.ok) {
                    console.log('Notification marked as read successfully');
                    closeNotification(notificationId);
                } else {
                    console.error('Failed to mark notification as read:', response.status);
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        // Enhanced test function with different notification types
        function testNotification(type = 'info') {
            const messages = {
                info: 'New system update available',
                success: 'Task completed successfully!',
                warning: 'Your session will expire in 5 minutes',
                error: 'Failed to save changes. Please try again.',
                user: 'New user registration: John Doe',
                schedule: 'Schedule updated for tomorrow',
                task: 'New task assigned to you'
            };
            
            const testNotification = {
                id: Date.now(),
                message: messages[type] || messages.info,
                alert_type: { type: type },
                created_at: new Date().toISOString(),
                read_at: null
            };
            
            console.log('Testing notification:', testNotification);
            showNotificationPopup(testNotification);
        }

        // Auto-demo function - shows different notifications automatically
        function startAutoDemo() {
            const types = ['info', 'success', 'warning', 'error', 'user', 'schedule', 'task'];
            let index = 0;
            
            setInterval(() => {
                testNotification(types[index % types.length]);
                index++;
            }, 3000);
        }

        // Uncomment the line below to start auto-demo
        // setTimeout(startAutoDemo, 2000);

        // Test data function for when backend is not available
        function testNotificationData() {
            const testData = {
                success: true,
                notifications: [
                    {
                        id: 1,
                        message: 'Test notification 1 - New user registered',
                        alert_type: { type: 'User' },
                        created_at: new Date().toISOString(),
                        read_at: null
                    },
                    {
                        id: 2,
                        message: 'Test notification 2 - Schedule updated',
                        alert_type: { type: 'Schedule' },
                        created_at: new Date().toISOString(),
                        read_at: null
                    }
                ]
            };
            
            processNotifications(testData.notifications);
        }

        // Event listeners for page visibility
        window.addEventListener('beforeunload', function() {
            stopNotificationPolling();
        });

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopNotificationPolling();
            } else if (adminId) {
                startNotificationPolling();
            }
        });
    </script>
    
   <!-- Bootstrap JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


    @yield('scripts')

</body>
</html>
