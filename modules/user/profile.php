<?php
require_once("../../database/config.php");
require_once("../../includes/session_check.php");

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = "";

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, phone, preferences FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle AJAX save request from modal
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $preferencesArray = $_POST['preferences'] ?? [];
    $preferences = implode(',', $preferencesArray);

    if (empty($full_name)) {
        $message = "Full name cannot be empty.";
        $message_type = "danger";
    } elseif (!empty($phone) && !preg_match("/^[0-9+]{7,15}$/", $phone)) {
        $message = "Phone number is invalid.";
        $message_type = "danger";
    } else {
        $update = $conn->prepare("UPDATE users SET full_name=?, phone=?, preferences=? WHERE user_id=?");
        $update->bind_param("sssi", $full_name, $phone, $preferences, $user_id);

        if ($update->execute()) {
            $message = "Profile updated successfully.";
            $message_type = "success";
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $message = "Failed to update profile.";
            $message_type = "danger";
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['message' => $message, 'type' => $message_type]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile | Maktaba</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #e9f7ef; }
.profile-card { border-radius: 15px; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
.profile-left { background: #28a745; color: white; padding: 2rem; text-align: center; }
.profile-left img { width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; margin-bottom: 1rem; }
.profile-left h4 { margin-bottom: 0.5rem; }
.profile-left p { font-size: 0.9rem; }
.profile-right { padding: 2rem; background: white; }
.btn-edit { border-radius: 8px; transition: all 0.2s ease; }
.btn-edit:hover { transform: translateY(-2px); }
</style>
</head>
<body>
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
    </div>
  </div>
</div>

<!-- Modal for editing -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editProfileForm">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Edit Profile</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Preferred Genres</label>
            <select name="preferences[]" class="form-select" multiple>
              <?php
                $genres = ['Fiction','Non-Fiction','Mystery','Fantasy','Science Fiction','Romance','Thriller','Biography','History'];
                $selected = isset($user['preferences']) ? explode(',', $user['preferences']) : [];
                foreach($genres as $genre){
                    $isSelected = in_array($genre, $selected) ? 'selected' : '';
                    echo "<option value=\"$genre\" $isSelected>$genre</option>";
                }
              ?>
            </select>
            <small class="text-muted">Hold Ctrl (Cmd on Mac) to select multiple genres.</small>
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
document.getElementById('editProfileForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'update_profile');

    fetch('', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
          alert(data.message);
          if(data.type === 'success') {
              location.reload(); // refresh page to show updated data
          }
      });
});
</script>
</body>
</html>
