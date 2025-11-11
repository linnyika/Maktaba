
document.addEventListener("DOMContentLoaded", function () {
    const printBtn = document.getElementById("printReportBtn");
    const reportSection = document.getElementById("reportSection");

    if (!printBtn || !reportSection) return;

    // --- Trigger print preview ---
    printBtn.addEventListener("click", function () {
        // Create a temporary print window
        const printWindow = window.open("", "PRINT", "height=800,width=1000");
        if (!printWindow) {
            alert("Popup blocked! Please allow popups for this site to print reports.");
            return;
        }

        // Clone current report HTML into print view
        printWindow.document.write(`
            <html>
            <head>
                <title>Report Preview - Maktaba</title>
                <link rel="stylesheet" href="../../assets/css/analytics.css">
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h2 { text-align: center; color: #222; }
                    table {
                        border-collapse: collapse;
                        width: 100%;
                        margin-top: 20px;
                    }
                    table, th, td {
                        border: 1px solid #aaa;
                    }
                    th, td {
                        padding: 8px 10px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                    .footer-note {
                        text-align: center;
                        margin-top: 30px;
                        font-size: 0.9em;
                        color: #555;
                    }
                </style>
            </head>
            <body>
                <h2>📊 Maktaba Report Preview</h2>
                ${reportSection.innerHTML}
                <div class="footer-note">
                    Generated on ${new Date().toLocaleString()}<br>
                    Maktaba Digital Library System
                </div>
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        // Wait briefly before printing (to ensure full load)
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 700);
    });
});
