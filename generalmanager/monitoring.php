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
    $leadTime = $_POST['leadTime'];  // Lead time input
    $dateReceived = $_POST['dateReceived'];  // Date received input

    // Calculate deadline (dateReceived + leadTime)
    $deadline = date('Y-m-d', strtotime($dateReceived . " + $leadTime days"));

    // Calculate days left (current date - deadline)
    $currentDate = date('Y-m-d');
    $daysLeft = (strtotime($deadline) - strtotime($currentDate)) / 86400; // 86400 seconds in a day

    // Update query
    $updateQuery = "
        UPDATE monitoring 
        SET supplierEvaluated = ?, 
            supplierPOCreated = ?, 
            gmApproved = ?, 
            supplierPOIssued = ?, 
            leadTime = ?, 
            deadline = ?, 
            daysLeft = ?
        WHERE poNumber = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param(
        "ssssssds", 
        $supplierEvaluated, 
        $supplierPOCreated, 
        $gmApproved, 
        $supplierPOIssued, 
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
    <title>Monitoring Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2><b>Monitoring</b></h2>

        <div class="table-div" >
            <table class="table table-bordered table-striped" >
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
                        <th>Lead Time</th>
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
                                 <td><?= htmlspecialchars($data['leadTime']); ?></td>
                                 <td style="text-align:center";>
                                    <button data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>">
                                        <img src="../assets/edit2.png" alt="Edit">
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal for editing each row -->
                            <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Monitoring Record</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>">

                                                <!-- Dropdown fields -->
                                                <div class="form-group">
                                                    <label>Supplier Evaluated</label>
                                                    <select name="supplierEvaluated" class="form-control">
                                                        <option value="Not Started" <?= $data['supplierEvaluated'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option value="In Progress" <?= $data['supplierEvaluated'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="Completed" <?= $data['supplierEvaluated'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Supplier PO Created</label>
                                                    <select name="supplierPOCreated" class="form-control">
                                                        <option value="Not Started" <?= $data['supplierPOCreated'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option value="In Progress" <?= $data['supplierPOCreated'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="Completed" <?= $data['supplierPOCreated'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>GM Approved</label>
                                                    <select name="gmApproved" class="form-control">
                                                        <option value="Not Started" <?= $data['gmApproved'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option value="In Progress" <?= $data['gmApproved'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="Completed" <?= $data['gmApproved'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Supplier PO Issued</label>
                                                    <select name="supplierPOIssued" class="form-control">
                                                        <option value="Not Started" <?= $data['supplierPOIssued'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option value="In Progress" <?= $data['supplierPOIssued'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="Completed" <?= $data['supplierPOIssued'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Lead Time (in days)</label>
                                                    <input type="number" name="leadTime" class="form-control" value="<?= $data['leadTime']; ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Date Received</label>
                                                    <input type="date" name="dateReceived" class="form-control" value="<?= $data['dateReceived']; ?>" required>
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
