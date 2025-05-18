<?php
// Include the database connection
include '../db.php'; // Assuming the database connection is in db.php

// Query to fetch data from the accounting table
$query = "SELECT * FROM accounting";
$result = $conn->query($query);

// Check if there are any results
if ($result) {
    $accountingData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $accountingData = [];
    echo "<script>alert('Error fetching data: " . $conn->error . "');</script>";
}

// Handle the update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $receivedCopy = $_POST['receivedCopy'];
    $paymentReceived = $_POST['paymentReceived'];

    // Update query
    $updateQuery = "UPDATE accounting SET receivedCopy = ?, paymentReceived = ? WHERE poNumber = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sss", $receivedCopy, $paymentReceived, $poNumber);

    if ($stmt->execute()) {
        echo "<script>alert('Record updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating record: " . $stmt->error . "');</script>";
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
    <hr>

    <div class="table-div" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>PO No.</th>
                    <th>Received Copy</th>
                    <th>Payment Received</th>
                    <th>Date Received</th>
                    <th>Deadline</th>
                    <th>Days Left</th>
                    <th>Lead Time</th>
                  
                    
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($accountingData)) : ?>
                    <?php foreach ($accountingData as $data) : ?>
                        <tr>
                            <td><?= htmlspecialchars($data['poNumber']); ?></td>
                            <td><?= htmlspecialchars($data['receivedCopy']); ?></td>
                            <td><?= htmlspecialchars($data['paymentReceived']); ?></td>
                            <td><?= htmlspecialchars($data['dateReceived']); ?></td>
                            <td><?= htmlspecialchars($data['deadline']); ?></td>
                            <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                             <td><?= htmlspecialchars($data['leadTime']); ?></td>
                           
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

                                            <!-- Payment Received Dropdown -->
                                            <div class="form-group">
                                                <label for="paymentReceived">Payment Received</label>
                                                <select class="form-control" name="paymentReceived" id="paymentReceived<?= $data['poNumber']; ?>" required <?= $data['receivedCopy'] != 'Completed' ? 'disabled' : ''; ?>>
                                                    <option value="Not Started" <?= $data['paymentReceived'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['paymentReceived'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['paymentReceived'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
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
                        <td colspan="5">No data found in the Accounting Department.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.modal').forEach(modal => {
                const receivedCopyDropdown = modal.querySelector('[name="receivedCopy"]');
                const paymentReceivedDropdown = modal.querySelector('[name="paymentReceived"]');

                receivedCopyDropdown.addEventListener('change', () => {
                    paymentReceivedDropdown.disabled = receivedCopyDropdown.value !== 'Completed';
                    if (paymentReceivedDropdown.disabled) {
                        paymentReceivedDropdown.value = '';
                    }
                });
            });
        });
    </script>
</body>
</html>
