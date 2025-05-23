<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        empty($_POST['poNumber']) || 
        empty($_POST['leadTime']) || 
        empty($_POST['buyer'])
    ) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'All fields are required.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
        exit;
    }

    $poNumber = $_POST['poNumber'];
    $buyer = $_POST['buyer'];
    $leadTime = (int)$_POST['leadTime'];

    $orderDate = date('Y-m-d H:i:s');
    $shipDate = date('Y-m-d', strtotime("+$leadTime days"));
    $daysLeft = $leadTime;
    $overallStatus = "Not Started";

    // Check for duplicate PO Number
    $checkQuery = "SELECT COUNT(*) as count FROM Orders WHERE poNumber = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $poNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'PO Number already exists. Please use a unique PO Number.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
        $sql = "INSERT INTO Orders (poNumber, buyer, orderDate, shipDate, daysLeft, leadTime, overallStatus) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssssiss", $poNumber, $buyer, $orderDate, $shipDate, $daysLeft, $leadTime, $overallStatus);

            if ($stmt->execute()) {
                echo "<script>
                    Swal.fire({
                        title: 'Success!',
                        text: 'Order added successfully.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = window.location.href;
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to add the order. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                </script>";
            }

            $stmt->close();
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Query error: " . $conn->error . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    }

    $conn->close();
} else {
    echo "<script>
        Swal.fire({
            title: 'Error!',
            text: 'Invalid request method.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>";
}
?>
