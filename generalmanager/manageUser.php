<?php
// Include the database connection
include '../db.php'; // Assuming the database connection is in db.php

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $staff_ID = $_POST['id'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $password = $_POST['password'];
    $status = $_POST['status'];

    // Update the staff record
    $updateQuery = "UPDATE staff SET 
        firstname = ?, 
        middlename = ?, 
        lastname = ?, 
        phone = ?, 
        email = ?, 
        department = ?, 
        position = ?, 
        password = ?, 
        status = ? 
        WHERE staff_ID = ?";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('sssssssssi', $firstname, $middlename, $lastname, $phone, $email, $department, $position, $password, $status, $staff_ID);
    $success = $stmt->execute();
    $stmt->close();
}

// Query to fetch data from the staff table
$query = "SELECT staff_ID, firstname, middlename, lastname, phone, email, department, position, password, status, accountCreated FROM staff";
$result = $conn->query($query);
$staffData = ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2><b>Staff Table</b></h2>
        <hr>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($staffData)) : ?>
                        <?php foreach ($staffData as $data) : ?>
                            <tr>
                                <td><?= htmlspecialchars($data['staff_ID']); ?></td>
                                <td><?= htmlspecialchars($data['firstname']); ?></td>
                                <td><?= htmlspecialchars($data['middlename']); ?></td>
                                <td><?= htmlspecialchars($data['lastname']); ?></td>
                                <td><?= htmlspecialchars($data['department']); ?></td>
                                <td><?= htmlspecialchars($data['position']); ?></td>
                                <td><?= htmlspecialchars($data['status']); ?></td>
                                <td style="text-align:center; border-color:transparent;">
                                    <button data-toggle="modal" data-target="#editModal<?= $data['staff_ID']; ?>" style="border: none; background: none; padding: 0; outline: none;">
                                        <img src="../assets/edit2.png" alt="Edit" />
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal for editing staff details -->
                            <div class="modal fade" id="editModal<?= $data['staff_ID']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['staff_ID']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $data['staff_ID']; ?>">Edit Staff Details</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST" action="">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($data['staff_ID']); ?>">
                                                <div class="form-group">
                                                    <label for="firstname">First Name</label>
                                                    <input type="text" class="form-control" name="firstname" value="<?= htmlspecialchars($data['firstname']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="middlename">Middle Name</label>
                                                    <input type="text" class="form-control" name="middlename" value="<?= htmlspecialchars($data['middlename']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="lastname">Last Name</label>
                                                    <input type="text" class="form-control" name="lastname" value="<?= htmlspecialchars($data['lastname']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="phone">Phone</label>
                                                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($data['phone']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($data['email']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="department">Department</label>
                                                    <input type="text" class="form-control" name="department" value="<?= htmlspecialchars($data['department']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="position">Position</label>
                                                    <input type="text" class="form-control" name="position" value="<?= htmlspecialchars($data['position']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="password">Password</label>
                                                    <input type="password" class="form-control" name="password" value="<?= htmlspecialchars($data['password']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="status">Status</label>
                                                    <select class="form-control" name="status">
                                                        <option value="Active" <?= $data['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="Inactive" <?= $data['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-success">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="12">No data found in the staff table.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (isset($success) && $success): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Staff details updated successfully.',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
