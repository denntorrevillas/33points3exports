<?php
// Include the database connection
include '../db.php'; // Adjust the path to your database connection file

// Fetch data from the MonitoringTable
$query = "SELECT * FROM monitoring";
$result = $conn->query($query);

// Check if there are any results
if ($result->num_rows > 0) {
    $monitoringData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $monitoringData = [];
}

// Handle the update functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $supplierEvaluated = $_POST['supplierEvaluated'];
    $supplierPOCreated = $_POST['supplierPOCreated'];
    $gmApproved = $_POST['gmApproved'];
    $supplierPOIssued = $_POST['supplierPOIssued'];
    $daysLeft = $_POST['daysLeft'];

    // Calculate the deadline based on the daysLeft value
    $dateReceivedQuery = "SELECT dateReceived FROM monitoring WHERE poNumber = ?";
    $stmt = $conn->prepare($dateReceivedQuery);
    $stmt->bind_param("s", $poNumber);
    $stmt->execute();
    $stmt->bind_result($dateReceived);
    $stmt->fetch();
    $stmt->close();

    // Calculate deadline
    $deadline = date('Y-m-d', strtotime("$dateReceived +$daysLeft days"));

    // Update query
    $updateQuery = "
        UPDATE monitoring 
        SET supplierEvaluated = ?, 
            supplierPOCreated = ?, 
            gmApproved = ?, 
            supplierPOIssued = ?, 
            daysLeft = ?, 
            deadline = ? 
        WHERE poNumber = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param(
        "ssssiss", 
        $supplierEvaluated, 
        $supplierPOCreated, 
        $gmApproved, 
        $supplierPOIssued, 
        $daysLeft, 
        $deadline, 
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
    <title>Monitoring Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2><b>Monitoring Table</b></h2>

        <div class="table-div" style="overflow-x:auto;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Supplier Evaluated</th>
                        <th>Supplier PO Created</th>
                        <th>GM Approved</th>
                        <th>Supplier PO Issued</th>
                        <th>Date Received</th>
                        <th>Deadline</th>
                        <th>Days Left</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($monitoringData)) : ?>
                        <?php foreach ($monitoringData as $data) : ?>
                            <tr>
                                <td><?= htmlspecialchars($data['poNumber']); ?></td>
                                <td><?= htmlspecialchars($data['supplierEvaluated']); ?></td>
                                <td><?= htmlspecialchars($data['supplierPOCreated']); ?></td>
                                <td><?= htmlspecialchars($data['gmApproved']); ?></td>
                                <td><?= htmlspecialchars($data['supplierPOIssued']); ?></td>
                                <td><?= htmlspecialchars($data['dateReceived']); ?></td>
                                <td><?= htmlspecialchars($data['deadline']); ?></td>
                                <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                                <td>
                                    <!-- Edit Button to Open Modal -->
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>">Edit</button>
                                </td>
                            </tr>

                            <!-- Modal for editing each row -->
                          <!-- Modal for editing each row -->
<div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['poNumber']; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?= $data['poNumber']; ?>">Edit Monitoring Record</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>">

                    <!-- Fields for updating -->
                    <div class="form-group">
                        <label>Supplier Evaluated</label>
                        <select name="supplierEvaluated" class="form-control">
                            <option <?= $data['supplierEvaluated'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                            <option <?= $data['supplierEvaluated'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option <?= $data['supplierEvaluated'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Supplier PO Created</label>
                        <select name="supplierPOCreated" class="form-control">
                            <option <?= $data['supplierPOCreated'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                            <option <?= $data['supplierPOCreated'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option <?= $data['supplierPOCreated'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>GM Approved</label>
                        <select name="gmApproved" class="form-control">
                            <option <?= $data['gmApproved'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                            <option <?= $data['gmApproved'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option <?= $data['gmApproved'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Supplier PO Issued</label>
                        <select name="supplierPOIssued" class="form-control">
                            <option <?= $data['supplierPOIssued'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                            <option <?= $data['supplierPOIssued'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option <?= $data['supplierPOIssued'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Days Left</label>
                        <input type="number" name="daysLeft" class="form-control" value="<?= $data['daysLeft']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Deadline</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['deadline']); ?>" readonly>
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
                            <td colspan="9">No data found in the Monitoring Table.</td>
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
