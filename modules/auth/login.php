<?php 
ini_set('session.cookie_path', '/');
session_start();
require_once("../../database/config.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // FIX: Changed to 'users' table and added user_role, is_verified check
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password_hash, user_role, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if user is verified
            if (!$user['is_verified']) {
                $error = "Please verify your email before logging in.";
            } elseif (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['user_role'];
                $_SESSION['email'] = $user['email'];
                
                // Redirect based on user role
                if ($user['user_role'] === 'admin') {
                    header("Location: /Maktaba/modules/admin/dashboard.php");
                } else {
                    header("Location: /Maktaba/modules/user/dashboard.php");
                }
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maktaba | Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/auth.css">
</head>
<body>
  <div class="auth-container">
    <div class="card p-4 border-0 shadow-lg text-center">
      <img src="../../assets/img/bg.png" alt="Maktaba Logo" width="70" class="mb-3">
      <h3 class="fw-bold text-primary mb-3">Login to Maktaba</h3>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="mb-3 text-start">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="mb-3 text-start">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn btn-success w-100 mb-3">Login</button>
      </form>

      <div class="d-flex justify-content-between">
        <a href="signup.php" class="text-primary text-decoration-none fw-semibold">Create Account</a>
        <a href="reset_password.php" class="text-primary text-decoration-none fw-semibold">Forgot Password?</a>
      </div>
    </div>
  </div>
</body>
</html>