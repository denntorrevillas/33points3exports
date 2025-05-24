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
    // Server-side password strength validation
    $rawPassword = $_POST['password'];

    // Simple password strength check function (you can enhance this)
    function isStrongPassword($password) {
        // Minimum 8 characters, at least one uppercase, one lowercase, one number, and one special char
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
    }

    if (!isStrongPassword($rawPassword)) {
        $alertScript = "
        <script>
            Swal.fire({
                title: 'Weak Password!',
                text: 'Your password is too weak. Please use a stronger password.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
        // Sanitize and validate input data
        $firstname = $conn->real_escape_string($_POST['firstname']);
        $middlename = $conn->real_escape_string($_POST['middlename']);
        $lastname = $conn->real_escape_string($_POST['lastname']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $department = $conn->real_escape_string($_POST['department']);
        $position = $conn->real_escape_string($_POST['position']);
        $password = password_hash($conn->real_escape_string($rawPassword), PASSWORD_DEFAULT);

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
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>33 Point 3 Exports Inc.</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css?v=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Style for the password strength bar */
        #password-strength {
            width: 100%;
            height: 10px;
            margin-top: 5px;
            background-color: #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        #password-strength > div {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease-in-out;
        }
        .strength-weak {
            background-color: #ff4d4d;
        }
        .strength-medium {
            background-color: #ffa500;
        }
        .strength-strong {
            background-color: #4caf50;
        }
    </style>
</head>
<body>
    <div class="user-input">
        <div class="left">
            <div class="left-logo">
                <div class="logo">
                    <img src="./assets/logo1.png" alt="" srcset="" />
                </div>
            </div>
        </div>

        <div class="right">
            <div class="log-in">
                <p id="login">Sign Up</p>
                <br /><br />
                <form action="" method="POST" id="signupForm">
                    <div class="input-div">
                        <input type="text" name="firstname" placeholder="Firstname" required />
                    </div>

                    <div class="input-div">
                        <input type="text" name="middlename" placeholder="Middlename" required />
                    </div>

                    <div class="input-div">
                        <input type="text" name="lastname" placeholder="Lastname" required />
                    </div>

                    <div class="input-div">
                        <input type="text" name="phone" placeholder="Phone Number" required />
                    </div>

                    <div class="input-div">
                        <input type="email" name="email" placeholder="Email Address" required />
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
                        <input type="text" name="position" placeholder="Position" required />
                    </div>

                    <div class="input-div">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Account Password"
                            required
                            autocomplete="new-password"
                        />
                        <div id="password-strength"><div></div></div>
                        <small id="password-strength-text" style="font-weight:bold;"></small>
                    </div>

                    <div class="btn">
                        <button type="submit" id="submitBtn">Sign Up</button>
                    </div>
                </form>

                <div class="signup">
                    <p>Already have an account? <a href="index.php">Log In</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('password-strength').firstElementChild;
        const strengthText = document.getElementById('password-strength-text');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('signupForm');

        function evaluatePasswordStrength(password) {
            let score = 0;

            if (!password) return 0;

            // Criteria for scoring
            if (password.length >= 8) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[a-z]/.test(password)) score++;
            if (/\d/.test(password)) score++;
            if (/[\W_]/.test(password)) score++;

            return score;
        }

        function updateStrengthMeter(password) {
            const score = evaluatePasswordStrength(password);

            // Reset classes
            strengthBar.className = '';

            let strength = '';
            let width = '0%';

            if (score === 0) {
                strengthText.textContent = '';
                width = '0%';
                submitBtn.disabled = true;
            } else if (score <= 2) {
                strength = 'Weak';
                strengthBar.classList.add('strength-weak');
                width = '33%';
                submitBtn.disabled = true;
            } else if (score === 3 || score === 4) {
                strength = 'Medium';
                strengthBar.classList.add('strength-medium');
                width = '66%';
                submitBtn.disabled = false;
            } else if (score === 5) {
                strength = 'Strong';
                strengthBar.classList.add('strength-strong');
                width = '100%';
                submitBtn.disabled = false;
            }

            strengthBar.style.width = width;
            strengthText.textContent = strength;
        }

        passwordInput.addEventListener('input', () => {
            updateStrengthMeter(passwordInput.value);
        });

        // Initial disable submit button
        submitBtn.disabled = true;

        // Optional: prevent form submission if password weak (extra layer of client validation)
        form.addEventListener('submit', function (e) {
            const score = evaluatePasswordStrength(passwordInput.value);
            if (score <= 2) {
                e.preventDefault();
                Swal.fire({
                    title: 'Weak Password!',
                    text: 'Your password is too weak. Please use a stronger password.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>

    <?php
    // Output the SweetAlert2 script if set
    echo $alertScript;
    ?>
</body>
</html>
