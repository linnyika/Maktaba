<?php
<<<<<<< HEAD
include_once __DIR__ . '/../config/config.php';
=======
include('../database/config.php');
>>>>>>> UI_Interface

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE customers SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_pass, $email);

    if ($stmt->execute()) {
        $message = "✅ Password updated successfully! You can now log in.";
    } else {
        $message = "❌ Failed to reset password. Try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maktaba | Set New Password</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #ffff, #00ddffff);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }
    .container {
      background: #fff;
      padding: 40px 50px;
      border-radius: 10px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      width: 350px;
      text-align: center;
    }
    h2 {
      color: #2ba0e8f9;
    }
    input {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    button {
      background-color: #00a2ffff;
      color: #fff;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background-color: #180a0117;
    }
    .message {
      margin-top: 15px;
      font-size: 14px;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Set New Password</h2>
    <form method="POST">
      <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
      <input type="password" name="password" placeholder="Enter new password" required>
      <button type="submit">Update Password</button>
    </form>
    <?php if ($message): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
