<?php
// Include the database connection
include '../db.php'; // Adjust the path to your database connection file

// Fetch data from the ProductionTable
$query = "SELECT * FROM production";
$result = $conn->query($query);

// Check if there are any results
if ($result->num_rows > 0) {
    $productionData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $productionData = [];
}

// Handle the update functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $finishing = $_POST['finishing'];
    $packed = $_POST['packed'];
    $inspected = $_POST['inspected'];
    $leadTime = $_POST['leadTime']; // Get Lead Time input

    // Fetch the dateReceived for the specified PO Number
    $dateReceivedQuery = "SELECT dateReceived FROM production WHERE poNumber = ?";
    $stmt = $conn->prepare($dateReceivedQuery);
    $stmt->bind_param("s", $poNumber);
    $stmt->execute();
    $stmt->bind_result($dateReceived);
    $stmt->fetch();
    $stmt->close();

    // Calculate the deadline based on the dateReceived and leadTime
    $deadline = date('Y-m-d', strtotime("$dateReceived +$leadTime days"));

    // Calculate the daysLeft (difference between the deadline and current date)
    $daysLeft = (strtotime($deadline) - time()) / (60 * 60 * 24); // in days

    // Update query
    $updateQuery = "
        UPDATE production 
        SET finishing = ?, 
            packed = ?, 
            inspected = ?, 
            daysLeft = ?, 
            deadline = ?, 
            leadTime = ? 
        WHERE poNumber = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param(
        "sssssss", 
        $finishing, 
        $packed, 
        $inspected, 
        $daysLeft, 
        $deadline, 
        $leadTime, 
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
    <title>Production Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2><b>Production Table</b></h2>
        <hr>

        <div class="table-div" style="overflow-x:auto;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Finishing</th>
                        <th>Packed</th>
                        <th>Inspected</th>
                        <th>Date Received</th>
                        <th>Deadline</th>
                        <th>Days Left</th>
                        <th>Lead Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productionData)) : ?>
                        <?php foreach ($productionData as $data) : ?>
                            <tr>
                                <td><?= htmlspecialchars($data['poNumber']); ?></td>
                                <td><?= htmlspecialchars($data['finishing']); ?></td>
                                <td><?= htmlspecialchars($data['packed']); ?></td>
                                <td><?= htmlspecialchars($data['inspected']); ?></td>
                                <td><?= htmlspecialchars($data['dateReceived']); ?></td>
                                <td><?= htmlspecialchars($data['deadline']); ?></td>
                                <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                                <td><?= htmlspecialchars($data['leadTime']); ?></td>
                                <td style="text-align:center;">
                                    <button data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>">
                                        <img src="../assets/edit2.png" alt="Edit">
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal for editing each row -->
                            <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['poNumber']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $data['poNumber']; ?>">Edit Production Record</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>">

                                                <!-- Fields for updating -->
                                                <div class="form-group">
                                                    <label>Finishing</label>
                                                    <select name="finishing" class="form-control">
                                                        <option <?= $data['finishing'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option <?= $data['finishing'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option <?= $data['finishing'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Packed</label>
                                                    <select name="packed" class="form-control">
                                                        <option <?= $data['packed'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option <?= $data['packed'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option <?= $data['packed'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Inspected</label>
                                                    <select name="inspected" class="form-control">
                                                        <option <?= $data['inspected'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option <?= $data['inspected'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option <?= $data['inspected'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
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
                            <td colspan="9">No data found in the Production Table.</td>
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
