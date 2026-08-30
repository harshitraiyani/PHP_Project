<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? 'admin/dashboard.php' : 'customer/dashboard.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $phone = sanitize($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        flash('register', 'Passwords do not match.', 'danger');
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            flash('register', 'Email already registered.', 'danger');
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed);
            if ($stmt->execute()) {
                flash('login', 'Registration successful! Please login.', 'success');
                header("Location: login.php");
                exit;
            } else {
                flash('register', 'Something went wrong. Try again.', 'danger');
            }
        }
    }
    header("Location: register.php");
    exit;
}

$pageTitle = "Register";
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
  <div class="card auth-card p-4">
    <div class="icon-badge"><i class="bi bi-person-plus-fill"></i></div>
    <h4 class="text-center mb-3">Create Your Account</h4>
    <?php flash('register'); ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" minlength="6" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" minlength="6" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
    <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login here</a></p>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
