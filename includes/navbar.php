<?php
$user = getLoggedInUser();
?>
<header class="app-navbar">
    <div class="navbar-left">
        <button class="menu-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation">
            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <!-- Global Search Bar -->
        <div class="global-search-container">
            <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="globalSearchInput" class="global-search-input" placeholder="Search Customer, Room, Booking, Invoice..." autocomplete="off">
            <div id="globalSearchResults" class="search-results-dropdown"></div>
        </div>
    </div>

    <div class="navbar-right">
        <div class="navbar-date">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span><?= date('D, d M Y') ?></span>
        </div>

        <!-- User Profile Dropdown -->
        <div class="user-profile-menu">
            <button class="profile-trigger" id="profileDropdownBtn">
                <div class="user-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
                <div class="user-info">
                    <span class="user-name"><?= escape($user['full_name']) ?></span>
                    <span class="user-role"><?= ucfirst(escape($user['role'])) ?></span>
                </div>
                <svg class="dropdown-chevron" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="profile-dropdown" id="profileDropdown">
                <a href="profile.php" class="dropdown-item">
                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Profile Details
                </a>
                <a href="change_password.php" class="dropdown-item">
                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Security & Password
                </a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item text-danger">
                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Sign Out
                </a>
            </div>
        </div>
    </div>
</header>
