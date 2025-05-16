<?php
// Initialize $alertScript
$alertScript = '';

// Database connection
include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input data
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $middlename = $conn->real_escape_string($_POST['middlename']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $department = $conn->real_escape_string($_POST['department']);
    $position = $conn->real_escape_string($_POST['position']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);

    // SQL Insert query
    $sql = "INSERT INTO staff (firstname, middlename, lastname, phone, email, department, position, password, status, accountCreated) 
            VALUES ('$firstname', '$middlename', '$lastname', '$phone', '$email', '$department', '$position', '$password', 'Active', NOW())";

    if ($conn->query($sql) === TRUE) {
        // SweetAlert2 success script
        $alertScript = "
        <script>
            Swal.fire({
                title: 'Success!',
                text: 'Sign-up successful!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'index.php'; // Redirect on confirmation
                }
            });
        </script>";
    } else {
        // SweetAlert2 error script
        $alertScript = "
        <script>
            Swal.fire({
                title: 'Error!',
                text: 'Failed to sign up. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>33 Point 3 Exports Inc.</title>
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link rel="stylesheet" href="./style.css?v=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="user-input">
        <div class="left">
            <div class="left-logo">
                <div class="logo">
                    <img src="./assets/logo1.png" alt="" srcset="">
                </div>
            </div>
        </div>

        <div class="right">
            <div class="log-in">
                <p id="login">Sign Up</p>
                <br><br>
                <form action="" method="POST">
                    <div class="input-div">
                        <input type="text" name="firstname" placeholder="Firstname" required>
                    </div>

                    <div class="input-div">
                        <input type="text" name="middlename" placeholder="Middlename" required>
                    </div>

                    <div class="input-div">
                        <input type="text" name="lastname" placeholder="Lastname" required>
                    </div>

                    <div class="input-div">
                        <input type="text" name="phone" placeholder="Phone Number" required>
                    </div>

                    <div class="input-div">
                        <input type="email" name="email" placeholder="Email Address" required>
                    </div>

                    <div class="input-div">
                        <label for="department">Select Department</label>
                        <select name="department" id="department" required>
                            <option value="Marketing">Marketing</option>
                            <option value="Accounting">Accounting</option>
                            <option value="Monitoring">Monitoring</option>
                            <option value="Production">Production</option>
                            <option value="Shipping">Shipping</option>
                        </select>
                    </div>

                    <div class="input-div">
                        <input type="text" name="position" placeholder="Position" required>
                    </div>

                    <div class="input-div">
                        <input type="password" name="password" placeholder="Account Password" required>
                    </div>

                    <div class="btn">
                        <button type="submit">Sign Up</button>
                    </div>
                </form>

                <div class="signup">
                    <p>New staff? <a href="">Sign Up</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Output the SweetAlert2 script if set
    echo $alertScript;
    ?>
</body>
</html>
