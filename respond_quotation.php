<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Access denied.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quotation_id = (int)$_POST['quotation_id'];
    $action = $_POST['action'];

    if (!in_array($action, ['accept', 'reject'])) {
        echo "Invalid action.";
        exit();
    }

    // Update quotation status
    $status = $action === 'accept' ? 'accepted' : 'rejected';

    $stmt = $conn->prepare("UPDATE quotations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $quotation_id);
    $stmt->execute();

    // Optional: Notify pharmacy via email
    $query = "
        SELECT u.name AS user_name, u.email AS user_email, q.total_amount, p.id AS prescription_id
        FROM quotations q
        JOIN prescriptions p ON q.prescription_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE q.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quotation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $to = "pharmacy@example.com"; // replace with actual email
        $subject = "Quotation $status by user";
        $message = "User {$row['user_name']} has $status your quotation for prescription #{$row['prescription_id']} (Amount: Rs. {$row['total_amount']})";
        $headers = "From: noreply@example.com";

        @mail($to, $subject, $message, $headers); // suppress error if mail not configured
    }

    // Redirect back
    header("Location: user_dashboard.php?status=$status");
    exit();
}

echo "Invalid request.";
?>
