<?php
require_once("../../includes/session_check.php");
require_once("../../includes/admin_check.php");
require_once("../../includes/report_helper.php");
require_once("../../database/config.php");

// Define getRecentReports if not already defined
if (!function_exists('getRecentReports')) {
  function getRecentReports($conn) {
    $sql = "SELECT report_id, generated_date, report_type, generated_by, status FROM reports ORDER BY generated_date DESC LIMIT 10";
    $result = $conn->query($sql);
    $reports = [];
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
      }
    }
    return $reports;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports Engine | Maktaba Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    /* Reports and Export Styles */
    .export-container {
        max-width: 1400px;
        margin: 40px auto;
        background: #fff;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .page-header {
        text-align: center;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e8f5f1;
    }

    .page-header h2 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 10px;
        font-size: 2.25rem;
    }

    .page-header p {
        color: #6c757d;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Export Options Grid */
    .export-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    .export-box {
        border: 2px solid #e8f5f1;
        border-radius: 15px;
        padding: 25px;
        background: #f8fdfb;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .export-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #78C2AD, #5da894);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .export-box:hover {
        box-shadow: 0 8px 25px rgba(120, 194, 173, 0.2);
        transform: translateY(-5px);
        border-color: #78C2AD;
    }

    .export-box:hover::before {
        opacity: 1;
    }

    .export-icon {
        text-align: center;
        margin-bottom: 20px;
    }

    .export-icon i {
        font-size: 2.5rem;
        color: #78C2AD;
        background: #e8f5f1;
        width: 80px;
        height: 80px;
        line-height: 80px;
        border-radius: 50%;
        display: inline-block;
    }

    .export-content {
        flex-grow: 1;
        margin-bottom: 25px;
    }

    .export-content h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        font-weight: 600;
        font-size: 1.4rem;
    }

    .export-content p {
        color: #5a6c7d;
        line-height: 1.6;
        margin: 0;
    }

    .export-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    /* Button Styles */
    .btn-export {
        border: none;
        padding: 12px 15px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .btn-export.excel {
        background: #217346;
        color: white;
    }

    .btn-export.excel:hover {
        background: #1a5c38;
        transform: translateY(-2px);
    }

    .btn-export.pdf {
        background: #dc3545;
        color: white;
    }

    .btn-export.pdf:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

    /* Success States */
    .export-success {
        border-color: #28a745 !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .export-container {
            margin: 20px;
            padding: 20px;
        }
        
        .export-options {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .export-box {
            padding: 20px;
        }
        
        .export-actions {
            grid-template-columns: 1fr;
        }
    }
  </style>
</head>
<body>
<?php include("../../includes/admin_nav.php"); ?>

<main class="container my-4">
  <div class="export-container">
    <div class="page-header">
      <h2><i class="bi bi-file-earmark-bar-graph"></i> Advanced Report Generator</h2>
      <p>Generate comprehensive system reports and export in multiple formats</p>
    </div>

    <!-- Quick Export Section -->
    <div class="mb-5">
      <h4 class="fw-bold text-success mb-4">🚀 Quick Export</h4>
      <div class="export-options">
        
        <!-- Audit Trail Reports -->
        <div class="export-box">
          <div class="export-icon">
            <i class="bi bi-clipboard-data"></i>
          </div>
          <div class="export-content">
            <h3>Audit Trail Report</h3>
            <p>Download complete system activity logs including user actions, timestamps, and IP addresses.</p>
          </div>
          <div class="export-actions">
            <button class="btn-export excel" onclick="downloadExport('audit_trail', 'excel')">
              <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
            <button class="btn-export pdf" onclick="downloadExport('audit_trail', 'pdf')">
              <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
          </div>
        </div>

        <!-- User Management Reports -->
        <div class="export-box">
          <div class="export-icon">
            <i class="bi bi-people"></i>
          </div>
          <div class="export-content">
            <h3>User Management Report</h3>
            <p>Export user accounts data including registration dates, roles, status, and profile information.</p>
          </div>
          <div class="export-actions">
            <button class="btn-export excel" onclick="downloadExport('users', 'excel')">
              <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
            <button class="btn-export pdf" onclick="downloadExport('users', 'pdf')">
              <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
          </div>
        </div>

        <!-- Reservation Reports -->
        <div class="export-box">
          <div class="export-icon">
            <i class="bi bi-book"></i>
          </div>
          <div class="export-content">
            <h3>Reservations Report</h3>
            <p>Generate detailed reservations report with book titles, user information, dates, and payment status.</p>
          </div>
          <div class="export-actions">
            <button class="btn-export excel" onclick="downloadExport('reservations', 'excel')">
              <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
            <button class="btn-export pdf" onclick="downloadExport('reservations', 'pdf')">
              <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
          </div>
        </div>

        <!-- Books Inventory Reports -->
        <div class="export-box">
          <div class="export-icon">
            <i class="bi bi-book-half"></i>
          </div>
          <div class="export-content">
            <h3>Books Inventory Report</h3>
            <p>Download complete books catalog with titles, authors, ISBN, categories, and availability status.</p>
          </div>
          <div class="export-actions">
            <button class="btn-export excel" onclick="downloadExport('books', 'excel')">
              <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
            <button class="btn-export pdf" onclick="downloadExport('books', 'pdf')">
              <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- TEST AREA - Simple Working Exports -->
    <div class="alert alert-info mb-5">
      <h5 class="fw-bold">🧪 Test Export System</h5>
      <p class="mb-3">Quick test to verify exports are working:</p>
      <div class="btn-group">
        <button class="btn btn-success" onclick="testExport('excel')">
          <i class="bi bi-file-earmark-excel"></i> Test Excel Export
        </button>
        <button class="btn btn-danger" onclick="testExport('pdf')">
          <i class="bi bi-file-earmark-pdf"></i> Test PDF Export
        </button>
      </div>
    </div>

    <!-- Custom Report Generator -->
    <div class="mb-5">
      <h4 class="fw-bold text-success mb-4">⚙️ Custom Report Generator</h4>
      <form method="GET" action="working_export.php" class="bg-light p-4 rounded">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-bold">Report Type</label>
            <select class="form-select" name="type" required>
              <option value="reservations">Reservations Report</option>
              <option value="audit_trail">Audit Trail Report</option>
              <option value="users">User Activity Report</option>
              <option value="books">Book Performance Report</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-bold">Start Date</label>
            <input type="date" class="form-control" name="start_date" required>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-bold">End Date</label>
            <input type="date" class="form-control" name="end_date" required>
          </div>

          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">
              <i class="bi bi-gear"></i> Generate
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Recent Reports Section -->
    <div>
      <h4 class="fw-bold text-success mb-3">📈 Recent Reports</h4>
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-success">
            <tr>
              <th>Date</th>
              <th>Report Type</th>
              <th>Generated By</th>
              <th>Status</th>
              <th>Download</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $reports = getRecentReports($conn);
              if (empty($reports)) {
                echo '<tr><td colspan="5" class="text-center text-muted py-4">No recent reports found.</td></tr>';
              } else {
                foreach ($reports as $r) {
                  $badgeClass = $r['status'] === 'Completed' ? 'bg-success' : ($r['status'] === 'Processing' ? 'bg-warning' : 'bg-secondary');
                  echo "
                  <tr>
                    <td>{$r['generated_date']}</td>
                    <td>{$r['report_type']}</td>
                    <td>{$r['generated_by']}</td>
                    <td><span class='badge {$badgeClass}'>{$r['status']}</span></td>
                    <td>
                      <div class='btn-group'>
                        <a href='working_export.php?type={$r['report_type']}&format=excel' class='btn btn-sm btn-outline-success'>
                          <i class='bi bi-file-earmark-excel'></i> Excel
                        </a>
                        <a href='working_export.php?type={$r['report_type']}&format=pdf' class='btn btn-sm btn-outline-danger'>
                          <i class='bi bi-file-earmark-pdf'></i> PDF
                        </a>
                      </div>
                    </td>
                  </tr>";
                }
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<script>
function downloadExport(type, format) {
    // Use the simple working export
    const url = `working_export.php?type=${type}&format=${format}`;
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Generating...';
    button.disabled = true;

    // Create download link
    const link = document.createElement('a');
    link.href = url;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Re-enable button
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        
        // Show success feedback
        const exportBox = button.closest('.export-box');
        exportBox.classList.add('export-success');
        setTimeout(() => exportBox.classList.remove('export-success'), 2000);
    }, 2000);
}

function testExport(format) {
    // Simple test function
    window.location.href = 'working_export.php?type=test&format=' + format;
}

// Add spinner animation
const style = document.createElement('style');
style.textContent = `
  .spinner {
    animation: spin 1s linear infinite;
  }
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
`;
document.head.appendChild(style);
</script>

</body>
</html>