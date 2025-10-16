<?php
session_start(); 
include('../../config/config.php'); 
password_hash('1234', PASSWORD_DEFAULT);
$error = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        
        $query = "SELECT * FROM Customer WHERE Email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['is_verified'] == 1) {
                if (password_verify($password, $user['PasswordHash'])) {
                    $_SESSION['customer_id'] = $user['CustomerID'];
                    $_SESSION['fullname'] = $user['FullName'];
                    header("Location: ../../modules/dashboard/dashboard.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "Account not verified!! Please check your email for OTP code.";
            }
        } else {
            $error = "No account found with that email.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Maktaba</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login to Maktaba</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <p>Don’t have an account? <a href="signup.php">Sign Up</a></p>
    </div>
</body>
</html>
