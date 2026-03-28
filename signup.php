<?php
require_once 'config.php';

if (isLoggedIn()) { header('Location: profile.php'); exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($_POST['name'] ?? '');
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db    = getDB();
        $stmt  = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $ins->bind_param('sss', $name, $email, $hash);
            if ($ins->execute()) {
                $_SESSION['user_id']   = $ins->insert_id;
                $_SESSION['user_name'] = $name;
                header('Location: profile.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up — FinPilot AI</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
</head>
<body>
  <div class="grid-bg"></div>

  <div class="auth-wrap">
    <div class="auth-card" data-aos="fade-up">
      <div class="auth-logo">
        <a href="index.html" style="font-family:var(--font-head);font-size:1.6rem;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none;">⚡ FinPilot</a>
      </div>
      <div class="auth-title">Create Account</div>
      <div class="auth-subtitle">Start your financial journey with AI guidance</div>

      <?php if ($error): ?>
      <div class="alert alert-err">⚠️ <?= $error ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <div class="input-icon">
            <span class="icon">👤</span>
            <input type="text" name="name" class="form-input"
                   placeholder="Rahul Sharma"
                   value="<?= clean($_POST['name'] ?? '') ?>" required />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="input-icon">
            <span class="icon">✉️</span>
            <input type="email" name="email" class="form-input"
                   placeholder="rahul@example.com"
                   value="<?= clean($_POST['email'] ?? '') ?>" required />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-icon">
            <span class="icon">🔒</span>
            <input type="password" name="password" class="form-input"
                   placeholder="Min. 6 characters" required />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <div class="input-icon">
            <span class="icon">🔒</span>
            <input type="password" name="confirm" class="form-input"
                   placeholder="Repeat password" required />
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
          Create Account →
        </button>
      </form>

      <div class="auth-footer">
        Already have an account? <a href="login.php">Sign in</a>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>AOS.init({ once:true, duration:500 });</script>
</body>
</html>
