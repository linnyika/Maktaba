// assets/js/export.js
// Member 5: frontend integration for export buttons & quick preview.
// Depends on: admin page buttons with data-action/data-format attributes
// Example usage in admin page: <button class="btn-export" data-action="sales" data-format="csv" data-from="2025-11-01" data-to="2025-11-10">Export CSV</button>

document.addEventListener('DOMContentLoaded', function () {

  async function postExport(action, format, options = {}) {
    // Build payload
    const payload = {
      action: action,
      format: format,
      options: options
    };

    // Show simple loader indicator if desired
    const loader = document.querySelector('#export-loader');
    if (loader) loader.style.display = 'inline-block';

    try {
      const res = await fetch('/modules/admin/export_reports.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      });

      // If response is a file stream, the server will send with appropriate headers.
      // For fetch to handle binary, use blob.
      if (!res.ok) {
        const txt = await res.text();
        throw new Error('Export failed: ' + res.status + ' - ' + txt);
      }

      // If server returns JSON with a url to download (safer for large files), handle it:
      const contentType = res.headers.get('content-type') || '';
      if (contentType.includes('application/json')) {
        const data = await res.json();
        if (data.download_url) {
          window.location = data.download_url;
        } else if (data.message) {
          alert(data.message);
        }
        return;
      }

      // Else treat as binary file and prompt download
      const blob = await res.blob();
      const disposition = res.headers.get('content-disposition') || '';
      let filename = 'export_' + Date.now();
      const match = disposition.match(/filename\*?=(?:UTF-8'')?["']?([^;"']+)/i);
      if (match && match[1]) filename = decodeURIComponent(match[1]);

      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      console.error(err);
      alert('Export failed: ' + (err.message || err));
    } finally {
      if (loader) loader.style.display = 'none';
    }
  }

  // Wire buttons with class .btn-export
  document.querySelectorAll('.btn-export').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const action = btn.dataset.action || 'sales';
      const format = btn.dataset.format || 'csv';
      // optional filters
      const from = btn.dataset.from || document.querySelector('#filter-from')?.value || null;
      const to = btn.dataset.to || document.querySelector('#filter-to')?.value || null;
      const options = {};
      if (from) options.from = from;
      if (to) options.to = to;
      // disable button briefly
      btn.disabled = true;
      postExport(action, format, options).finally(() => btn.disabled = false);
    });
  });

  // Quick preview (open small modal with HTML) if button has data-preview="true"
  document.querySelectorAll('.btn-preview').forEach(btn => {
    btn.addEventListener('click', async function (e) {
      const action = btn.dataset.action || 'sales';
      try {
        const res = await fetch('/modules/admin/export_reports.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
          body: JSON.stringify({action: action, format: 'preview', options: {}})
        });
        if (res.ok) {
          const html = await res.text();
          const w = window.open("", "_blank", "width=900,height=700");
          w.document.open();
          w.document.write(html);
          w.document.close();
        } else {
          const txt = await res.text();
          alert('Preview failed: ' + txt);
        }
      } catch (err) {
        console.error(err);
        alert('Preview error: ' + err.message);
      }
    });
  });

});
