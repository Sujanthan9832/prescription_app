
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Access denied.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch prescriptions and associated quotations
$sql = "
    SELECT p.*, q.id AS quotation_id, q.total_amount, q.status AS quote_status
    FROM prescriptions p
    LEFT JOIN quotations q ON p.id = q.prescription_id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include 'components/header.php'; ?>
<!-- <!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
</head>
<body> -->
    <div class="dashboardContent">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
    <h3  class="titleGap">Your Prescriptions & Quotations</h3>

    <?php while ($row = $result->fetch_assoc()):
        $status_class = 'pending';
        $status_label = 'Pending';
        if ($row['quote_status'] === 'accepted') {
            $status_class = 'accepted'; $status_label = 'Accepted';
        } elseif ($row['quote_status'] === 'rejected') {
            $status_class = 'rejected'; $status_label = 'Rejected';
        } elseif ($row['quote_status']) {
            $status_class = 'quoted'; $status_label = 'Quoted';
        }
    ?>
        <div class="prescription <?= $status_class ?>">
            <p><strong>Note:</strong> <?= htmlspecialchars($row['note']) ?></p>
            <p><strong>Delivery:</strong> <?= htmlspecialchars($row['delivery_address']) ?> at <?= $row['delivery_time_slot'] ?></p>
            <p><strong>Status:</strong> <?= $status_label ?></p>

            <?php if ($row['quotation_id']): ?>
                <p><strong>Total Quotation:</strong> Rs. <?= number_format($row['total_amount'], 2) ?></p>

                <?php if ($row['quote_status'] === 'Pending' || empty($row['quote_status'])): ?>

                    <form action="respond_quotation.php" method="post" style="display:inline-block;">
                        <input type="hidden" name="quotation_id" value="<?= $row['quotation_id'] ?>">
                        <input type="hidden" name="action" value="accept">
                        <button type="submit" class="btn">Accept</button>
                    </form>

                    <form action="respond_quotation.php" method="post" style="display:inline-block;">
                        <input type="hidden" name="quotation_id" value="<?= $row['quotation_id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="secondtryBtn" style="" onmouseover="this.style.backgroundColor='red';" onmouseout="this.style.backgroundColor='';">Reject</button>
                    </form>
                <?php else: ?>
                    <p><em>You have <?= $status_label ?> this quotation.</em></p>
                <?php endif; ?>
            <?php else: ?>
                <p><em>Awaiting quotation from pharmacy.</em></p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
    </div>
<!-- </body>
</html> -->
<?php include 'components/footer.php'; ?>


