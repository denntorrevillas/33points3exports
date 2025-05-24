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
    $newLeadTime = intval($_POST['leadTime']);

    // Get the current deadline
    $deadlineQuery = "SELECT deadline FROM accounting WHERE poNumber = ?";
    $stmt1 = $conn->prepare($deadlineQuery);
    $stmt1->bind_param("s", $poNumber);
    $stmt1->execute();
    $stmt1->bind_result($currentDeadline);
    $stmt1->fetch();
    $stmt1->close();

    // Calculate the new deadline and daysLeft
    $deadlineDate = new DateTime($currentDeadline);
    $deadlineDate->modify("+$newLeadTime days");
    $newDeadline = $deadlineDate->format('Y-m-d');

    $currentDate = new DateTime();
    $daysLeft = $currentDate->diff($deadlineDate)->days;
    if ($currentDate > $deadlineDate) {
        $daysLeft = 0; // If the deadline is past, set daysLeft to 0
    }

    // Update leadTime, deadline, and daysLeft
    $updateQuery = "UPDATE accounting SET leadTime = ?, deadline = ?, daysLeft = ? WHERE poNumber = ?";
    $stmt2 = $conn->prepare($updateQuery);
    $stmt2->bind_param("isis", $newLeadTime, $newDeadline, $daysLeft, $poNumber);

    if ($stmt2->execute()) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Lead Time, Deadline, and Days Left updated successfully!',
                showConfirmButton: true
            }).then(() => {
                window.location.href = '';
            });
        </script>";
        exit;
    } else {
        echo "<script>alert('Error updating record: " . $stmt2->error . "');</script>";
    }

    $stmt2->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Accounting Department</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
</head>
<body>
    <h2><b>Accounting Department</b></h2>
    <hr />

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
                            <td><?= htmlspecialchars($data['dateReceived']); ?></td>
                            <td><?= htmlspecialchars($data['deadline']); ?></td>
                            <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                            <td><?= htmlspecialchars($data['leadTime']); ?></td>
                            <td>
                                <button data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>" style="border: none;background-color:transparent;s">
                                    <img src="../assets/edit2.png" alt="Edit" />
                                </button>
                            </td>
                        </tr>

                        <!-- Modal for editing leadTime only -->
                        <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['poNumber']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?= $data['poNumber']; ?>">Edit Lead Time for PO <?= htmlspecialchars($data['poNumber']); ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>" />

                                            <div class="form-group">
                                                <label for="leadTime<?= $data['poNumber']; ?>">Add Lead Time (Days)</label>
                                                <input type="number" min="0" class="form-control" name="leadTime" id="leadTime<?= $data['poNumber']; ?>" required placeholder="Enter days to add" />
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
                        <td colspan="8">No data found in the Accounting Department.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>
