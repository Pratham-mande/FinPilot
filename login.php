<?php
require_once 'config.php';

if (isLoggedIn()) { header('Location: profile.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, name, password FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $redirect = $_GET['redirect'] ?? 'profile.php';
            header("Location: $redirect");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — FinPilot AI</title>
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
      <div class="auth-title">Welcome Back</div>
      <div class="auth-subtitle">Sign in to view your financial dashboard</div>

      <?php if ($error): ?>
      <div class="alert alert-err">⚠️ <?= $error ?></div>
      <?php endif; ?>

      <form method="POST" action="">
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
                   placeholder="Your password" required />
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
          Sign In →
        </button>
      </form>

      <div class="auth-footer" style="margin-top:16px;">
        Don't have an account? <a href="signup.php">Create one free</a>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>AOS.init({ once:true, duration:500 });</script>
</body>
</html>
