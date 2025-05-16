<?php
// Include the database connection
include '../db.php'; // Assuming the database connection is in db.php

// Query to fetch data from the marketing table
$query = "SELECT * FROM marketing";
$result = $conn->query($query);

// Check if there are any results
if ($result->num_rows > 0) {
    // Fetch the data as an associative array
    $marketingData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $marketingData = [];
}

// Handle the update
// Handle the update
// Handle the update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $receivedOrder = $_POST['receivedOrder'];
    $businessAward = $_POST['businessAward'];
    $endorsedToGM = $_POST['endorsedToGM'];
    $daysLeft = $_POST['daysLeft'];
    $leadTime = isset($_POST['leadTime']) ? $_POST['leadTime'] : NULL; // Allow blank (NULL) value for leadTime

    // Calculate the new deadline
    $deadline = date('Y-m-d', strtotime("+$daysLeft days"));

    // Update query (now allowing leadTime to be NULL if not provided)
    $updateQuery = "UPDATE marketing SET receivedOrder = ?, businessAward = ?, endorsedToGM = ?, daysLeft = ?, deadline = ?, leadTime = ? WHERE poNumber = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssss", $receivedOrder, $businessAward, $endorsedToGM, $daysLeft, $deadline, $leadTime, $poNumber);

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
    <title>Marketing Department</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <h2><b>Marketing Department</b></h2>
    <hr>

    <div class="table-div" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>PO No.</th>
                    <th>Received Order</th>
                    <th>Business Award</th>
                    <th>Endorsed to GM</th>
                    <th>Order Received</th>
                    <th>Deadline</th>
                    <th>Days Left</th>
                    <th>Lead Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($marketingData)) : ?>
                    <?php foreach ($marketingData as $data) : ?>
                        <tr>
                            <td><?= htmlspecialchars($data['poNumber']); ?></td>
                            <td><?= htmlspecialchars($data['receivedOrder']); ?></td>
                            <td><?= htmlspecialchars($data['businessAward']); ?></td>
                            <td><?= htmlspecialchars($data['endorsedToGM']); ?></td>
                            <td><?= htmlspecialchars($data['orderReceived']); ?></td>
                            <td><?= htmlspecialchars($data['deadline']); ?></td>
                            <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                            <td><?= htmlspecialchars($data['leadTime']); ?></td>
                            <td>
                                <buttom data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>">
                                    <img src="../assets/edit2.png" alt="Edit">
                                </button>
                            </td>
                        </tr>

                        <!-- Modal for editing each row -->
                        <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['poNumber']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?= $data['poNumber']; ?>">Edit Marketing Department</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>">

                                            <!-- Received Order Dropdown -->
                                            <div class="form-group">
                                                <label for="receivedOrder">Received Order</label>
                                                <select class="form-control" name="receivedOrder" id="receivedOrder" required>
                                                    <option value="Not Started" <?= $data['receivedOrder'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['receivedOrder'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['receivedOrder'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>

                                            <!-- Business Award Dropdown -->
                                            <div class="form-group">
                                                <label for="businessAward">Business Award</label>
                                                <select class="form-control" name="businessAward" id="businessAward" required>
                                                    <option value="Not Started" <?= $data['businessAward'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['businessAward'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['businessAward'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>

                                            <!-- Endorsed to GM Dropdown -->
                                            <div class="form-group">
                                                <label for="endorsedToGM">Endorsed to GM</label>
                                                <select class="form-control" name="endorsedToGM" id="endorsedToGM" required>
                                                    <option value="Not Started" <?= $data['endorsedToGM'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['endorsedToGM'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['endorsedToGM'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
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
                        <td colspan="8">No data found in the Marketing Department.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Iterate through each modal and attach event listeners
            document.querySelectorAll('.modal').forEach(modal => {
                const receivedOrderSelect = modal.querySelector('[name="receivedOrder"]');
                const businessAwardSelect = modal.querySelector('[name="businessAward"]');
                const endorsedToGMSelect = modal.querySelector('[name="endorsedToGM"]');

                // Function to toggle the disabled state
                const toggleDisableState = () => {
                    const isReceivedOrderCompleted = receivedOrderSelect.value === 'Completed';
                    const isBusinessAwardCompleted = businessAwardSelect.value === 'Completed';

                    // Enable or disable the next dropdown based on the current state
                    businessAwardSelect.disabled = !isReceivedOrderCompleted;
                    endorsedToGMSelect.disabled = !isBusinessAwardCompleted;
                };

                // Initial state check
                toggleDisableState();

                // Add event listeners for changes on dropdowns
                receivedOrderSelect.addEventListener('change', toggleDisableState);
                businessAwardSelect.addEventListener('change', toggleDisableState);
            });
        });
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</
