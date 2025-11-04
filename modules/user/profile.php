<?php
require_once("../../database/config.php");
require_once("../../includes/session_check.php");
require_once("../../includes/logger.php");

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, phone, preferences, avatar FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$user = $user ?? [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'preferences' => '',
    'avatar' => ''
];

// Handle AJAX update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $preferencesArray = $_POST['preferences'] ?? [];
    $preferences = implode(',', $preferencesArray);

    // Handle avatar upload
    $avatarFile = $user['avatar'];
    $uploadDir = realpath(__DIR__ . '/../../Assets/avatars/') . DIRECTORY_SEPARATOR;

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES['avatar']['name']);
        $targetFile = $uploadDir . $fileName;

        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes)) {
            move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile);
            $avatarFile = $fileName;
        }
    }

    // Update user record
    $update = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, preferences=?, avatar=? WHERE user_id=?");
    $update->bind_param("sssssi", $full_name, $email, $phone, $preferences, $avatarFile, $user_id);

    if ($update->execute()) {
        if (function_exists('logActivity')) {
            logActivity($user_id, "Updated profile information and avatar");
        }
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating profile.']);
    }
    logActivity($user_id, "Updated profile information and avatar");

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<<<<<<< HEAD
<meta charset="UTF-8">
<title>My Profile | Maktaba</title>
<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body>
<?php include("../../includes/user_nav.php"); ?>
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <div class="profile-card d-flex flex-wrap">
        <!-- Left section -->
        <div class="profile-left col-md-4 d-flex flex-column align-items-center justify-content-center">
          <img src="https://via.placeholder.com/120" alt="Avatar">
          <h4><?= htmlspecialchars($user['full_name'] ?? 'User') ?></h4>
          <p><?= htmlspecialchars($user['email'] ?? 'Email') ?></p>
        </div>
        <!-- Right section (read-only info) -->
        <div class="profile-right col-md-8">
          <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? '—') ?></p>
          <p><strong>Preferred Genres:</strong>
            <?php
              if(!empty($user['preferences'])){
                  echo htmlspecialchars(str_replace(',', ', ', $user['preferences']));
              } else {
                  echo '—';
              }
            ?>
          </p>
          <button class="btn btn-success mt-3 btn-edit" data-bs-toggle="modal" data-bs-target="#editModal">
            <i class="bi bi-pencil-square"></i> Edit Profile
          </button>
        </div>
      </div>
=======
    <meta charset="UTF-8">
    <title>User Profile - Maktaba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e6f4ea, #ffffff);
            font-family: 'Poppins', sans-serif;
            padding-top: 60px;
        }
        .profile-card {
            max-width: 700px;
            margin: auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .profile-header {
            background: #2d8a34;
            color: #fff;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }
        .profile-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #fff;
            margin-bottom: 10px;
            object-fit: cover;
            background: #fff;
        }
        .edit-avatar-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            transition: 0.3s;
        }
        .edit-avatar-btn:hover {
            background: rgba(255,255,255,0.4);
        }
        .profile-body {
            padding: 30px;
        }
        .btn-edit {
            background-color: #2d8a34;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: 0.3s;
        }
        .btn-edit:hover {
            background-color: #256c2a;
        }
        small.note {
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<div class="profile-card">
    <div class="profile-header">
        <?php
            $avatarPath = "../../Assets/avatars/" . (!empty($user['avatar']) ? htmlspecialchars($user['avatar']) : "default.png");
        ?>
        <img id="profileAvatar" src="<?= $avatarPath; ?>" alt="Avatar">
        <button class="edit-avatar-btn" data-bs-toggle="modal" data-bs-target="#editModal">
            <i class="bi bi-pencil-square"></i>
        </button>
        <h4><?= htmlspecialchars($user['full_name'] ?: 'User'); ?></h4>
        <p><?= htmlspecialchars($user['email'] ?: 'Email not set'); ?></p>
    </div>
    <div class="profile-body">
        <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?: '—'); ?></p>
        <p><strong>Preferred Genres:</strong>
            <?= $user['preferences'] ? htmlspecialchars(str_replace(',', ', ', $user['preferences'])) : '—'; ?>
        </p>
        <button class="btn-edit mt-3" data-bs-toggle="modal" data-bs-target="#editModal">
            <i class="bi bi-pencil"></i> Edit Profile
        </button>
>>>>>>> 1d15ad97a48ed5a4ed0b2b7a7d4e305a4b69c3e9
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editProfileForm" enctype="multipart/form-data">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Edit Profile</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
              <img id="avatarPreview" src="<?= $avatarPath; ?>" alt="Avatar Preview" class="rounded-circle" width="120" height="120" style="object-fit: cover;">
              <input type="file" name="avatar" class="form-control mt-2" accept="image/*" onchange="previewAvatar(event)">
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Preferred Genres</label>
            <select name="preferences[]" class="form-select" multiple>
              <?php
                $genres = ['Fiction','Non-Fiction','Mystery','Fantasy','Science Fiction','Romance','Thriller','Biography','History'];
                $selected = $user['preferences'] ? explode(',', $user['preferences']) : [];
                foreach($genres as $genre){
                    $isSelected = in_array($genre, $selected) ? 'selected' : '';
                    echo "<option value=\"$genre\" $isSelected>$genre</option>";
                }
              ?>
            </select>
            <small class="note">💡 Hold <strong>Ctrl</strong> (or <strong>Cmd</strong> on Mac) and click to select multiple genres.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        document.getElementById('avatarPreview').src = URL.createObjectURL(file);
    }
}

document.getElementById('editProfileForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'update_profile');

    fetch('', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
          alert(data.message);
          if (data.success) location.reload();
      })
      .catch(() => alert('Something went wrong.'));
});
</script>
</body>
</html>