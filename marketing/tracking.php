<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$data = [];
$error = '';
$poNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../db.php'; // your database connection

    $poNumber = trim($_POST['poNumber'] ?? '');

    if (empty($poNumber)) {
        $error = "Please enter a PO Number.";
    } else {
        $sql = "
            SELECT 
                m.poNumber,
                m.orderreceived AS marketing_datereceived,
                m.dateCompleted AS marketing_dateCompleted,
                m.completionSpan AS marketing_completionSpan,
                m.receivedOrder,
                m.businessAward,
                m.endorsedToGM,

                a.datereceived AS accounting_datereceived,
                a.dateCompleted AS accounting_dateCompleted,
                a.completionSpan AS accounting_completionSpan,
                a.receivedCopy,
                a.paymentReceived,

                mo.datereceived AS monitoring_datereceived,
                mo.dateCompleted AS monitoring_dateCompleted,
                mo.completionSpan AS monitoring_completionSpan,
                mo.supplierEvaluated,
                mo.supplierPOCreated,
                mo.gmApproved,
                mo.supplierPOIssued,

                p.datereceived AS production_datereceived,
                p.dateCompleted AS production_dateCompleted,
                p.completionSpan AS production_completionSpan,
                p.finishing,
                p.packed,
                p.inspected,

                s.datereceived AS shipping_datereceived,
                s.dateCompleted AS shipping_dateCompleted,
                s.completionSpan AS shipping_completionSpan,
                s.pre_loading,
                s.loading,
                s.transported,
                s.delivered_to_customer
            FROM marketinghistory m
            LEFT JOIN accountinghistory a ON m.poNumber = a.poNumber
            LEFT JOIN monitoringhistory mo ON m.poNumber = mo.poNumber
            LEFT JOIN productionhistory p ON m.poNumber = p.poNumber
            LEFT JOIN shippinghistory s ON m.poNumber = s.poNumber
            WHERE m.poNumber = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $poNumber);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $error = "PO Number not found.";
            } else {
                $data = $result->fetch_assoc();
            }
            $stmt->close();
        }
        $conn->close();
    }
}

// Function to get CSS class based on status text
function statusClass($value) {
    $val = strtolower(trim($value));
    if ($val === 'not started') {
        return 'status-not-started';
    } elseif ($val === 'in progress') {
        return 'status-in-progress';
    } elseif ($val === 'completed') {
        return 'status-completed';
    } else {
        if ($val === '' || $val === 'n/a' || $val === 'na' || $val === 'null') {
            return 'status-na';
        }
        return 'status-na';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>PO Tracking Timeline - Minimal</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
<style>
 
  .container {
    max-width: 600px;
    margin: auto;
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
  }
  h2 {
    text-align: center;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #111;
  }
  form {
    text-align: center;
    margin-bottom: 2rem;
  }
  input[type="text"] {
    width: 70%;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    transition: border-color 0.3s;
  }
  input[type="text"]:focus {
    border-color: #0077ff;
    outline: none;
  }
  button {
    margin-left: 0.8rem;
    background: #1d503a;
    color: white;
    border: none;
    padding: 0.55rem 1.3rem;
    font-size: 1rem;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
  }
  button:hover {
    background: rgb(23, 85, 58);
  }
  .error {
    color: #cc0000;
    text-align: center;
    margin-bottom: 1rem;
  }

  /* Minimal timeline styles */
  .timeline {
    position: relative;
    margin-left: 1rem;
    border-left: 2px solid #ddd;
    padding-left: 1.2rem;
  }
  .timeline-event {
    position: relative;
    margin-bottom: 2rem;
    padding-left: 1.2rem;
  }
  .timeline-event:last-child {
    margin-bottom: 0;
  }
  .timeline-event::before {
    content: "";
    position: absolute;
    left: -1.3rem;
    top: 6px;
    width: 12px;
    height: 12px;
    background: #1d503a;
    border-radius: 50%;
  }
  h3 {
    margin: 0 0 0.6rem 0;
    font-weight: 600;
    font-size: 1.1rem;
    color: #1d503a;
  }
  .field {
    font-size: 0.95rem;
    color: #555;
    margin-bottom: 0.3rem;
  }
  .label {
    font-weight: 600;
    color: #333;
  }

  /* Status colors */
  .status-not-started {
    color: #cc0000; /* red */
    font-weight: 600;
  }
  .status-in-progress {
    color: #d9a200; /* golden yellow */
    font-weight: 600;
  }
  .status-completed {
    color: #1d7a1d; /* green */
    font-weight: 600;
  }
  .status-na {
    color: #888888; /* gray */
    font-style: italic;
  }
</style>
</head>
<body>

<div class="container">
  <h2>Track PO Number</h2>
  <form method="POST" action="">
    <input type="text" name="poNumber" placeholder="Enter PO Number" value="<?= htmlspecialchars($poNumber ?? '') ?>" required />
    <button type="submit">Track</button>
  </form>

  <?php if (!empty($error)) : ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($data)) : ?>
    <div class="timeline">

      <div class="timeline-event">
        <h3>Marketing Department</h3>
        <div class="field"><span class="label">Order Received:</span> <span class="<?= statusClass($data['marketing_datereceived'] ?? '') ?>"><?= htmlspecialchars($data['marketing_datereceived'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Date Completed:</span> <span><?= htmlspecialchars($data['marketing_dateCompleted'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Completion Span:</span> <span><?= htmlspecialchars($data['marketing_completionSpan'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Received Order:</span> <span class="<?= statusClass($data['receivedOrder'] ?? '') ?>"><?= htmlspecialchars($data['receivedOrder'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Business Award:</span> <span class="<?= statusClass($data['businessAward'] ?? '') ?>"><?= htmlspecialchars($data['businessAward'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Endorsed To GM:</span> <span class="<?= statusClass($data['endorsedToGM'] ?? '') ?>"><?= htmlspecialchars($data['endorsedToGM'] ?? 'N/A') ?></span></div>
      </div>

      <div class="timeline-event">
        <h3>Accounting Department</h3>
        <div class="field"><span class="label">Order Received:</span> <span class="<?= statusClass($data['accounting_datereceived'] ?? '') ?>"><?= htmlspecialchars($data['accounting_datereceived'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Date Completed:</span> <span><?= htmlspecialchars($data['accounting_dateCompleted'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Completion Span:</span> <span><?= htmlspecialchars($data['accounting_completionSpan'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Received Copy:</span> <span class="<?= statusClass($data['receivedCopy'] ?? '') ?>"><?= htmlspecialchars($data['receivedCopy'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Payment Received:</span> <span class="<?= statusClass($data['paymentReceived'] ?? '') ?>"><?= htmlspecialchars($data['paymentReceived'] ?? 'N/A') ?></span></div>
      </div>

      <div class="timeline-event">
        <h3>Monitoring Department</h3>
        <div class="field"><span class="label">Order Received:</span> <span class="<?= statusClass($data['monitoring_datereceived'] ?? '') ?>"><?= htmlspecialchars($data['monitoring_datereceived'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Date Completed:</span> <span><?= htmlspecialchars($data['monitoring_dateCompleted'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Completion Span:</span> <span><?= htmlspecialchars($data['monitoring_completionSpan'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Supplier Evaluated:</span> <span class="<?= statusClass($data['supplierEvaluated'] ?? '') ?>"><?= htmlspecialchars($data['supplierEvaluated'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Supplier PO Created:</span> <span class="<?= statusClass($data['supplierPOCreated'] ?? '') ?>"><?= htmlspecialchars($data['supplierPOCreated'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">GM Approved:</span> <span class="<?= statusClass($data['gmApproved'] ?? '') ?>"><?= htmlspecialchars($data['gmApproved'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Supplier PO Issued:</span> <span class="<?= statusClass($data['supplierPOIssued'] ?? '') ?>"><?= htmlspecialchars($data['supplierPOIssued'] ?? 'N/A') ?></span></div>
      </div>

      <div class="timeline-event">
        <h3>Production Department</h3>
        <div class="field"><span class="label">Order Received:</span> <span class="<?= statusClass($data['production_datereceived'] ?? '') ?>"><?= htmlspecialchars($data['production_datereceived'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Date Completed:</span> <span><?= htmlspecialchars($data['production_dateCompleted'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Completion Span:</span> <span><?= htmlspecialchars($data['production_completionSpan'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Finishing:</span> <span class="<?= statusClass($data['finishing'] ?? '') ?>"><?= htmlspecialchars($data['finishing'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Packed:</span> <span class="<?= statusClass($data['packed'] ?? '') ?>"><?= htmlspecialchars($data['packed'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Inspected:</span> <span class="<?= statusClass($data['inspected'] ?? '') ?>"><?= htmlspecialchars($data['inspected'] ?? 'N/A') ?></span></div>
      </div>

      <div class="timeline-event">
        <h3>Shipping Department</h3>
        <div class="field"><span class="label">Order Received:</span> <span class="<?= statusClass($data['shipping_datereceived'] ?? '') ?>"><?= htmlspecialchars($data['shipping_datereceived'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Date Completed:</span> <span><?= htmlspecialchars($data['shipping_dateCompleted'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Completion Span:</span> <span><?= htmlspecialchars($data['shipping_completionSpan'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Pre Loading:</span> <span class="<?= statusClass($data['pre_loading'] ?? '') ?>"><?= htmlspecialchars($data['pre_loading'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Loading:</span> <span class="<?= statusClass($data['loading'] ?? '') ?>"><?= htmlspecialchars($data['loading'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Transported:</span> <span class="<?= statusClass($data['transported'] ?? '') ?>"><?= htmlspecialchars($data['transported'] ?? 'N/A') ?></span></div>
        <div class="field"><span class="label">Delivered to Customer:</span> <span class="<?= statusClass($data['delivered_to_customer'] ?? '') ?>"><?= htmlspecialchars($data['delivered_to_customer'] ?? 'N/A') ?></span></div>
      </div>

    </div>
  <?php endif; ?>

</div>

</body>
</html>
