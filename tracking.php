<?php
// tracking.php

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database credentials — change these to your own!
include 'db.php';
try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $poNumber = filter_input(INPUT_POST, 'poNumber', FILTER_SANITIZE_STRING);
        if (!$poNumber) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid or missing PO Number']);
            exit;
        }

        // Example with 3 departments - add more if needed
        $queries = [
            'marketing' => "SELECT poNumber, receivedOrder, businessAward, endorsedToGM, orderReceived, deadline, daysLeft, leadTime FROM marketing WHERE poNumber = ?",
            'accounting' => "SELECT poNumber, receivedCopy, paymentReceived, dateReceived, deadline, daysLeft, leadTime FROM accounting WHERE poNumber = ?",
            'monitoring' => "SELECT poNumber, supplierEvaluated, supplierPOCreated, gmApproved, supplierPOIssued, dateReceived, deadline, daysLeft, leadTime FROM monitoring WHERE poNumber = ?",
            // Add more if needed
        ];

        $results = [];

        foreach ($queries as $dept => $sql) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $poNumber);
            $stmt->execute();
            $res = $stmt->get_result();
            $data = $res->fetch_assoc();
            $results[$dept] = $data ?: null;
            $stmt->close();
        }

        $conn->close();

        if (count(array_filter($results)) === 0) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Order not found in any department']);
            exit;
        }

        echo json_encode(['status' => 'success', 'data' => $results]);
        exit;
    } else {
        // Show HTML frontend if GET or others
        ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tracking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2><b>Track Order</b></h2>
        <hr />
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group mb-3">
                    <input
                        type="text"
                        id="poNumber"
                        class="form-control"
                        placeholder="Enter PO Number"
                        aria-label="PO Number"
                    />
                    <button class="btn btn-success" onclick="trackOrder()">TRACK ORDER</button>
                </div>
                <div id="result"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function trackOrder() {
            const poNumber = document.getElementById('poNumber').value.trim();
            const resultDiv = document.getElementById('result');
            if (!poNumber) {
                resultDiv.innerHTML = '<div class="alert alert-warning">Please enter a PO Number!</div>';
                return;
            }

            fetch('', { // POST to same PHP file
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `poNumber=${encodeURIComponent(poNumber)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const d = data.data;
                        let html = '';

                        // Marketing
                        if (d.marketing) {
                            html += `<div class="card mb-3">
                                <div class="card-header">Marketing Department</div>
                                <div class="card-body">
                                    <p><strong>PO Number:</strong> ${d.marketing.poNumber}</p>
                                    <p><strong>Received Order:</strong> ${d.marketing.receivedOrder || 'N/A'}</p>
                                    <p><strong>Business Award:</strong> ${d.marketing.businessAward || 'N/A'}</p>
                                    <p><strong>Endorsed To GM:</strong> ${d.marketing.endorsedToGM || 'N/A'}</p>
                                    <p><strong>Order Received:</strong> ${d.marketing.orderReceived || 'N/A'}</p>
                                    <p><strong>Deadline:</strong> ${d.marketing.deadline || 'N/A'}</p>
                                    <p><strong>Days Left:</strong> ${d.marketing.daysLeft || 'N/A'}</p>
                                    <p><strong>Lead Time:</strong> ${d.marketing.leadTime || 'N/A'}</p>
                                </div>
                            </div>`;
                        }

                        // Accounting
                        if (d.accounting) {
                            html += `<div class="card mb-3">
                                <div class="card-header">Accounting Department</div>
                                <div class="card-body">
                                    <p><strong>PO Number:</strong> ${d.accounting.poNumber}</p>
                                    <p><strong>Received Copy:</strong> ${d.accounting.receivedCopy || 'N/A'}</p>
                                    <p><strong>Payment Received:</strong> ${d.accounting.paymentReceived || 'N/A'}</p>
                                    <p><strong>Date Received:</strong> ${d.accounting.dateReceived || 'N/A'}</p>
                                    <p><strong>Deadline:</strong> ${d.accounting.deadline || 'N/A'}</p>
                                    <p><strong>Days Left:</strong> ${d.accounting.daysLeft || 'N/A'}</p>
                                    <p><strong>Lead Time:</strong> ${d.accounting.leadTime || 'N/A'}</p>
                                </div>
                            </div>`;
                        }

                        // Monitoring
                        if (d.monitoring) {
                            html += `<div class="card mb-3">
                                <div class="card-header">Monitoring Department</div>
                                <div class="card-body">
                                    <p><strong>PO Number:</strong> ${d.monitoring.poNumber}</p>
                                    <p><strong>Supplier Evaluated:</strong> ${d.monitoring.supplierEvaluated || 'N/A'}</p>
                                    <p><strong>Supplier PO Created:</strong> ${d.monitoring.supplierPOCreated || 'N/A'}</p>
                                    <p><strong>GM Approved:</strong> ${d.monitoring.gmApproved || 'N/A'}</p>
                                    <p><strong>Supplier PO Issued:</strong> ${d.monitoring.supplierPOIssued || 'N/A'}</p>
                                    <p><strong>Date Received:</strong> ${d.monitoring.dateReceived || 'N/A'}</p>
                                    <p><strong>Deadline:</strong> ${d.monitoring.deadline || 'N/A'}</p>
                                    <p><strong>Days Left:</strong> ${d.monitoring.daysLeft || 'N/A'}</p>
                                    <p><strong>Lead Time:</strong> ${d.monitoring.leadTime || 'N/A'}</p>
                                </div>
                            </div>`;
                        }

                        resultDiv.innerHTML = html;
                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    resultDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                });
        }
    </script>
</body>
</html>

<?php
    }
} catch (mysqli_sql_exception $e) {
    error_log("DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
    exit;
}
?>
