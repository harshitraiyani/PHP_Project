<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? 'admin/dashboard.php' : 'customer/dashboard.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header("Location: " . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php'));
            exit;
        }
    }
    flash('login', 'Invalid email or password.', 'danger');
    header("Location: login.php");
    exit;
}

$pageTitle = "Login";
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
  <div class="card auth-card p-4">
    <div class="icon-badge"><i class="bi bi-shield-lock-fill"></i></div>
    <h4 class="text-center mb-3">Login to Your Account</h4>
    <?php flash('login'); ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p class="text-center mt-3 mb-0">Don't have an account? <a href="register.php">Register here</a></p>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
