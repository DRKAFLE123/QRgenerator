<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QR Generator</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
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
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card blue active" onclick="filterBy('total', this)">
                <div class="icon"><i class="fa-solid fa-qrcode"></i></div>
                <div class="info">
                    <h3 id="stat-total">{{ $stats['total'] }}</h3>
                    <p>Total Bio Pages</p>
                </div>
            </div>
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
                            <th>Organization</th>
                            <th>Created Date</th>
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h2>Delete Bio Page?</h2>
            <p>Are you sure you want to delete this Bio Page? This action cannot be undone and will permanently remove
                all associated data.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-confirm" id="confirmDeleteBtn">Delete Project</button>
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
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif

        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
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

            if (type === 'active' || type === 'inactive') {
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

        // Custom Delete Modal
        function confirmDelete(id) {
            deleteId = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            deleteId = null;
            // Reset modal title and button to default single delete state
            document.getElementById('deleteModal').querySelector('h2').innerText = 'Delete Bio Page?';
            document.getElementById('deleteModal').querySelector('p').innerText = 'Are you sure you want to delete this Bio Page? This action cannot be undone and will permanently remove all associated data.';
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.onclick = null; // Remove standard handler (it's added via addEventListener below, but we need to ensure bulk handler is gone)
            // Wait, we attached bulk handler via .onclick property which overrides. 
            // BUT the original single delete is via addEventListener.
            // So if we set .onclick = null, the addEventListener one should still be there? 
            // NO, addEventListener is separate. 
            // ISSUE: If we set onclick, both might fire? 
            // Better approach: Use a single currentAction variable or clean up handlers.

            // Fix: Let's make sure the single delete event listener checks for `deleteId`. 
            // The bulk delete handler is directly assigned to onclick. 
            // We should clear the onclick so it doesn't fire for single deletes.
            confirmBtn.onclick = null;
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            if (!deleteId) return;

            fetch(`/admin/bio/${deleteId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
                .then(response => {
                    if (response.ok) {
                        sessionStorage.setItem('toastMessage', 'Bio Page deleted successfully');
                        window.location.reload();
                    } else {
                        showToast('Failed to delete page', 'error');
                        closeModal();
                    }
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
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ids: selectedIds })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        if (data.stats) updateStats(data.stats);

                        // Refresh table content (or remove rows locally)
                        // For simplicity, let's reload the table data via AJAX like search
                        fetchData();

                        // Reset UI
                        document.getElementById('selectAll').checked = false;
                        document.getElementById('bulkActionBar').classList.remove('show');
                        closeModal();
                    } else {
                        showToast('Failed to delete selected pages', 'error');
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
                    const blob = new Blob([svgContent], {type: 'image/svg+xml'});
                    const url = URL.createObjectURL(blob);
                    
                    img.onload = function() {
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
                    
                    img.onerror = function() {
                        showToast('Failed to convert QR to PNG', 'error');
                    };

                    img.src = url;
                })
                .catch(err => {
                    console.error(err);
                    showToast('Failed to download QR', 'error');
                });
        }


    </script>
</body>

</html>