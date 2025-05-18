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
    $leadTime = (int)$_POST['leadTime'];  // Lead Time input

    // Fetch the current daysLeft from the database
    $daysLeftQuery = "SELECT daysLeft FROM shipping WHERE poNumber = ?";
    $stmt = $conn->prepare($daysLeftQuery);
    $stmt->bind_param("s", $poNumber);
    $stmt->execute();
    $stmt->bind_result($currentDaysLeft);
    $stmt->fetch();
    $stmt->close();

    // Add leadTime to the current daysLeft
    $newDaysLeft = $currentDaysLeft + $leadTime;

    // Calculate the new deadline from current date + newDaysLeft
    $deadline = date('Y-m-d', strtotime("+$newDaysLeft days"));

    // Update query — update only daysLeft and deadline
    $updateQuery = "
        UPDATE shipping 
        SET daysLeft = ?, 
            deadline = ? 
        WHERE poNumber = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param(
        "dss",
        $newDaysLeft,
        $deadline,
        $poNumber
    );

    if ($stmt->execute()) {
        echo "<script>alert('Lead Time added to Days Left successfully!');</script>";
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Shipping Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
</head>
<body>
    <div class="container">
        <h2><b>Shipping Table</b></h2>
        <hr />

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
                                        <img src="../assets/edit2.png" alt="Edit" style="height: 20px; width: 20px;" />
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal for editing Lead Time only -->
                            <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['poNumber']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $data['poNumber']; ?>">Edit Lead Time</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>" />

                                                <!-- Lead Time input field only -->
                                                <div class="form-group">
                                                    <label>Lead Time (Days)</label>
                                                    <input type="number" name="leadTime" value="<?= $data['leadTime']; ?>" class="form-control" min="0" required />
                                                </div>

                                                <!-- Display calculated Deadline and Days Left as readonly -->
                                                <div class="form-group">
                                                    <label>Deadline</label>
                                                    <input type="text" value="<?= $data['deadline']; ?>" class="form-control" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label>Days Left</label>
                                                    <input type="text" value="<?= round($data['daysLeft'], 2); ?>" class="form-control" readonly />
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
