<?php
// Include the database connection
include '../db.php'; // Assuming the database connection is in db.php

// Query to fetch data from the accounting table
$query = "SELECT * FROM accounting";
$result = $conn->query($query);

// Check if there are any results
if ($result->num_rows > 0) {
    // Fetch the data as an associative array
    $accountingData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $accountingData = [];
}

// Handle the update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $receivedCopy = $_POST['receivedCopy'];
    $paymentReceived = $_POST['paymentReceived'];
    $daysLeft = $_POST['daysLeft'];

    // Calculate the deadline by adding daysLeft to today's date
    $deadline = date('Y-m-d', strtotime("+$daysLeft days"));

    // Update query
    $updateQuery = "UPDATE accounting SET receivedCopy = ?, paymentReceived = ?, deadline = ?, daysLeft = ? WHERE poNumber = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssss", $receivedCopy, $paymentReceived, $deadline, $daysLeft, $poNumber);

    if ($stmt->execute()) {
        echo "<script>alert('Record updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating record!');</script>";
    }

    $stmt->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Department</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <h2><b>Accounting Department</b></h2>

    <div class="table-div" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>PO No.</th>
                    <th>Received Copy</th>
                    <th>Payment Received</th>
                    <th>Deadline</th>
                    <th>Days Left</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($accountingData)) : ?>
                    <?php foreach ($accountingData as $data) : ?>
                        <tr>
                            <td><?= htmlspecialchars($data['poNumber']); ?></td>
                            <td><?= htmlspecialchars($data['receivedCopy']); ?></td>
                            <td><?= htmlspecialchars($data['paymentReceived']); ?></td>
                            <td><?= htmlspecialchars($data['deadline']); ?></td>
                            <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                            <td>
                                <buttom data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>">
                                    <img src="../assets/edit2.png" alt="Edit" style="height: 20px;  width: 20px;">
                                </button>
                            </td>
                        </tr>

                        <!-- Modal for editing each row -->
                        <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['poNumber']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?= $data['poNumber']; ?>">Edit Accounting Record</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>">

                                            <!-- Received Copy Dropdown -->
                                            <div class="form-group">
                                                <label for="receivedCopy">Received Copy</label>
                                                <select class="form-control" name="receivedCopy" id="receivedCopy<?= $data['poNumber']; ?>" required>
                                                    <option value="Not Started" <?= $data['receivedCopy'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['receivedCopy'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['receivedCopy'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Payment Received Dropdown (Initially Disabled) -->
                                            <div class="form-group">
                                                <label for="paymentReceived">Payment Received</label>
                                                <select class="form-control" name="paymentReceived" id="paymentReceived<?= $data['poNumber']; ?>" required disabled>
                                                    <option value="Not Started" <?= $data['paymentReceived'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['paymentReceived'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['paymentReceived'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>

                                            <!-- Deadline (Read-Only) -->
                                            <div class="form-group">
                                                <label for="deadline">Deadline</label>
                                                <input type="text" class="form-control" name="deadline" id="deadline<?= $data['poNumber']; ?>" value="<?= $data['deadline']; ?>" readonly>
                                            </div>

                                            <!-- Days Left Input -->
                                            <div class="form-group">
                                                <label for="daysLeft">Days Left</label>
                                                <input type="number" class="form-control" name="daysLeft" value="<?= $data['daysLeft']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7">No data found in the Accounting Department.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Iterate through each modal and attach event listeners
        document.querySelectorAll('.modal').forEach(modal => {
            const receivedCopyDropdown = modal.querySelector('[name="receivedCopy"]');
            const paymentReceivedDropdown = modal.querySelector('[name="paymentReceived"]');
            const daysLeftInput = modal.querySelector('[name="daysLeft"]');
            const deadlineInput = modal.querySelector('[name="deadline"]');

            // Function to calculate deadline based on daysLeft
            const calculateDeadline = () => {
                const daysLeft = parseInt(daysLeftInput.value, 10);
                if (daysLeft) {
                    const deadline = new Date();
                    deadline.setDate(deadline.getDate() + daysLeft);
                    deadlineInput.value = deadline.toISOString().split('T')[0]; // Format as YYYY-MM-DD
                }
            };

            // Listen for changes in daysLeft to recalculate the deadline
            daysLeftInput.addEventListener('input', calculateDeadline);

            // Listen for changes in receivedCopy to enable/disable paymentReceived dropdown
            receivedCopyDropdown.addEventListener('change', () => {
                if (receivedCopyDropdown.value === 'Completed') {
                    paymentReceivedDropdown.disabled = false; // Enable paymentReceived when Received Copy is completed
                } else {
                    paymentReceivedDropdown.disabled = true; // Disable paymentReceived when Received Copy is not completed
                    paymentReceivedDropdown.value = ''; // Reset the value
                }
            });

            // Initialize the dropdowns and fields
            if (receivedCopyDropdown.value === 'Completed') {
                paymentReceivedDropdown.disabled = false; // Enable if it's already Completed
            } else {
                paymentReceivedDropdown.disabled = true;
            }

            // Initialize deadline when page loads (if fields are already filled)
            calculateDeadline();
        });
    });
</script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
