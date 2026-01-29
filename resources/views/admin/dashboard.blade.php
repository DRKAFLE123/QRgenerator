<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QR Generator</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/css/admin.css">
    <style>
        @media (max-width: 600px) {
            .modal-content {
                margin: 5% auto !important;
                width: 95% !important;
                padding: 1.5rem !important;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 2rem;
            border: 1px solid #888;
            width: 90%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
        }

        .modal-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #636E72;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .modal-close-btn:hover {
            color: #2D3436;
        }

        .modal-icon {
            font-size: 3rem;
            color: #FF6B6B;
            margin-bottom: 1rem;
        }

        .modal h2 {
            margin-top: 0;
            color: #2D3436;
        }

        .modal p {
            color: #636E72;
            margin-bottom: 2rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-cancel {
            background: #E9ECEF;
            color: #2D3436;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-confirm {
            background: #FF6B6B;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-brand">
            <i class="fa-solid fa-shield-halved"></i> Admin Dashboard
        </div>
        <div class="nav-center">
            QR Code Generator
            <button onclick="openPlatformModal()"
                style="margin-left: 20px; background: rgba(255,255,255,0.2); border: none; padding: 5px 10px; color: white; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                <i class="fa-solid fa-list"></i> Manage Platforms
            </button>
        </div>
        <div class="nav-user">
            <span>{{ Auth::user()->name }}</span>
            <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="container">
        <!-- Stats Sections -->
        <div class="stats-container">
            <!-- Top Section: Total -->
            <div class="stats-hero">
                <div class="stat-card hero-card active" onclick="filterBy('total', this)">
                    <div class="icon"><i class="fa-solid fa-qrcode"></i></div>
                    <div class="info">
                        <h3 id="stat-total">{{ $stats['total'] }}</h3>
                        <p>Total Bio Pages</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Filters -->
            <div class="stats-grid">
                <div class="stat-card green" onclick="filterBy('active', this)">
                    <div class="icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="info">
                        <h3 id="stat-active">{{ $stats['active'] }}</h3>
                        <p>Active</p>
                    </div>
                </div>
                <div class="stat-card orange" onclick="filterBy('inactive', this)">
                    <div class="icon"><i class="fa-solid fa-circle-pause"></i></div>
                    <div class="info">
                        <h3 id="stat-inactive">{{ $stats['inactive'] }}</h3>
                        <p>Inactive</p>
                    </div>
                </div>
                <div class="stat-card success" onclick="filterBy('paid', this)">
                    <div class="icon"><i class="fa-solid fa-coins"></i></div>
                    <div class="info">
                        <h3 id="stat-paid">{{ $stats['paid'] }}</h3>
                        <p>Paid</p>
                    </div>
                </div>
                <div class="stat-card danger" onclick="filterBy('unpaid', this)">
                    <div class="icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                    <div class="info">
                        <h3 id="stat-unpaid">{{ $stats['unpaid'] }}</h3>
                        <p>Unpaid</p>
                    </div>
                </div>
                <div class="stat-card warning" onclick="filterBy('expiring', this)">
                    <div class="icon"><i class="fa-solid fa-hourglass-half"></i></div>
                    <div class="info">
                        <h3 id="stat-expiring">{{ $stats['expiring_soon'] }}</h3>
                        <p>Expiring Soon</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="toolbar">
            <div class="date-filter">
                <input type="date" id="startDate" class="date-input" placeholder="Start Date" max="{{ date('Y-m-d') }}"
                    onchange="filterByDate()">
                <span class="separator">to</span>
                <input type="date" id="endDate" class="date-input" placeholder="End Date" max="{{ date('Y-m-d') }}"
                    onchange="filterByDate()">
                <button class="btn-clear-date" onclick="clearDateFilter()" title="Clear Dates">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="search-form">
                <div class="search-input">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" name="search" placeholder="Search by organization..."
                        value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" id="selectAll" class="custom-checkbox"
                                    onchange="toggleSelectAll()">
                            </th>
                            <th>No.</th>
                            <th>Organization</th>
                            <th>Created Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('admin.partials.bio-table-rows')
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bulk Action Bar -->
    <div id="bulkActionBar" class="bulk-action-bar">
        <div class="selected-info">
            <span class="count-badge" id="selectedCount">0</span>
            <span class="selected-text">Items Selected</span>
        </div>
        <div class="bulk-actions">
            <button class="btn-bulk-delete" onclick="confirmBulkDelete()">
                <i class="fa-solid fa-trash"></i> Delete Selected
            </button>
        </div>
    </div>

    <!-- Custom Expiry Modal -->
    <div id="expiryModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon" style="color: #F08C00;">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <h2>Set Expiry Date</h2>
            <p>Update expiry for <strong id="expiryOrgName"></strong></p>
            <div class="form-group" style="margin-bottom: 2rem; text-align: left;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Choose Expiry Date</label>
                <input type="date" id="customExpiryDate" class="date-input" style="width: 100%; padding: 0.8rem;">
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeExpiryModal()">Cancel</button>
                <button class="btn-confirm" onclick="saveCustomExpiry()" style="background: #667eea;">Save
                    Changes</button>
            </div>
        </div>
    </div>

    <!-- Subscription Renewal Modal -->
    <div id="renewModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon" style="color: #667eea;">
                <i class="fa-solid fa-calendar-plus"></i>
            </div>
            <h2>Renew Subscription</h2>
            <p>Extend the subscription for <strong id="renewOrgName"></strong></p>
            <div class="renewal-options" style="margin-bottom: 2rem;">
                <button class="btn-renew-option" onclick="executeRenewal(180)">
                    <span class="days">180 Days</span>
                    <span class="desc">6 Months Subscription</span>
                </button>
                <button class="btn-renew-option" onclick="executeRenewal(365)">
                    <span class="days">365 Days</span>
                    <span class="desc">1 Year Subscription</span>
                </button>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeRenewModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <div class="modal-icon warning" style="color: #ff6b6b;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h2>Delete Bio Page?</h2>
            <p style="color: #636E72; margin-bottom: 1.5rem;">
                This action will <strong>ARCHIVE</strong> the Bio Page. It can be restored later if needed, but the QR
                Code will stop working immediately.
            </p>

            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-confirm delete" id="confirmDeleteBtn"
                    style="background-color: #ff6b6b; color: white;">Archive Bio
                    Page</button>
            </div>
        </div>
    </div>

    <!-- Platform Manager Modal -->
    <div id="platformModal" class="modal">
        <div class="modal-content" style="max-width: 800px; width: 95%;">
            <div class="modal-header"
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">Manage Platforms</h2>
                <button onclick="closePlatformModal()" class="modal-close-btn"
                    style="position: static;">&times;</button>
            </div>

            <div style="display: flex; gap: 20px;">
                <!-- List Section -->
                <div
                    style="flex: 1; border-right: 1px solid #ddd; padding-right: 20px; max-height: 60vh; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 10px; text-align: left;">Label</th>
                                <th style="padding: 10px; text-align: left;">Icon</th>
                                <th style="padding: 10px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="platformTableBody">
                            @foreach($platforms as $platform)
                                <tr id="platform-row-{{ $platform->id }}">
                                    <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $platform->label }}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><i
                                            class="{{ $platform->icon }}"></i> {{ $platform->icon }}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">
                                        <button onclick='editPlatform(@json($platform))'
                                            style="background: none; border: none; color: #3498db; cursor: pointer;"><i
                                                class="fa-solid fa-pen"></i></button>
                                        <button onclick="deletePlatform({{ $platform->id }})"
                                            style="background: none; border: none; color: #e74c3c; cursor: pointer;"><i
                                                class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Form Section -->
                <div style="flex: 0 0 300px;">
                    <h3 id="platformFormTitle" style="margin-top: 0;">Add New Platform</h3>
                    <form id="platformForm" onsubmit="savePlatform(event)">
                        <input type="hidden" id="platformId">

                        <div class="form-group" style="margin-bottom: 15px; text-align: left;">
                            <label style="display: block; margin-bottom: 5px;">Key (Unique ID)</label>
                            <input type="text" id="platformKey" class="form-input" required placeholder="e.g. snapchat"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 15px; text-align: left;">
                            <label style="display: block; margin-bottom: 5px;">Label</label>
                            <input type="text" id="platformLabel" class="form-input" required
                                placeholder="e.g. Snapchat"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 15px; text-align: left;">
                            <label style="display: block; margin-bottom: 5px;">Icon Class (FontAwesome)</label>
                            <input type="text" id="platformIcon" class="form-input" required
                                placeholder="e.g. fa-brands fa-snapchat"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 15px; text-align: left;">
                            <label style="display: block; margin-bottom: 5px;">Type</label>
                            <select id="platformType" class="form-input"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="url">URL</option>
                                <option value="phone">Phone</option>
                                <option value="sms">SMS</option>
                                <option value="text">Text</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 15px; text-align: left;">
                            <label style="display: block; margin-bottom: 5px;">Placeholder</label>
                            <input type="text" id="platformPlaceholder" class="form-input"
                                placeholder="e.g. https://snapchat.com/..."
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>

                        <button type="submit" class="btn-confirm" style="width: 100%;">Save Platform</button>
                        <button type="button" onclick="resetPlatformForm()" class="btn-cancel"
                            style="width: 100%; margin-top: 10px;">Reset</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Edit Bio Page Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content" style="max-width: 600px; text-align: left;">
            <div class="modal-icon" style="color: #2196f3; font-size: 2.5rem; text-align: center;">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <h2 style="text-align: center;">Edit Bio Page</h2>
            <p style="text-align: center;">Update content for <strong id="editOrgNameDisplay"></strong></p>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Organization Name</label>
                <input type="text" id="editOrgName" class="form-input"
                    style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Main Website (Optional)</label>
                <input type="url" id="editWebsite" class="form-input"
                    style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px;">
            </div>

            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Update Logo</label>
                    <input type="file" id="editLogo" accept="image/*" class="form-input"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 6px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Update Cover</label>
                    <input type="file" id="editCover" accept="image/*" class="form-input"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 6px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Theme</label>
                <select id="editTheme" class="form-input"
                    style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px;">
                    <option value="modern">Modern</option>
                    <option value="vibrant">Vibrant</option>
                    <option value="business">Business</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Links & Content</label>
                <div id="editLinksContainer"
                    style="max-height: 300px; overflow-y: auto; margin-bottom: 10px; border: 1px solid #eee; padding: 10px; border-radius: 6px;">
                    <!-- Links will be added here -->
                </div>
                <button onclick="addEditLinkRow()"
                    style="background: #e3f2fd; color: #2196f3; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    <i class="fa-solid fa-plus"></i> Add Social Link
                </button>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Custom Links</label>
                <div id="editCustomLinksContainer"
                    style="max-height: 300px; overflow-y: auto; margin-bottom: 10px; border: 1px solid #eee; padding: 10px; border-radius: 6px;">
                    <!-- Custom Links will be added here -->
                </div>
                <button onclick="addCustomLinkRow()"
                    style="background: #efebe9; color: #795548; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    <i class="fa-solid fa-plus"></i> Add Custom Link
                </button>
            </div>

            <div class="modal-actions" style="margin-top: 2rem;">
                <button class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button class="btn-confirm" onclick="saveEditContent()" style="background: #2196f3;">Save
                    Changes</button>
            </div>
        </div>
    </div>

    <!-- Analytics Modal -->
    <div id="analyticsModal" class="modal">
        <div class="modal-content" style="max-width: 700px; text-align: left; position: relative;">
            <a id="exportAnalyticsBtnTop" href="#" class="btn-icon" title="Export CSV"
                style="position: absolute; top: 15px; right: 55px; background: #10B981; color: white; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%; text-decoration: none; font-size: 16px;">
                <i class="fa-solid fa-download"></i>
            </a>
            <button onclick="closeAnalyticsModal()" class="modal-close-btn">&times;</button>
            <div class="modal-icon" style="color: #6C5CE7; font-size: 2.5rem; text-align: center;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <h2 style="text-align: center;">Bio Page Analytics</h2>
            <p style="text-align: center; margin-bottom: 20px;">Stats for <strong id="analyticsNameDisplay"></strong>
            </p>

            <div style="text-align: center; margin-bottom: 30px;">
                <span style="font-size: 1.2rem; color: #636E72;">Total Visits:</span>
                <strong id="analyticsTotalDisplay"
                    style="font-size: 2rem; color: #2D3436; margin-left: 10px;">0</strong>
            </div>

            <div style="height: 300px; position: relative; margin-bottom: 30px;">
                <canvas id="analyticsChart"></canvas>
            </div>

            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <h3 style="text-align: center; margin-bottom: 15px; color: #636E72;">Device Breakdown</h3>
                    <div style="height: 200px; position: relative;">
                        <canvas id="deviceChart"></canvas>
                    </div>
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <h3 style="text-align: center; margin-bottom: 15px; color: #636E72;">Top Locations</h3>
                    <div id="locationList"
                        style="height: 200px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <!-- Location items will be added here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let deleteId = null;
        let currentStatus = '';
        let currentPayment = '';
        let currentSearch = '';

        // Toast Function
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';

            toast.innerHTML = `
                <i class="fa-solid ${icon}"></i>
                <span>${message}</span>
            `;

            container.appendChild(toast);

            // Trigger animation
            setTimeout(() => toast.classList.add('show'), 10);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Check for session messages on load
        @if(session('success'))         showToast("{{ session('success') }}", 'success');
        @endif

        @if(session('error'))         showToast("{{ session('error') }}", 'error');
        @endif

        // Check for client-side storage messages (e.g. after reload)
        if (sessionStorage.getItem('toastMessage')) {
            showToast(sessionStorage.getItem('toastMessage'), 'success');
            sessionStorage.removeItem('toastMessage');
        }

        // Real-time Search
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('tableBody');
        let searchTimeout = null;

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            currentSearch = this.value;
            fetchData();
        });

        // Date Filter Functions
        function filterByDate() {
            fetchData();
        }

        function clearDateFilter() {
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            fetchData();
        }

        // Add robust event listeners for date inputs
        document.getElementById('startDate').addEventListener('input', filterByDate);
        document.getElementById('endDate').addEventListener('input', filterByDate);

        // Filter Function

        // Filter Function
        function filterBy(type, card) {
            // Update active state visuals
            document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');

            // Reset filters logic
            currentStatus = '';
            currentPayment = '';

            if (type === 'active' || type === 'inactive' || type === 'expiring') {
                currentStatus = type;
            } else if (type === 'paid' || type === 'unpaid') {
                currentPayment = type;
            }
            // 'total' leaves activeStatus/Payment empty, effectively clearing filters

            fetchData();
        }

        function fetchData() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                let params = new URLSearchParams();
                if (currentSearch) params.append('search', currentSearch);
                if (currentStatus) params.append('status', currentStatus);
                if (currentPayment) params.append('payment_status', currentPayment);

                // Add date parameters
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                if (startDate) params.append('start_date', startDate);
                if (endDate) params.append('end_date', endDate);

                fetch(`{{ route('admin.dashboard') }}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.text())
                    .then(html => {
                        tableBody.innerHTML = html;
                    });
            }, 300);
        }

        // Update Stats Function
        function updateStats(stats) {
            if (!stats) return;
            document.getElementById('stat-total').innerText = stats.total;
            document.getElementById('stat-active').innerText = stats.active;
            document.getElementById('stat-inactive').innerText = stats.inactive;
            document.getElementById('stat-paid').innerText = stats.paid;
            document.getElementById('stat-unpaid').innerText = stats.unpaid;
            if (document.getElementById('stat-expiring')) {
                document.getElementById('stat-expiring').innerText = stats.expiring_soon;
            }
        }

        // Status Toggle
        function toggleStatus(id, checkbox) {
            const status = checkbox.checked ? 'active' : 'inactive';

            fetch(`/admin/bio/${id}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    _method: 'PATCH',
                    status: status
                })
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    showToast('Status updated successfully');
                    if (data.stats) updateStats(data.stats);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to update status', 'error');
                    checkbox.checked = !checkbox.checked; // Revert
                });
        }

        // Payment Update
        function updatePayment(id, select) {
            const status = select.value;
            select.className = `payment-select ${status}`;

            fetch(`/admin/bio/${id}/payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    _method: 'PATCH',
                    payment_status: status
                })
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    showToast('Payment status updated successfully');
                    if (data.stats) updateStats(data.stats);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to update payment status', 'error');
                });
        }

        // Renewal Modal Functions
        let renewId = null;

        function openRenewModal(id, name) {
            renewId = id;
            document.getElementById('renewOrgName').innerText = name;
            document.getElementById('renewModal').style.display = 'block';
        }

        function closeRenewModal() {
            document.getElementById('renewModal').style.display = 'none';
            renewId = null;
        }

        function executeRenewal(days) {
            if (!renewId) return;

            fetch(`/admin/bio/${renewId}/renew`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ days: days })
            })
                .then(response => {
                    if (!response.ok) throw new Error('Renewal failed');
                    return response.json();
                })
                .then(data => {
                    showToast(data.message);
                    if (data.stats) updateStats(data.stats);
                    fetchData(); // Refresh table
                    closeRenewModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to renew subscription', 'error');
                });
        }

        // Custom Expiry Modal Functions
        let editExpiryId = null;

        function openExpiryModal(id, name, currentDate) {
            editExpiryId = id;
            document.getElementById('expiryOrgName').innerText = name;
            if (currentDate) {
                // currentDate might be like "Dec 27, 2025" or a raw timestamp.
                // We'll try to parse it or just leave empty if it's not ISO format.
                // Actually, it's better to pass the raw date from the template.
                document.getElementById('customExpiryDate').value = currentDate;
            }
            document.getElementById('expiryModal').style.display = 'block';
        }

        function closeExpiryModal() {
            document.getElementById('expiryModal').style.display = 'none';
            editExpiryId = null;
        }

        function saveCustomExpiry() {
            if (!editExpiryId) return;
            const newDate = document.getElementById('customExpiryDate').value;

            if (!newDate) {
                showToast('Please select a valid date', 'error');
                return;
            }

            fetch(`/admin/bio/${editExpiryId}/expiry`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    _method: 'PATCH',
                    expiry_date: newDate
                })
            })
                .then(response => {
                    if (!response.ok) throw new Error('Update failed');
                    return response.json();
                })
                .then(data => {
                    showToast(data.message);
                    if (data.stats) updateStats(data.stats);
                    fetchData(); // Refresh table
                    closeExpiryModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to update expiry date', 'error');
                });
        }

        // Custom Delete Modal
        function confirmDelete(id) {
            deleteId = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            deleteId = null;  // Reset bulk handler (if any) to default or just clear onclick
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.onclick = null;
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            if (!deleteId) return;

            fetch(`/admin/bio/${deleteId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Bio Page archived successfully');
                        if (data.stats) updateStats(data.stats);
                        fetchData(); // Refresh table
                        closeModal();
                    } else {
                        showToast(data.message || 'Failed to delete page', 'error');
                        closeModal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                    closeModal();
                });
        });

        // Close modal on outside click
        window.onclick = function (event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        // Bulk Selection Logic
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkState();
        }

        function updateBulkState() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const actionBar = document.getElementById('bulkActionBar');
            const countSpan = document.getElementById('selectedCount');
            const selectAll = document.getElementById('selectAll');
            const totalCheckboxes = document.querySelectorAll('.row-checkbox');

            if (checkboxes.length > 0) {
                actionBar.classList.add('show');
                countSpan.innerText = checkboxes.length;
            } else {
                actionBar.classList.remove('show');
            }

            // Update Select All state
            if (checkboxes.length === totalCheckboxes.length && totalCheckboxes.length > 0) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else if (checkboxes.length > 0) {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
        }

        // Bulk Delete Functions
        function confirmBulkDelete() {
            document.getElementById('deleteModal').querySelector('h2').innerText = 'Delete Selected Pages?';
            document.getElementById('deleteModal').querySelector('p').innerText = 'Are you sure you want to delete the selected Bio Pages? This action cannot be undone.';
            // Change button action to bulk delete
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.onclick = executeBulkDelete; // Attach new handler
            document.getElementById('deleteModal').style.display = 'block';
        }

        function executeBulkDelete() {
            const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);

            fetch('{{ route('admin.bio.bulk-delete') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ids: selectedIds })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        if (data.stats) updateStats(data.stats);
                        fetchData();
                        document.getElementById('selectAll').checked = false;
                        document.getElementById('bulkActionBar').classList.remove('show');
                        closeModal();
                    } else {
                        showToast(data.message || 'Failed to delete selected pages', 'error');
                        closeModal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                    closeModal();
                });
        }
        // Dropdown Logic (Delegated for AJAX content)
        document.addEventListener('click', function (e) {
            // Check if clicked button is a download trigger
            const btn = e.target.closest('.btn-icon.download');

            if (btn) {
                e.stopPropagation(); // Prevent closing immediately
                e.preventDefault(); // Prevent default button behavior

                const dropdown = btn.nextElementSibling;
                const isVisible = dropdown.classList.contains('show');

                // Close all other open dropdowns
                document.querySelectorAll('.dropdown-content.show').forEach(d => d.classList.remove('show'));

                // Toggle current
                if (!isVisible) {
                    dropdown.classList.add('show');
                }
            } else {
                // Clicked outside, close all
                document.querySelectorAll('.dropdown-content.show').forEach(d => d.classList.remove('show'));
            }
        });

        // Client-side PNG Download (Bypasses server Imagick requirement)
        function downloadPng(id, name) {
            const url = `/admin/bio/${id}/download-qr`;

            fetch(url)
                .then(response => response.text())
                .then(svgContent => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const img = new Image();

                    // Cleanup SVG for Image loading
                    const blob = new Blob([svgContent], { type: 'image/svg+xml' });
                    const url = URL.createObjectURL(blob);

                    img.onload = function () {
                        // Set canvas to high res
                        canvas.width = 1000;
                        canvas.height = 1000;

                        // Fill white background (transparent SVG -> black PNG issue)
                        ctx.fillStyle = "#FFFFFF";
                        ctx.fillRect(0, 0, canvas.width, canvas.height);

                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                        // Download
                        const a = document.createElement('a');
                        a.download = `qr-${name}.png`;
                        a.href = canvas.toDataURL('image/png');
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);

                        URL.revokeObjectURL(url);

                        showToast('QR Code downloaded as PNG', 'success');
                    };

                    img.onerror = function () {
                        showToast('Failed to convert QR to PNG', 'error');
                    };

                    img.src = url;
                })
                .catch(err => {
                    console.error(err);
                    showToast('Failed to download QR', 'error');
                });
        }


        // Edit Modal Functions
        let editId = null;

        function openEditModal(page) {
            editId = page.id;
            document.getElementById('editOrgName').value = page.name;
            document.getElementById('editWebsite').value = page.website || '';
            document.getElementById('editTheme').value = page.theme || 'modern';

            // Clear and Populate Social Links
            const socialContainer = document.getElementById('editLinksContainer');
            socialContainer.innerHTML = '';

            // Clear and Populate Custom Links
            const customContainer = document.getElementById('editCustomLinksContainer');
            customContainer.innerHTML = '';

            let links = [];
            try {
                links = typeof page.links === 'string' ? JSON.parse(page.links) : page.links;
            } catch (e) { links = []; }

            if (links) {
                links.forEach(link => {
                    if (link.platform === 'custom') {
                        addCustomLinkRow(link);
                    } else {
                        addEditLinkRow(link);
                    }
                });
            }

            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            editId = null;
        }

        // Platform Config replaced by dynamic PLATFORMS variable


        function addCustomLinkRow(data = { label: '', url: '' }) {
            const container = document.getElementById('editCustomLinksContainer');
            const row = document.createElement('div');
            row.className = 'custom-link-row';
            row.style.display = 'flex';
            row.style.gap = '10px';
            row.style.marginBottom = '10px';

            row.innerHTML = `
                <input type="text" class="form-input custom-label" value="${data.label || ''}" placeholder="Button Label" style="flex:1; padding: 0.5rem; border:1px solid #ddd; border-radius:4px;">
                <input type="text" class="form-input custom-url" value="${data.url || ''}" placeholder="Destination URL" style="flex:2; padding: 0.5rem; border:1px solid #ddd; border-radius:4px;">
                <button type="button" onclick="this.parentElement.remove()" style="background:#ff6b6b; color:white; border:none; padding:0.5rem; border-radius:4px; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
            `;
            container.appendChild(row);

            // Auto-scroll to bottom
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 10);
        }

        function saveEditContent() {
            if (!editId) return;

            const name = document.getElementById('editOrgName').value;
            const website = document.getElementById('editWebsite').value;
            const theme = document.getElementById('editTheme').value;
            const logoFile = document.getElementById('editLogo').files[0];
            const coverFile = document.getElementById('editCover').files[0];

            const links = [];
            // Social Links
            document.querySelectorAll('.edit-link-row').forEach(row => {
                const platform = row.querySelector('.edit-platform').value;
                const url = row.querySelector('.edit-url').value;
                const label = row.querySelector('.edit-label').value;
                if (url) links.push({ platform, url, label });
            });

            // Custom Links
            document.querySelectorAll('.custom-link-row').forEach(row => {
                const label = row.querySelector('.custom-label').value;
                const url = row.querySelector('.custom-url').value;
                if (label && url) {
                    links.push({ platform: 'custom', label, url });
                }
            });

            // Use FormData for file uploads
            const formData = new FormData();
            formData.append('name', name);
            formData.append('website', website);
            formData.append('theme', theme);
            formData.append('links', JSON.stringify(links));

            if (logoFile) formData.append('logo', logoFile);
            if (coverFile) formData.append('cover', coverFile);

            fetch(`/admin/bio/${editId}/update`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                    // Content-Type is auto-set for FormData
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        fetchData();
                        closeEditModal();
                    } else {
                        showToast('Update failed', 'error');
                    }
                })
                .catch(error => {
                    console.error(error);
                    showToast('Error updating content', 'error');
                });
        }

        // Analytics Functions
        let analyticsChart = null;
        let deviceChart = null;

        function openAnalyticsModal(id, name) {
            document.getElementById('analyticsNameDisplay').textContent = name;
            document.getElementById('analyticsModal').style.display = 'block';

            // Set export link
            document.getElementById('exportAnalyticsBtnTop').href = `/admin/bio/${id}/analytics/export`;

            // Fetch analytics data
            fetch(`/admin/bio/${id}/analytics`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('analyticsTotalDisplay').textContent = data.total_visits;

                        // --- Main Visits Chart ---
                        const labels = data.chart_data.map(item => item.date);
                        const values = data.chart_data.map(item => item.count);

                        if (analyticsChart) analyticsChart.destroy();
                        
                        const ctx = document.getElementById('analyticsChart').getContext('2d');
                        analyticsChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Visits',
                                    data: values,
                                    borderColor: '#6C5CE7',
                                    backgroundColor: 'rgba(108, 92, 231, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                            }
                        });

                        // --- Device Doughnut Chart ---
                        if (deviceChart) deviceChart.destroy();

                        const deviceLabels = data.device_data.map(item => item.device_type.toUpperCase());
                        const deviceValues = data.device_data.map(item => item.count);
                        const deviceColors = ['#FF6B6B', '#48dbfb', '#1dd1a1', '#feca57'];

                        const ctxDevice = document.getElementById('deviceChart').getContext('2d');
                        deviceChart = new Chart(ctxDevice, {
                            type: 'doughnut',
                            data: {
                                labels: deviceLabels,
                                datasets: [{
                                    data: deviceValues,
                                    backgroundColor: deviceColors,
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { position: 'right' } }
                            }
                        });


                        // --- Top Locations List ---
                        const locationList = document.getElementById('locationList');
                        locationList.innerHTML = '';
                        
                        if (data.location_data && data.location_data.length > 0) {
                            data.location_data.forEach(loc => {
                                const item = document.createElement('div');
                                item.style.display = 'flex';
                                item.style.justifyContent = 'space-between';
                                item.style.padding = '8px';
                                item.style.borderBottom = '1px solid #f0f0f0';
                                item.innerHTML = `
                                    <span><i class="fa-solid fa-location-dot" style="color:#ff7675; margin-right:5px;"></i> ${loc.city}, ${loc.country}</span>
                                    <strong>${loc.count}</strong>
                                `;
                                locationList.appendChild(item);
                            });
                        } else {
                            locationList.innerHTML = '<p style="text-align:center; color:#ccc; margin-top:20px;">No location data</p>';
                        }

                    } else {
                        showToast('Failed to load analytics', 'error');
                    }
                })
                .catch(error => {
                    console.error(error);
                    showToast('Error loading analytics', 'error');
                });
        }

        function closeAnalyticsModal() {
            document.getElementById('analyticsModal').style.display = 'none';
            if (analyticsChart) {
                analyticsChart.destroy();
                analyticsChart = null;
            }
            if (deviceChart) {
                deviceChart.destroy();
                deviceChart = null;
            }
        }
        // Injected Platforms from Backend
        const PLATFORMS = @json($platforms);

        function addEditLinkRow(data = { platform: 'website', label: '', url: '' }) {
            const container = document.getElementById('editLinksContainer');
            const row = document.createElement('div');
            row.className = 'edit-link-row';
            row.style.display = 'flex';
            row.style.gap = '10px';
            row.style.marginBottom = '10px';

            let optionsHtml = '';

            // Generate options from injected PLATFORMS
            if (PLATFORMS && PLATFORMS.length > 0) {
                PLATFORMS.forEach(p => {
                    optionsHtml += `<option value="${p.key}" ${data.platform === p.key ? 'selected' : ''}>${p.label}</option>`;
                });
            } else {
                // Fallback if empty
                optionsHtml = '<option value="website">Website</option>';
            }


            row.innerHTML = `
                <select class="form-input edit-platform" style="flex:1; padding: 0.5rem; border:1px solid #ddd; border-radius:4px;">
                    ${optionsHtml}
                </select>
                <input type="text" class="form-input edit-label" value="${data.label || ''}" placeholder="Label (Optional)" style="width: 120px; padding: 0.5rem; border:1px solid #ddd; border-radius:4px;">
                <input type="text" class="form-input edit-url" value="${data.url}" placeholder="URL / Number / Text" style="flex:2; padding: 0.5rem; border:1px solid #ddd; border-radius:4px;">
                <button type="button" onclick="this.parentElement.remove()" style="background:#ff6b6b; color:white; border:none; padding:0.5rem; border-radius:4px; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
            `;
            container.appendChild(row);
            // Auto-scroll to bottom
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 10);
        }

        // --- Platform Manager JS ---
        function openPlatformModal() {
            document.getElementById('platformModal').style.display = 'block';
            resetPlatformForm();
        }

        function closePlatformModal() {
            document.getElementById('platformModal').style.display = 'none';
        }

        function resetPlatformForm() {
            document.getElementById('platformForm').reset();
            document.getElementById('platformId').value = '';
            document.getElementById('platformKey').disabled = false;
            document.getElementById('platformFormTitle').innerText = 'Add New Platform';
        }

        function editPlatform(platform) {
            document.getElementById('platformId').value = platform.id;
            document.getElementById('platformKey').value = platform.key;
            document.getElementById('platformKey').disabled = true; // Key cannot be changed
            document.getElementById('platformLabel').value = platform.label;
            document.getElementById('platformIcon').value = platform.icon;
            document.getElementById('platformType').value = platform.type;
            document.getElementById('platformPlaceholder').value = platform.placeholder || '';
            document.getElementById('platformFormTitle').innerText = 'Edit Platform';
        }

        function savePlatform(event) {
            event.preventDefault();
            const id = document.getElementById('platformId').value;
            const isEdit = !!id;
            const url = isEdit ? `/admin/platforms/${id}` : '/admin/platforms';
            const method = isEdit ? 'PUT' : 'POST';

            const payload = {
                key: document.getElementById('platformKey').value,
                label: document.getElementById('platformLabel').value,
                icon: document.getElementById('platformIcon').value,
                type: document.getElementById('platformType').value,
                placeholder: document.getElementById('platformPlaceholder').value
            };

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        location.reload(); // Reload to reflect changes in PHP rendered list and JS variable
                    } else {
                        showToast('Action failed', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Error saving platform', 'error');
                });
        }

        function deletePlatform(id) {
            if (!confirm('Are you sure you want to delete this platform?')) return;

            fetch(`/admin/platforms/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        document.getElementById(`platform-row-${id}`).remove();
                    } else {
                        showToast('Delete failed', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Error deleting platform', 'error');
                });
        }
    </script>
</body>

</html>