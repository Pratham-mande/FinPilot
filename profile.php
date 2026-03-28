<?php
require_once 'config.php';
requireLogin();

$user  = currentUser();
$error = '';

// Load existing profile
$db   = getDB();
$uid  = (int)$_SESSION['user_id'];
$existing = [];
$res = $db->query("SELECT * FROM financial_data WHERE user_id = $uid LIMIT 1");
if ($res) $existing = $res->fetch_assoc() ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age      = $_POST['age'] ?? 0;
    $income   = (float)($_POST['income'] ?? 0);
    $expenses = (float)($_POST['expenses'] ?? 0);
    $savings  = (float)($_POST['savings'] ?? 0);
    $debt     = (float)($_POST['debt'] ?? 0);
    $risk     = clean($_POST['risk'] ?? 'Medium');
    $goals    = $_POST['goals'] ?? [];

    if (!$age || !$income) {
        $error = 'Age and Income are required fields.';
    } elseif ($age < 18 || $age > 90) {
        $error = 'Please enter a valid age (18–90).';
    } elseif ($income < 0 || $expenses < 0) {
        $error = 'Income and Expenses cannot be negative.';
    } else {
        $goalsJson = json_encode(array_map('clean', $goals));
        if ($existing) {
            $stmt = $db->prepare("UPDATE financial_data SET age=?,income=?,expenses=?,savings=?,debt=?,risk=?,goal=? WHERE user_id=?");
            $stmt->bind_param('dddddssd', $age,$income,$expenses,$savings,$debt,$risk,$goalsJson,$uid);
        } else {
            $stmt = $db->prepare("INSERT INTO financial_data (user_id,age,income,expenses,savings,debt,risk,goal) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param('iddddssl', $uid,$age,$income,$expenses,$savings,$debt,$risk,$goalsJson);
        }
        if ($stmt->execute()) {
            header('Location: questions.php');
            exit;
        } else {
            $error = 'Failed to save profile. Please try again.';
        }
    }
}

$existingGoals = json_decode($existing['goals'] ?? '[]', true) ?: [];
$goalOptions = [
  ['id'=>'House',      'icon'=>'🏠','label'=>'House'],
  ['id'=>'Travel',     'icon'=>'✈️','label'=>'Travel'],
  ['id'=>'Education',  'icon'=>'🎓','label'=>'Education'],
  ['id'=>'Marriage',   'icon'=>'💍','label'=>'Marriage'],
  ['id'=>'Retirement', 'icon'=>'🌅','label'=>'Retirement'],
  ['id'=>'Wealth',     'icon'=>'💰','label'=>'Wealth'],
  ['id'=>'Emergency',  'icon'=>'🚨','label'=>'Emergency'],
  ['id'=>'Debt',       'icon'=>'💳','label'=>'Debt-Free'],
  ['id'=>'Other',      'icon'=>'🎯','label'=>'Other'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Financial Profile — FinPilot AI</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
</head>
<body>
  <div class="grid-bg"></div>

  <nav>
    <a href="index.html" class="nav-logo">⚡ FinPilot</a>
    <div class="nav-links">
      <span style="color:var(--muted);font-size:0.88rem;">👋 <?= clean($user['name'] ?? 'User') ?></span>
      <a href="logout.php">Logout</a>
    </div>
  </nav>

  <div class="page-wrap">
    <!-- Progress -->
    <div class="progress-bar-wrap" data-aos="fade-down">
      <div class="progress-steps">
        <div class="prog-step">
          <div class="prog-dot active">1</div>
          <div class="prog-line"></div>
        </div>
        <div class="prog-step">
          <div class="prog-dot">2</div>
          <div class="prog-line"></div>
        </div>
        <div class="prog-step">
          <div class="prog-dot">3</div>
          <div class="prog-line"></div>
        </div>
        <div class="prog-step">
          <div class="prog-dot">✓</div>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:0.75rem;color:var(--muted);">
        <span style="color:var(--accent);font-weight:600;">Profile</span>
        <span>Questions</span>
        <span>AI Analysis</span>
        <span>Results</span>
      </div>
    </div>

    <div class="page-header" data-aos="fade-up">
      <h1>📋 Your Financial Profile</h1>
      <p>Tell us about your finances so AI can build your personalized plan.</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-err" data-aos="fade-in">⚠️ <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <!-- Basic Info -->
      <div class="card mb-24" data-aos="fade-up">
        <div class="card-header">
          <div class="card-icon">👤</div>
          <div class="card-title">Basic Information</div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Age</label>
            <input type="number" name="age" class="form-input"
                   placeholder="e.g. 28" min="18" max="90"
                   value="<?= $existing['age'] ?? ($_POST['age'] ?? '') ?>" required />
          </div>
          <div class="form-group">
            <label class="form-label">Risk Appetite</label>
            <div class="risk-slider" id="riskBtns">
              <button type="button" class="risk-btn low <?= ($existing['risk']??'Medium')==='Low'?'active':'' ?>" data-val="Low">🛡️ Low</button>
              <button type="button" class="risk-btn med <?= ($existing['risk']??'Medium')==='Medium'?'active':'' ?>" data-val="Medium">⚖️ Medium</button>
              <button type="button" class="risk-btn high <?= ($existing['risk']??'Medium')==='High'?'active':'' ?>" data-val="High">🚀 High</button>
            </div>
            <input type="hidden" name="risk" id="riskInput" value="<?= $existing['risk'] ?? 'Medium' ?>" />
          </div>
        </div>
      </div>

      <!-- Financial Data -->
      <div class="card mb-24" data-aos="fade-up" data-aos-delay="80">
        <div class="card-header">
          <div class="card-icon">💰</div>
          <div class="card-title">Monthly Financials</div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Monthly Income (₹)</label>
            <div class="input-prefix">
              <span class="prefix-symbol">₹</span>
              <input type="number" name="income" class="form-input"
                     placeholder="50000" min="0"
                     value="<?= $existing['income'] ?? ($_POST['income'] ?? '') ?>" required />
            </div>
            <div class="form-hint">Total take-home salary / business income</div>
          </div>
          <div class="form-group">
            <label class="form-label">Monthly Expenses (₹)</label>
            <div class="input-prefix">
              <span class="prefix-symbol">₹</span>
              <input type="number" name="expenses" class="form-input"
                     placeholder="35000" min="0"
                     value="<?= $existing['expenses'] ?? ($_POST['expenses'] ?? '') ?>" />
            </div>
            <div class="form-hint">Rent, food, utilities, transport</div>
          </div>
          <div class="form-group">
            <label class="form-label">Current Savings (₹)</label>
            <div class="input-prefix">
              <span class="prefix-symbol">₹</span>
              <input type="number" name="savings" class="form-input"
                     placeholder="200000" min="0"
                     value="<?= $existing['savings'] ?? ($_POST['savings'] ?? '') ?>" />
            </div>
            <div class="form-hint">Total savings / FD / liquid investments</div>
          </div>
          <div class="form-group">
            <label class="form-label">Total Debt (₹)</label>
            <div class="input-prefix">
              <span class="prefix-symbol">₹</span>
              <input type="number" name="debt" class="form-input"
                     placeholder="100000" min="0"
                     value="<?= $existing['debt'] ?? ($_POST['debt'] ?? '') ?>" />
            </div>
            <div class="form-hint">Credit card, loans, EMIs outstanding</div>
          </div>
        </div>
      </div>

      <!-- Goals -->
      <div class="card mb-24" data-aos="fade-up" data-aos-delay="160">
        <div class="card-header">
          <div class="card-icon">🎯</div>
          <div class="card-title">Financial Goals</div>
        </div>
        <p style="font-size:0.88rem;color:var(--muted);margin-bottom:16px;">Select all goals you want to achieve (you can pick multiple)</p>
        <div class="checkbox-grid">
          <?php foreach ($goalOptions as $g): ?>
          <div class="checkbox-item">
            <input type="checkbox" name="goals[]" id="goal_<?= $g['id'] ?>"
                   value="<?= $g['id'] ?>"
                   <?= in_array($g['id'], $existingGoals) ? 'checked' : '' ?> />
            <label for="goal_<?= $g['id'] ?>">
              <span class="cb-icon"><?= $g['icon'] ?></span>
              <?= $g['label'] ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div data-aos="fade-up" data-aos-delay="200">
        <button type="submit" class="btn btn-primary btn-full btn-lg">
          Continue to Questions →
        </button>
      </div>
    </form>
  </div>

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({ once: true, duration: 600, easing: 'ease-out-cubic' });

    // Risk buttons
    document.querySelectorAll('.risk-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.risk-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('riskInput').value = btn.dataset.val;
      });
    });
  </script>
</body>
</html>
