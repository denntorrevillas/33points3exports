<?php
// Fetch the orders data
$orders = include 'fetchOrders.php';

// Check if a success message should be displayed
$successMessage = isset($_GET['success']) && $_GET['success'] === '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
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

        <!-- Success Message -->
        <?php if ($successMessage): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Order added successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table id="ordersTable">
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
                    <form id="addOrderForm" action="sqlAddOrder.php" method="POST">
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

    <!-- JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Function to change the text color of the "Days Left" column based on the value
            function updateDaysLeftColor() {
                const daysLeftCells = document.querySelectorAll("table tbody tr td:nth-child(5)");
                daysLeftCells.forEach(cell => {
                    const daysLeft = parseInt(cell.textContent);
                    if (daysLeft <= 40) {
                        cell.style.color = "#ff4d4d"; // Red
                    } else if (daysLeft <= 80) {
                        cell.style.color = "#ffcc00"; // Yellow
                    } else {
                        cell.style.color = "#28a745"; // Green
                    }
                });
            }

            // Call the function initially to color the cells
            updateDaysLeftColor();

            // Search functionality
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
