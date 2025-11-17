<?php
require_once("../../database/config.php");
require_once("../../includes/session_check.php");
require_once("../../includes/audit_helper.php"); // cleaned logActivity

$user_id = $_SESSION['user_id'] ?? 0;
$message = "";

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, phone, preferences, avatar FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name   = trim($_POST['full_name'] ?? $user['full_name']);
    $email       = trim($_POST['email'] ?? $user['email']);
    $phone       = trim($_POST['phone'] ?? '');
    $preferences = trim($_POST['preferences'] ?? '');
    $avatar      = $user['avatar'];

    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../../assets/uploads/avatars/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_tmp  = $_FILES['avatar']['tmp_name'];
        $file_ext  = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed)) {
            $new_filename = "avatar_{$user_id}_" . time() . "." . $file_ext;
            $destination  = $upload_dir . $new_filename;
            if (move_uploaded_file($file_tmp, $destination)) {
                $avatar = "/assets/uploads/avatars/" . $new_filename;
            } else {
                $message = '<div class="alert alert-warning mt-3">Avatar upload failed.</div>';
            }
        } else {
            $message = '<div class="alert alert-warning mt-3">Invalid avatar format. Allowed: jpg, jpeg, png, gif.</div>';
        }
    }

    if (!empty($full_name) && !empty($email)) {
        $update = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, preferences=?, avatar=? WHERE user_id=?");
        $update->bind_param("sssssi", $full_name, $email, $phone, $preferences, $avatar, $user_id);

        if ($update->execute()) {
            $message = '<div class="alert alert-success mt-3">Profile updated successfully.</div>';
            logActivity($conn, $user_id, "Updated profile details");

            // Refresh user data
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $message = '<div class="alert alert-danger mt-3">Error updating profile. Please try again.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning mt-3">Full name and email are required.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile | Maktaba</title>
<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
body { background-color: #f8fafc; }
.profile-card { border: none; border-radius: 18px; overflow: hidden; box-shadow: 0 6px 16px rgba(0,0,0,0.1); }
.avatar-wrapper { text-align: center; margin-top: -70px; position: relative; }
.avatar-wrapper img { width: 140px; height: 140px; border-radius: 50%; border: 5px solid #fff; object-fit: cover; box-shadow: 0 5px 12px rgba(0,0,0,0.2); transition: transform 0.2s ease-in-out; }
.avatar-wrapper img:hover { transform: scale(1.05); }
.avatar-wrapper button { position: absolute; bottom: 10px; right: 35%; transform: translateX(50%); border-radius: 50%; }
</style>
</head>
<body>
<?php include("../../includes/user_nav.php"); ?>

<main class="container py-5">
    <div class="card profile-card shadow-lg mx-auto" style="max-width: 700px;">
        <div class="card-header bg-primary text-white text-center position-relative" style="height: 150px;">
            <h3 class="mt-4 fw-bold"><i class="bi bi-person-circle me-2"></i>My Profile</h3>
        </div>

        <!-- Avatar with overlay -->
        <div class="avatar-wrapper">
            <img id="avatarPreview" src="<?= htmlspecialchars($user['avatar'] ?: '/assets/img/default-avatar.png') ?>" alt="User Avatar">
            <button class="btn btn-sm btn-light shadow-sm" data-bs-toggle="modal" data-bs-target="#avatarModal">
                <i class="bi bi-camera-fill text-primary"></i>
            </button>
        </div>

        <div class="card-body px-5 pb-4">
            <?= $message ?>
            <div class="text-center mt-3">
                <h5 class="fw-semibold"><?= htmlspecialchars($user['full_name']) ?></h5>
                <p class="text-muted mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email']) ?></p>
                <p class="text-muted mb-1"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($user['phone'] ?: 'N/A') ?></p>
                <p class="text-muted"><i class="bi bi-heart me-2"></i><?= htmlspecialchars($user['preferences'] ?: 'No preferences set') ?></p>
            </div>
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil-square me-1"></i>Edit Profile
                </button>
            </div>
        </div>
    </div>
</main>

<!-- Avatar Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-bounding-box me-2"></i>Change Avatar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="avatarModalPreview" src="<?= htmlspecialchars($user['avatar'] ?: '/assets/img/default-avatar.png') ?>" 
                         class="rounded-circle mb-3" style="width:120px; height:120px; object-fit:cover;">
                    <input type="file" name="avatar" id="avatarInputModal" class="form-control" accept="image/*">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Preferences</label>
                        <textarea name="preferences" class="form-control" rows="3"><?= htmlspecialchars($user['preferences'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold"><i class="bi bi-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?= date('Y') ?> Maktaba Bookstore | User Profile</small>
</footer>
</body>
</html>
