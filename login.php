<?php
session_start();
include 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate
    if (empty($email) || empty($password)) {
        $errors[] = "Both fields are required.";
    } else {
        // Fetch user
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result && password_verify($password, $result['password'])) {
            // Set session and redirect
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['user_name'] = $result['name'];
            header("Location: upload.php");
            exit();
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <link rel="stylesheet" type="text/css" href="style/style.css">
</head>
<body>

    <?php
    if (!empty($errors)) {
        echo "<ul style='color: red;'>";
        foreach ($errors as $e) echo "<li>$e</li>";
        echo "</ul>";
    }
    ?>
    <div class="login-container">
    <h2>Login</h2>
    <form method="post">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a>.</p>

    </div>
</body>
</html>
