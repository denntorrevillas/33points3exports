<?php
// Include the database connection
include '../db.php'; // Adjust the path to your database connection file

// Fetch data from the Shipping Table
$query = "SELECT 
    sh.poNumber,
    sh.pre_loading,
    sh.loading,
    sh.transported,
    sh.delivered_to_customer,
    sh.dateReceived,
    sh.deadline,
    sh.daysLeft,
    sh.leadTime,
    sh.staff_ID,
    CONCAT(s.firstname, ' ', s.lastname) AS fullname
FROM 
    shipping sh
LEFT JOIN 
    staff s
ON 
    sh.staff_ID = s.staff_ID
ORDER BY 
    sh.delivered_to_customer ASC;";
$result = $conn->query($query);

// Check if there are any results
$shippingData = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Initialize update result variables
$updateSuccess = false;
$updateError = '';

// Handle the update functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $pre_loading = $_POST['pre_loading'];
    $loading = $_POST['loading'];
    $transported = $_POST['transported'];
    $delivered_to_customer = $_POST['delivered_to_customer'];

    // Fetch existing daysLeft and dateReceived for this PO number
    $daysLeftQuery = "SELECT daysLeft, dateReceived FROM shipping WHERE poNumber = ?";
    $stmt = $conn->prepare($daysLeftQuery);
    $stmt->bind_param("s", $poNumber);
    $stmt->execute();
    $stmt->bind_result($daysLeft, $dateReceived);
    $stmt->fetch();
    $stmt->close();

    // Calculate deadline based on fetched daysLeft
    $deadline = date('Y-m-d', strtotime("$dateReceived +$daysLeft days"));

    // Update query
    $updateQuery = "
        UPDATE shipping 
        SET pre_loading = ?, 
            loading = ?, 
            transported = ?, 
            delivered_to_customer = ?, 
            deadline = ? ,
            staff_ID = ?
        WHERE poNumber = ?";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param(
        "sssssss", 
        $pre_loading, 
        $loading, 
        $transported, 
        $delivered_to_customer, 
        $deadline, 
         $staff_ID,
        $poNumber
    );

    if ($stmt->execute()) {
        $updateSuccess = true;
    } else {
        $updateError = $stmt->error;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2><b>Shipping Table</b></h2>
        <hr>

        <div class="table-div" style="overflow-x:auto;">
            <table class="table">
               
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
                        <th>Last Modified By</th>
                        <th>Action</th>
                    </tr>
               
                <tbody>
                    <?php if (!empty($shippingData)) : ?>
                        <?php foreach ($shippingData as $data) : ?>
                            <tr>
                                <td><?= htmlspecialchars($data['poNumber']); ?></td>
                                <td><?= htmlspecialchars($data['pre_loading']); ?></td>
                                <td><?= htmlspecialchars($data['loading']); ?></td>
                                <td><?= htmlspecialchars($data['transported']); ?></td>
                                <td><?= htmlspecialchars($data['delivered_to_customer']); ?></td>
                                <td><?= htmlspecialchars($data['dateReceived']); ?></td>
                                <td><?= htmlspecialchars($data['deadline']); ?></td>
                                <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                                <td><?= htmlspecialchars($data['leadTime']); ?></td>
                                 <td><?= htmlspecialchars($data['fullname']); ?></td>
                                <td style="text-align: center;">
                                    <button data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>" style="border: none;background-color:transparent;s">
                                    <img src="../assets/edit2.png" alt="Edit" />
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

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const targetColumns = [7]; // Target column index for Days Left
            const rows = document.querySelectorAll("table tbody tr");

            rows.forEach(row => {
                const cells = row.querySelectorAll("td");

                targetColumns.forEach(columnIndex => {
                    if (cells[columnIndex]) {
                        const value = parseInt(cells[columnIndex].textContent.trim(), 10);

                        if (!isNaN(value)) {
                            if (value >= 10) {
                                cells[columnIndex].style.backgroundColor = "green";
                                cells[columnIndex].style.color = "white";
                            } else if (value >= 4 && value <= 9) {
                                cells[columnIndex].style.backgroundColor = "orange";
                                cells[columnIndex].style.color = "white";
                            } else if (value >= 2 && value <= 3) {
                                cells[columnIndex].style.backgroundColor = "yellow";
                                cells[columnIndex].style.color = "black";
                            } else if (value <= 1) {
                                cells[columnIndex].style.backgroundColor = "red";
                                cells[columnIndex].style.color = "white";
                            }
                        }
                    }
                });
            });
        });

        <?php if ($updateSuccess): ?>
        Swal.fire({
            icon: 'success',
            title: 'Record updated successfully!',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            window.location.href = window.location.href;
        });
        <?php elseif ($updateError != ''): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error updating record',
            text: '<?= addslashes($updateError); ?>'
        });
        <?php endif; ?>
    </script>
</body>
</html>
