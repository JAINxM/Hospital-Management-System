/* ============================================================
   MAIN JAVASCRIPT ENGINE
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Toggle Setup
    const appContainer = document.querySelector('.app-container');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function () {
            if (window.innerWidth <= 1024) {
                appContainer.classList.toggle('sidebar-open');
            } else {
                appContainer.classList.toggle('sidebar-collapsed');
            }
        });
    }

    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function () {
            appContainer.classList.remove('sidebar-open');
        });
    }

    // 2. Sidebar Submenu Accordion
    const navSubmenuHeaders = document.querySelectorAll('.nav-group .has-submenu');
    navSubmenuHeaders.forEach(header => {
        header.addEventListener('click', function () {
            const parentGroup = this.parentElement;
            parentGroup.classList.toggle('open');
        });
    });

    // 3. User Profile Dropdown
    const profileDropdownBtn = document.getElementById('profileDropdownBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileDropdownBtn && profileDropdown) {
        profileDropdownBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!profileDropdown.contains(e.target) && !profileDropdownBtn.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
    }

    // 4. Global Search Handler
    const globalSearchInput = document.getElementById('globalSearchInput');
    const globalSearchResults = document.getElementById('globalSearchResults');

    if (globalSearchInput && globalSearchResults) {
        let debounceTimer;

        globalSearchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                globalSearchResults.style.display = 'none';
                globalSearchResults.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetchGlobalSearch(query);
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!globalSearchInput.contains(e.target) && !globalSearchResults.contains(e.target)) {
                globalSearchResults.style.display = 'none';
            }
        });
    }

    function fetchGlobalSearch(query) {
        // Send search query to backend endpoint or simulate live search
        fetch('view_bookings.php?search_api=' + encodeURIComponent(query))
            .then(res => res.json())
            .catch(() => null)
            .then(data => {
                if (!data || data.length === 0) {
                    globalSearchResults.innerHTML = '<div class="search-result-item" style="color:var(--text-muted);">No matching results found</div>';
                } else {
                    let html = '';
                    data.forEach(item => {
                        html += `<div class="search-result-item" onclick="window.location.href='${item.url}'">
                            <span class="search-result-badge badge-${item.type_class}">${item.type}</span>
                            <div>
                                <div style="font-weight:600;">${item.title}</div>
                                <div style="font-size:0.775rem; color:var(--text-muted);">${item.subtitle}</div>
                            </div>
                        </div>`;
                    });
                    globalSearchResults.innerHTML = html;
                }
                globalSearchResults.style.display = 'block';
            });
    }

    // 5. Image Upload Preview Handler
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function () {
            const previewId = this.getAttribute('data-preview');
            const previewImg = document.getElementById(previewId);
            if (previewImg && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
});
