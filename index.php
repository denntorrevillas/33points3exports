<?php
// Start the session
session_start();

// Include the database connection
include 'db.php';

// Initialize alert script
$alertScript = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    // Check if user exists in the database
    $sql = "SELECT * FROM staff WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row['password'])) {
            // Store staff ID in session
            $_SESSION['staff_id'] = $row['id'];

            // Redirect based on department
            switch ($row['department']) {
                case 'Marketing':
                    header('Location: marketing');
                    break;
                case 'Accounting':
                    header('Location: accounting');
                    break;
                case 'Monitoring':
                    header('Location:monitoring');
                    break;
                case 'Production':
                    header('Location: production');
                    break;
                case 'Shipping':
                    header('Location: shipping');
                    break;
               
                default:
                    header('Location: general');
            }
            exit;
        } else {
            // Invalid password
            $alertScript = "
            <script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Invalid email or password.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    } else {
        // User not found
        $alertScript = "
        <script>
            Swal.fire({
                title: 'Error!',
                text: 'No account found with this email.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>33 Point 3 Exports Inc.</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="user-input">
        <div class="left">
            <div class="left-logo">
                <div class="logo">
                    <img src="./assets/logo1.png" alt="">
                </div>
            </div>
        </div>

        <div class="right">
            <div class="log-in">
                <p id="login">Log In</p>
                <br><br>
                <form action="" method="POST">
                    <div class="input-div">
                        <input type="text" name="email" placeholder="Enter your Email Address" required>
                    </div>
                    <div class="input-div">
                        <input type="password" name="password" placeholder="Enter your Password" required>
                    </div>
                    <div class="btn">
                        <button type="submit">Log In</button>
                    </div>
                </form>
                <div class="signup">
                    <p>New staff? <a href="signup.php">Sign Up</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Output the SweetAlert2 script
    echo $alertScript;
    ?>
</body>
</html>
