<?php
// Include the database connection
include '../db.php'; // Assuming the database connection is in db.php

// Periodically update `daysLeft` in the database
$updateDaysLeftQuery = "UPDATE marketing SET daysLeft = DATEDIFF(deadline, CURDATE())";
if (!$conn->query($updateDaysLeftQuery)) {
    die("Error updating daysLeft: " . $conn->error);
}

// Query to fetch data from the marketing table
$query = "SELECT * FROM marketing";
$result = $conn->query($query);

// Check if there are any results
$marketingData = ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Initialize update status
$updateStatus = false;

// Handle updates to leadTime and recalculate deadline and daysLeft
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $leadTime = isset($_POST['leadTime']) ? (int)$_POST['leadTime'] : 0;

    // Fetch the orderReceived date
    $fetchQuery = "SELECT orderReceived FROM marketing WHERE poNumber = ?";
    $stmt = $conn->prepare($fetchQuery);
    $stmt->bind_param("s", $poNumber);
    $stmt->execute();
    $stmt->bind_result($orderReceived);
    $stmt->fetch();
    $stmt->close();

    if ($orderReceived) {
        // Calculate the new deadline
        $deadline = date('Y-m-d', strtotime("$orderReceived +$leadTime days"));

        // Calculate daysLeft based on the current date
        $currentDate = date('Y-m-d');
        $daysLeft = (strtotime($deadline) - strtotime($currentDate)) / (60 * 60 * 24);

        // Update the database with leadTime, deadline, and daysLeft
        $updateQuery = "UPDATE marketing SET leadTime = ?, deadline = ?, daysLeft = ? WHERE poNumber = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("isis", $leadTime, $deadline, $daysLeft, $poNumber);

        if ($stmt->execute()) {
            $updateStatus = true; // Set update status to true for SweetAlert
        } else {
            echo "<script>alert('Error updating record!');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Order not found!');</script>";
    }
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
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if ($updateStatus): ?>
        <script>
            Swal.fire({
                title: 'Success!',
                text: 'Record updated successfully!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = window.location.href;
            });
        </script>
    <?php endif; ?>

    <div class="container">
        <h2 ><b>Marketing Department</b></h2>
        <hr>

        <div class="table-responsive">
            <table class="table table-striped">
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
                               <td style="text-align:center; border-color:transparent;">
                                    <button data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>" style="border: none; background: none; padding: 0; outline: none;">
                                    <img src="../assets/edit2.png" alt="Edit" />
                                </button>
                                </td>
                            </tr>

                            <!-- Modal for editing each row -->
                            <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['poNumber']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $data['poNumber']; ?>">Edit Lead Time</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST" action="">
                                            <div class="modal-body">
                                                <input type="hidden" name="poNumber" value="<?= htmlspecialchars($data['poNumber']); ?>">

                                                <!-- Lead Time Input -->
                                                <div class="form-group">
                                                    <label for="leadTime">Lead Time</label>
                                                    <input type="number" class="form-control" name="leadTime" value="<?= htmlspecialchars($data['leadTime']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" name="update" class="btn btn-success">UPDATE</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9">No data found in the Marketing Department.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
