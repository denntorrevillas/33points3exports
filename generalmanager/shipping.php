<?php
// Include the database connection
include '../db.php'; // Adjust the path to your database connection file

// Fetch data from the Shipping Table
$query = "SELECT * FROM shipping";
$result = $conn->query($query);

// Check if there are any results
if ($result->num_rows > 0) {
    $shippingData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $shippingData = [];
}

// Handle the update functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $pre_loading = $_POST['pre_loading'];
    $loading = $_POST['loading'];
    $transported = $_POST['transported'];
    $delivered_to_customer = $_POST['delivered_to_customer'];
    $leadTime = $_POST['leadTime'];  // Get Lead Time input

    // Fetch the dateReceived for the specified PO Number
    $dateReceivedQuery = "SELECT dateReceived FROM shipping WHERE poNumber = ?";
    $stmt = $conn->prepare($dateReceivedQuery);
    $stmt->bind_param("s", $poNumber);
    $stmt->execute();
    $stmt->bind_result($dateReceived);
    $stmt->fetch();
    $stmt->close();

    // Calculate the deadline based on the leadTime value
    $deadline = date('Y-m-d', strtotime("$dateReceived +$leadTime days"));

    // Calculate the daysLeft (difference between the deadline and current date)
    $daysLeft = (strtotime($deadline) - time()) / (60 * 60 * 24); // in days

    // Update query
    $updateQuery = "
        UPDATE shipping 
        SET pre_loading = ?, 
            loading = ?, 
            transported = ?, 
            delivered_to_customer = ?, 
            leadTime = ?, 
            deadline = ?, 
            daysLeft = ? 
        WHERE poNumber = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param(
        "ssssssds", 
        $pre_loading, 
        $loading, 
        $transported, 
        $delivered_to_customer, 
        $leadTime, 
        $deadline, 
        $daysLeft, 
        $poNumber
    );

    if ($stmt->execute()) {
        echo "<script>alert('Record updated successfully!');</script>";
        // Refresh the page to see changes
        echo "<script>window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('Error updating record: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2><b>Shipping Table</b></h2>
        <hr>

        <div class="table-div" style="overflow-x:auto;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Pre-loading</th>
                        <th>Loading</th>
                        <th>Transported</th>
                        <th>Delivered to Customer</th>
                        <th>Date Received</th>
                        <th>Deadline</th>
                        <th>Days Left</th>
                        <th>Lead Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($shippingData)) : ?>
                        <?php foreach ($shippingData as $data) : ?>
                            <tr>
                                <td>PO<?= htmlspecialchars($data['poNumber']); ?></td>
                                <td><?= htmlspecialchars($data['pre_loading']); ?></td>
                                <td><?= htmlspecialchars($data['loading']); ?></td>
                                <td><?= htmlspecialchars($data['transported']); ?></td>
                                <td><?= htmlspecialchars($data['delivered_to_customer']); ?></td>
                                <td><?= htmlspecialchars($data['dateReceived']); ?></td>
                                <td><?= htmlspecialchars($data['deadline']); ?></td>
                                <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                                <td><?= htmlspecialchars($data['leadTime']); ?></td>
                                <td style="text-align: center;">
                                    <button data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>">
                                        <img src="../assets/edit2.png" alt="Edit" style="height: 20px; width: 20px;">
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal for editing each row -->
                            <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['poNumber']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $data['poNumber']; ?>">Edit Shipping Record</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>">

                                                <!-- Fields for updating -->
                                                <div class="form-group">
                                                    <label>Pre-loading</label>
                                                    <select name="pre_loading" class="form-control">
                                                        <option <?= $data['pre_loading'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option <?= $data['pre_loading'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option <?= $data['pre_loading'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Loading</label>
                                                    <select name="loading" class="form-control">
                                                        <option <?= $data['loading'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option <?= $data['loading'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option <?= $data['loading'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Transported</label>
                                                    <select name="transported" class="form-control">
                                                        <option <?= $data['transported'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option <?= $data['transported'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option <?= $data['transported'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Delivered to Customer</label>
                                                    <select name="delivered_to_customer" class="form-control">
                                                        <option <?= $data['delivered_to_customer'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option <?= $data['delivered_to_customer'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option <?= $data['delivered_to_customer'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                
                                                <!-- Lead Time input field -->
                                                <div class="form-group">
                                                    <label>Lead Time (Days)</label>
                                                    <input type="number" name="leadTime" value="<?= $data['leadTime']; ?>" class="form-control" required>
                                                </div>

                                                <!-- Display calculated Deadline and Days Left -->
                                                <div class="form-group">
                                                    <label>Deadline</label>
                                                    <input type="text" value="<?= $data['deadline']; ?>" class="form-control" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label>Days Left</label>
                                                    <input type="text" value="<?= $data['daysLeft']; ?>" class="form-control" readonly>
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
                            <td colspan="10">No data found in the Shipping Table.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
