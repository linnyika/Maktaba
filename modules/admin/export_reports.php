<?php
session_start();
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../database/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maktaba | Export Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/reports.css">
</head>
<body>

<?php include '../../includes/admin_nav.php'; ?>

<div class="export-container">
    <div class="page-header">
        <h2><i class="bi bi-download me-2"></i>Export Reports Dashboard</h2>
        <p>Select a report type to generate and download in your preferred format</p>
    </div>

    <!-- Date Range Filter -->
    <div class="date-filter-container">
        <h5><i class="bi bi-calendar-range me-2"></i>Filter by Date Range (Optional)</h5>
        <form id="dateFilterForm" class="date-filter-form">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startDate" name="start_date">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" id="endDate" name="end_date">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="clearDates()">
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="applyDateFilter()">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                </div>
            </div>
        </form>
        <div id="dateFilterStatus" class="filter-status"></div>
    </div>

    <div class="export-options">
        <!-- Sales Report -->
        <div class="export-box">
            <div class="export-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="export-content">
                <h3>Sales Report</h3>
                <p>Includes M-Pesa payments, total revenue, transaction trends, and sales performance metrics.</p>
            </div>
            <div class="export-actions">
                <button class="btn-export excel" onclick="exportReport('sales', 'excel')">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </button>
                <button class="btn-export pdf" onclick="exportReport('sales', 'pdf')">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </button>
                <button class="btn-preview" onclick="previewReport('sales')">
                    <i class="bi bi-eye me-1"></i>Preview
                </button>
            </div>
        </div>

        <!-- Book Performance Report -->
        <div class="export-box">
            <div class="export-icon">
                <i class="bi bi-book"></i>
            </div>
            <div class="export-content">
                <h3>Book Performance</h3>
                <p>Shows loan frequency, popular categories, ratings, and inventory performance metrics.</p>
            </div>
            <div class="export-actions">
                <button class="btn-export excel" onclick="exportReport('books', 'excel')">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </button>
                <button class="btn-export pdf" onclick="exportReport('books', 'pdf')">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </button>
                <button class="btn-preview" onclick="previewReport('books')">
                    <i class="bi bi-eye me-1"></i>Preview
                </button>
            </div>
        </div>

        <!-- User Report -->
        <div class="export-box">
            <div class="export-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="export-content">
                <h3>User Report</h3>
                <p>Summarizes registered users, roles, activity levels, and user engagement metrics.</p>
            </div>
            <div class="export-actions">
                <button class="btn-export excel" onclick="exportReport('users', 'excel')">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </button>
                <button class="btn-export pdf" onclick="exportReport('users', 'pdf')">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </button>
                <button class="btn-preview" onclick="previewReport('users')">
                    <i class="bi bi-eye me-1"></i>Preview
                </button>
            </div>
        </div>

        <!-- Orders Report -->
        <div class="export-box">
            <div class="export-icon">
                <i class="bi bi-cart"></i>
            </div>
            <div class="export-content">
                <h3>Orders Report</h3>
                <p>Order history, status tracking, payment information, and fulfillment metrics.</p>
            </div>
            <div class="export-actions">
                <button class="btn-export excel" onclick="exportReport('orders', 'excel')">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </button>
                <button class="btn-export pdf" onclick="exportReport('orders', 'pdf')">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </button>
                <button class="btn-preview" onclick="previewReport('orders')">
                    <i class="bi bi-eye me-1"></i>Preview
                </button>
            </div>
        </div>

        <!-- Reservations Report -->
        <div class="export-box">
            <div class="export-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="export-content">
                <h3>Reservations</h3>
                <p>Book reservations, pickup schedules, status tracking, and reservation analytics.</p>
            </div>
            <div class="export-actions">
                <button class="btn-export excel" onclick="exportReport('reservations', 'excel')">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </button>
                <button class="btn-export pdf" onclick="exportReport('reservations', 'pdf')">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </button>
                <button class="btn-preview" onclick="previewReport('reservations')">
                    <i class="bi bi-eye me-1"></i>Preview
                </button>
            </div>
        </div>

        <!-- Inventory Report -->
        <div class="export-box">
            <div class="export-icon">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <div class="export-content">
                <h3>Inventory</h3>
                <p>Current stock levels, book availability, reserved quantities, and inventory status.</p>
            </div>
            <div class="export-actions">
                <button class="btn-export excel" onclick="exportReport('inventory', 'excel')">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </button>
                <button class="btn-export pdf" onclick="exportReport('inventory', 'pdf')">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </button>
                <button class="btn-preview" onclick="previewReport('inventory')">
                    <i class="bi bi-eye me-1"></i>Preview
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Generating Report...</h5>
                <p class="text-muted mb-0">Please wait while we prepare your download.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Current date filter state
let currentDateFilter = {
    start_date: '',
    end_date: ''
};

// Initialize date inputs with default values (last 30 days)
document.addEventListener('DOMContentLoaded', function() {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);
    
    document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
    document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
    
    currentDateFilter.start_date = startDate.toISOString().split('T')[0];
    currentDateFilter.end_date = endDate.toISOString().split('T')[0];
    
    updateFilterStatus();
});

function applyDateFilter() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        alert('Start date cannot be after end date.');
        return;
    }
    
    currentDateFilter.start_date = startDate;
    currentDateFilter.end_date = endDate;
    updateFilterStatus();
}

function clearDates() {
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    currentDateFilter.start_date = '';
    currentDateFilter.end_date = '';
    updateFilterStatus();
}

function updateFilterStatus() {
    const statusElement = document.getElementById('dateFilterStatus');
    const startDate = currentDateFilter.start_date;
    const endDate = currentDateFilter.end_date;
    
    if (startDate && endDate) {
        statusElement.innerHTML = `<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Filter: ${startDate} to ${endDate}</span>`;
    } else if (startDate || endDate) {
        statusElement.innerHTML = `<span class="badge bg-warning"><i class="bi bi-exclamation-triangle me-1"></i>Partial date range set</span>`;
    } else {
        statusElement.innerHTML = `<span class="badge bg-secondary"><i class="bi bi-info-circle me-1"></i>No date filter applied</span>`;
    }
}

function exportReport(type, format) {
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
    button.disabled = true;
    
    // Show loading modal
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    loadingModal.show();
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'export_reports_handler.php';
    form.style.display = 'none';
    
    // Add action and format
    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = type;
    form.appendChild(actionInput);
    
    const formatInput = document.createElement('input');
    formatInput.name = 'format';
    formatInput.value = format;
    form.appendChild(formatInput);
    
    // Add date filters if set
    if (currentDateFilter.start_date) {
        const startInput = document.createElement('input');
        startInput.name = 'start_date';
        startInput.value = currentDateFilter.start_date;
        form.appendChild(startInput);
    }
    
    if (currentDateFilter.end_date) {
        const endInput = document.createElement('input');
        endInput.name = 'end_date';
        endInput.value = currentDateFilter.end_date;
        form.appendChild(endInput);
    }
    
    document.body.appendChild(form);
    form.submit();
    
    // Reset button after a delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        loadingModal.hide();
    }, 10000);
}

function previewReport(type) {
    let url = `export_reports_handler.php?action=${type}&format=preview`;
    
    // Add date filters if set
    if (currentDateFilter.start_date) {
        url += `&start_date=${currentDateFilter.start_date}`;
    }
    if (currentDateFilter.end_date) {
        url += `&end_date=${currentDateFilter.end_date}`;
    }
    
    window.open(url, '_blank', 'width=1200,height=800,scrollbars=yes');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>