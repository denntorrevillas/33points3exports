<?php
include '../db.php';

$orders = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        empty($_POST['poNumber']) || 
        empty($_POST['leadTime']) || 
        empty($_POST['buyer'])
    ) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'All fields are required.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
        exit;
    }

    $poNumber = $_POST['poNumber'];
    $buyer = $_POST['buyer'];
    $leadTime = (int)$_POST['leadTime'];

    $orderDate = date('Y-m-d H:i:s');
    $shipDate = date('Y-m-d', strtotime("+$leadTime days"));
    $overallStatus = "Not Started";

    // Check for duplicate PO Number
    $checkQuery = "SELECT COUNT(*) as count FROM Orders WHERE poNumber = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $poNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'PO Number already exists. Please use a unique PO Number.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
        $sql = "INSERT INTO Orders (poNumber, buyer, orderDate, shipDate, leadTime, overallStatus) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssssis", $poNumber, $buyer, $orderDate, $shipDate, $leadTime, $overallStatus);

            if ($stmt->execute()) {
                echo "<script>
                    Swal.fire({
                        title: 'Success!',
                        text: 'Order added successfully.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = window.location.href;
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to add the order. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                </script>";
            }

            $stmt->close();
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Query error: " . $conn->error . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    }
}

// Fetch the orders data
$query = "SELECT * FROM Orders";
$result = $conn->query($query);
if ($result) {
    $orders = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close(); // Close connection only after all database operations
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2><b>Add Order</b></h2>
        <hr>

        <div class="d-flex justify-content-between align-items-center my-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                Add Order
            </button>
            <div class="search w-20">
                <input id="searchInput" class="form-control" type="text" placeholder="Search Order" />
                <button type="button" id="searchButton" class="btn btn-success">
                    SEARCH
                </button>
            </div>
        </div>

        <div class="table-div">
            <table id="table-div-content">
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Buyer</th>
                        <th>Order Date</th>
                        <th>Ship Date</th>
                        <th>Days Left</th>
                        <th>Lead Time</th>
                        <th>Overall Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)) : ?>
                        <?php foreach ($orders as $order) : ?>
                            <tr>
                                <td><?= htmlspecialchars($order['poNumber']); ?></td>
                                <td><?= htmlspecialchars($order['buyer']); ?></td>
                                <td><?= htmlspecialchars($order['orderDate']); ?></td>
                                <td><?= htmlspecialchars($order['shipDate']); ?></td>
                                <td><?= htmlspecialchars($order['daysLeft']); ?> Days</td>
                                <td><?= htmlspecialchars($order['leadTime']); ?> Days</td>
                                <td><?= htmlspecialchars($order['overallStatus']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Order Modal -->
    <div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addOrderModalLabel">Add New Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add Order Form -->
                    <form id="addOrderForm" action="" method="POST">
                        <div class="mb-3">
                            <label for="poNumber" class="form-label">PO No.</label>
                            <input type="text" name="poNumber" class="form-control" id="poNumber" placeholder="Enter PO Number" required />
                        </div>
                        <div class="mb-3">
                            <label for="buyer" class="form-label">Buyer</label>
                            <input type="text" name="buyer" class="form-control" id="buyer" placeholder="Enter Buyer Name" required />
                        </div>
                        <div class="mb-3">
                            <label for="leadTime" class="form-label">Lead Time</label>
                            <input type="number" name="leadTime" class="form-control" id="leadTime" placeholder="Enter Lead Time" required />
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="addOrderForm" class="btn btn-success">Save Order</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const rows = document.querySelectorAll('table tbody tr');

            function filterTable() {
                const searchValue = searchInput.value.toLowerCase();
                rows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    row.style.display = rowText.includes(searchValue) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterTable);
            searchButton.addEventListener('click', filterTable);
        });
    </script>
</body>
</html>
