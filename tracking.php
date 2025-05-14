<?php
// Enable error reporting for debugging - remove in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php'; // Include database connection

    if (!$conn) {
        die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . mysqli_connect_error()]));
    }

    $poNumber = $_POST['poNumber'] ?? '';

    if (empty($poNumber)) {
        die(json_encode(['status' => 'error', 'message' => 'PO Number is required']));
    }

    // Queries for each department
    $queries = [
        'marketing' => "SELECT poNumber, receivedOrder, businessAward, endorsedToGM, orderReceived, deadline, daysLeft, leadTime FROM marketing WHERE poNumber = ?",
        'accounting' => "SELECT poNumber, receivedCopy, paymentReceived, dateReceived, deadline, daysLeft, leadTime FROM accounting WHERE poNumber = ?",
        'monitoring' => "SELECT poNumber, supplierEvaluated, supplierPOCreated, gmApproved, supplierPOIssued, dateReceived, deadline, daysLeft, leadTime FROM monitoring WHERE poNumber = ?",
        'production' => "SELECT poNumber, productionStart, productionEnd, quantityProduced FROM production WHERE poNumber = ?",
        'shipping' => "SELECT poNumber, shippedDate, deliveredDate, trackingNumber FROM shipping WHERE poNumber = ?",
    ];

    $results = []; // To store results from all tables

    // Fetch data from each table with error checking
    foreach ($queries as $department => $query) {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die(json_encode(['status' => 'error', 'message' => "Prepare failed for $department: " . $conn->error]));
        }

        $stmt->bind_param("s", $poNumber);

        if (!$stmt->execute()) {
            die(json_encode(['status' => 'error', 'message' => "Execute failed for $department: " . $stmt->error]));
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $results[$department] = $result->fetch_assoc();
        } else {
            $results[$department] = null;
        }

        $stmt->close();
    }

    $conn->close();

    if (!empty(array_filter($results))) {
        echo json_encode(['status' => 'success', 'data' => $results]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Order not found in any department!']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tracking System</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" /> -->
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

            fetch('tracking.php', {
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

                        // Production
                        if (d.production) {
                            html += `<div class="card mb-3">
                                <div class="card-header">Production Department</div>
                                <div class="card-body">
                                    <p><strong>PO Number:</strong> ${d.production.poNumber}</p>
                                    <p><strong>Production Start:</strong> ${d.production.productionStart || 'N/A'}</p>
                                    <p><strong>Production End:</strong> ${d.production.productionEnd || 'N/A'}</p>
                                    <p><strong>Quantity Produced:</strong> ${d.production.quantityProduced || 'N/A'}</p>
                                </div>
                            </div>`;
                        }

                        // Shipping
                        if (d.shipping) {
                            html += `<div class="card mb-3">
                                <div class="card-header">Shipping Department</div>
                                <div class="card-body">
                                    <p><strong>PO Number:</strong> ${d.shipping.poNumber}</p>
                                    <p><strong>Shipped Date:</strong> ${d.shipping.shippedDate || 'N/A'}</p>
                                    <p><strong>Delivered Date:</strong> ${d.shipping.deliveredDate || 'N/A'}</p>
                                    <p><strong>Tracking Number:</strong> ${d.shipping.trackingNumber || 'N/A'}</p>
                                </div>
                            </div>`;
                        }

                        resultDiv.innerHTML = html;
                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch((error) => {
                    console.error('Fetch error:', error);
                    resultDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                });
        }
    </script>
</body>
</html>
