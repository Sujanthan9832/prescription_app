<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_quotation'])) {
    $prescription_id = $_POST['prescription_id'];

    // Get user ID from prescription
    $stmt = $conn->prepare("SELECT user_id FROM prescriptions WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed (prescription lookup): " . $conn->error);
    }
    $stmt->bind_param("i", $prescription_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prescription = $result->fetch_assoc();

    if (!$prescription) {
        die("Prescription not found.");
    }

    $user_id = $prescription['user_id'];
    $quotation_items = $_SESSION['quotation_items'][$prescription_id] ?? [];

    if (empty($quotation_items)) {
        die("No quotation items found.");
    }

    // Calculate total
    $total = 0;
    foreach ($quotation_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Insert quotation
    $stmt = $conn->prepare("INSERT INTO quotations (prescription_id, user_id, total_amount, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
    if (!$stmt) {
        die("Prepare failed (insert quotation): " . $conn->error);
    }
    $stmt->bind_param("iid", $prescription_id, $user_id, $total);
    $stmt->execute();
    $quotation_id = $stmt->insert_id;

    // Insert each quotation item
    $stmt_item = $conn->prepare("INSERT INTO quotation_items (quotation_id, drug_name, quantity, price) VALUES (?, ?, ?, ?)");
    if (!$stmt_item) {
        die("Prepare failed (insert quotation_items): " . $conn->error);
    }

    foreach ($quotation_items as $item) {
        $stmt_item->bind_param("isid", $quotation_id, $item['drug'], $item['quantity'], $item['price']);
        $stmt_item->execute();
    }

    // Update prescription status
    $stmt = $conn->prepare("UPDATE prescriptions SET status = 'Quoted' WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed (update prescription): " . $conn->error);
    }
    $stmt->bind_param("i", $prescription_id);
    $stmt->execute();

    // Fetch user email
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed (user email): " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_email = $user['email'];

    // Send email
    $subject = "Quotation for Your Prescription";
    $message = "Dear User,\n\nA quotation has been prepared for your uploaded prescription.\nPlease log in to your account to view and accept or reject the quotation.\n\nThank you.";
    $headers = "From: ssujanthan02@gmail.com";

    if( mail($user_email, $subject, $message, $headers)){
        echo "Email sent successfully to $user_email";
    }else{
        echo "Sorry, failed while sending mail!";
    }
    // Clear session
    unset($_SESSION['quotation_items'][$prescription_id]);

    echo "<script>alert('Quotation sent successfully.'); window.location.href = 'pharmacy_dashboard.php';</script>";
    exit();
} else {
    echo "Invalid request.";
}
?>
