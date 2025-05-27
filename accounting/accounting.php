<?php
// // Start the session to use session variables
// session_start();

// Include the database connection
include '../db.php'; 

// Query to fetch data from the accounting table
$query = "SELECT 
    s.staff_ID,
    CONCAT(s.firstname, ' ', s.lastname) AS fullname,
    a.poNumber,
    a.receivedCopy,
    a.paymentReceived,
    a.dateReceived,
    a.deadline,
    a.daysLeft,
    a.leadTime
FROM 
    staff s
RIGHT JOIN 
    accounting a ON s.staff_ID = a.staff_ID
ORDER BY 
    a.poNumber DESC;
";
$result = $conn->query($query);

if ($result) {
    $accountingData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $accountingData = [];
    $errorFetching = "Error fetching data: " . $conn->error;
}

// Handle update request
$updateSuccess = false;
$updateError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $poNumber = trim($_POST['poNumber'] ?? '');
    $receivedCopy = trim($_POST['receivedCopy'] ?? '');
    $paymentReceived = trim($_POST['paymentReceived'] ?? '');
    $staff_ID = $_SESSION['staff_ID'] ?? null;

    if ($poNumber !== '' && $receivedCopy !== '' && $staff_ID !== null) {
        // Allow paymentReceived to be empty if receivedCopy != 'Completed'
        if ($receivedCopy !== 'Completed') {
            $paymentReceived = ''; // Ensure disabled paymentReceived is empty
        }

        $updateQuery = "UPDATE accounting SET receivedCopy = ?, paymentReceived = ?, staff_ID = ? WHERE poNumber = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssss", $receivedCopy, $paymentReceived, $staff_ID, $poNumber);

        if ($stmt->execute()) {
            $updateSuccess = true;
        } else {
            $updateError = "Error updating record: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $updateError = "Invalid data received or session staff_ID missing.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Accounting Department</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
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
                    <th>Last Modified By</th>
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
                            <td><?= htmlspecialchars($data['fullname']); ?></td>
                            <td>
                                <button data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>" style="border: none;background-color:transparent;">
                                    <img src="../assets/edit2.png" alt="Edit" />
                                </button>
                            </td>
                        </tr>

                        <!-- Modal for editing each row -->
                        <div class="modal fade" id="editModal<?= htmlspecialchars($data['poNumber']); ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= htmlspecialchars($data['poNumber']); ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?= htmlspecialchars($data['poNumber']); ?>">Edit Accounting Record</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <input type="hidden" name="poNumber" value="<?= htmlspecialchars($data['poNumber']); ?>" />

                                            <div class="form-group">
                                                <label for="receivedCopy<?= htmlspecialchars($data['poNumber']); ?>">Received Copy</label>
                                                <select class="form-control" name="receivedCopy" id="receivedCopy<?= htmlspecialchars($data['poNumber']); ?>" required>
                                                    <option value="Not Started" <?= $data['receivedCopy'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['receivedCopy'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['receivedCopy'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="paymentReceived<?= htmlspecialchars($data['poNumber']); ?>">Payment Received</label>
                                                <select class="form-control" name="paymentReceived" id="paymentReceived<?= htmlspecialchars($data['poNumber']); ?>" <?= $data['receivedCopy'] != 'Completed' ? 'disabled' : ''; ?>>
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
                        <td colspan="9">No data found in the Accounting Department.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php if ($updateSuccess): ?>
            Swal.fire({
                icon: 'success',
                title: 'Update Successful',
                text: 'Record updated successfully!',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        <?php elseif ($updateError): ?>
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: <?= json_encode($updateError); ?>,
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>
</body>
</html>
