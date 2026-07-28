<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="app-sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="brand-logo">
            <div class="logo-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0v-5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v5m-6 0h6"></path></svg>
            </div>
            <div class="brand-text">
                <span class="hotel-name">Grand Royale</span>
                <span class="hotel-sub">Hotel & Suites</span>
            </div>
        </a>
        <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close Sidebar">&times;</button>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section-label">Main</div>
        
        <a href="dashboard.php" class="nav-item <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            <span>Dashboard</span>
        </a>

        <div class="menu-section-label">Management</div>

        <!-- Room Module -->
        <div class="nav-group <?= in_array($currentPage, ['add_room.php', 'view_rooms.php', 'edit_room.php']) ? 'open' : '' ?>">
            <div class="nav-item has-submenu">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Rooms</span>
                <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            <div class="submenu">
                <a href="add_room.php" class="<?= ($currentPage == 'add_room.php') ? 'active' : '' ?>">Add Room</a>
                <a href="view_rooms.php" class="<?= ($currentPage == 'view_rooms.php') ? 'active' : '' ?>">View Rooms</a>
            </div>
        </div>

        <!-- Customer Module -->
        <div class="nav-group <?= in_array($currentPage, ['add_customer.php', 'view_customers.php', 'edit_customer.php']) ? 'open' : '' ?>">
            <div class="nav-item has-submenu">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Customers</span>
                <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            <div class="submenu">
                <a href="add_customer.php" class="<?= ($currentPage == 'add_customer.php') ? 'active' : '' ?>">Add Customer</a>
                <a href="view_customers.php" class="<?= ($currentPage == 'view_customers.php') ? 'active' : '' ?>">View Customers</a>
            </div>
        </div>

        <!-- Booking Module -->
        <div class="nav-group <?= in_array($currentPage, ['booking.php', 'view_bookings.php']) ? 'open' : '' ?>">
            <div class="nav-item has-submenu">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Bookings</span>
                <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            <div class="submenu">
                <a href="booking.php" class="<?= ($currentPage == 'booking.php') ? 'active' : '' ?>">New Booking</a>
                <a href="view_bookings.php" class="<?= ($currentPage == 'view_bookings.php') ? 'active' : '' ?>">All Bookings</a>
            </div>
        </div>

        <div class="menu-section-label">Operations</div>

        <a href="checkin.php" class="nav-item <?= ($currentPage == 'checkin.php') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
            <span>Check-In</span>
        </a>

        <a href="checkout.php" class="nav-item <?= ($currentPage == 'checkout.php') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Check-Out</span>
        </a>

        <div class="menu-section-label">Account</div>

        <a href="profile.php" class="nav-item <?= ($currentPage == 'profile.php') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>My Profile</span>
        </a>

        <a href="change_password.php" class="nav-item <?= ($currentPage == 'change_password.php') ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <span>Change Password</span>
        </a>

        <a href="logout.php" class="nav-item nav-logout">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Logout</span>
        </a>
    </nav>
</aside>
