<?php
require_once 'config.php';
requireLogin();

$user = currentUser();
$uid  = (int)$_SESSION['user_id'];
$db   = getDB();

// Check profile exists
$fd = $db->query("SELECT id FROM financial_data WHERE user_id = $uid LIMIT 1")->fetch_assoc();
if (!$fd) { header('Location: profile.php'); exit; }

// Load existing responses
$existing = [];
$res = $db->query("SELECT * FROM behavioral_responses WHERE user_id = $uid LIMIT 1");
if ($res) $existing = $res->fetch_assoc() ?? [];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $q1 = clean($_POST['q1'] ?? 'Not Sure');
    $q2 = clean($_POST['q2'] ?? 'Not Sure');
    $q3 = clean($_POST['q3'] ?? 'Not Sure');
    $q4 = clean($_POST['q4'] ?? 'Not Sure');
    $q5 = clean($_POST['q5'] ?? 'Not Sure');

    // Save responses
    if ($existing) {
        $stmt = $db->prepare("UPDATE behavioral_responses SET q1=?,q2=?,q3=?,q4=?,q5=? WHERE user_id=?");
        $stmt->bind_param('sssssi', $q1,$q2,$q3,$q4,$q5,$uid);
    } else {
        $stmt = $db->prepare("INSERT INTO behavioral_responses (user_id,q1,q2,q3,q4,q5) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('isssss', $uid,$q1,$q2,$q3,$q4,$q5);
    }

    if ($stmt->execute()) {

        // 🔥 Get financial data
        $fd_res = $db->query("SELECT * FROM financial_data WHERE user_id=$uid LIMIT 1");
        $fd = $fd_res->fetch_assoc();

        // 🔥 Prepare answers
        $resp = [
            "q1"=>$q1,
            "q2"=>$q2,
            "q3"=>$q3,
            "q4"=>$q4,
            "q5"=>$q5
        ];

        // 🔥 Build prompt
        $prompt = buildFinancialPrompt($fd, $resp);

        // 🔥 Call Gemini
        $aiResult = callAI($prompt);

        // 🔥 Fallback (important)
        if (!$aiResult) {
            $aiResult = [
                "score" => 50,
                "scoreLabel" => "Fallback",
                "insights" => ["AI failed, using default"],
                "personality" => "Basic Planner",
                "plans" => []
            ];
        }

        // 🔥 Save AI result
        $json = json_encode($aiResult);

        $stmt2 = $db->prepare("UPDATE financial_data SET ai_result=? WHERE user_id=?");
        $stmt2->bind_param("si", $json, $uid);
        $stmt2->execute();

        header('Location: processing.html');
        exit;
    }
}
$questions = [
  ['key'=>'q1','icon'=>'🏦','title'=>'Emergency Fund','question'=>'Do you have an emergency fund (3–6 months of expenses)?'],
  ['key'=>'q2','icon'=>'📈','title'=>'Monthly Investing','question'=>'Do you invest money every month (SIP, stocks, FD, etc.)?'],
  ['key'=>'q3','icon'=>'📊','title'=>'Expense Tracking','question'=>'Do you actively track your daily expenses?'],
  ['key'=>'q4','icon'=>'🛡️','title'=>'Insurance Coverage','question'=>'Do you have adequate health and life insurance?'],
  ['key'=>'q5','icon'=>'🎯','title'=>'Long-term Investment','question'=>'Have you invested in any long-term instruments (PPF, NPS, ELSS)?'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Behavioral Assessment — FinPilot AI</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
</head>
<body>
  <div class="grid-bg"></div>

  <nav>
    <a href="index.html" class="nav-logo">⚡ FinPilot</a>
    <div class="nav-links">
      <a href="profile.php" style="color:var(--muted);font-size:0.88rem;">← Back</a>
      <span style="color:var(--muted);font-size:0.88rem;">👋 <?= clean($user['name'] ?? 'User') ?></span>
      <a href="logout.php">Logout</a>
    </div>
  </nav>

  <div class="page-wrap">
    <!-- Progress -->
    <div class="progress-bar-wrap" data-aos="fade-down">
      <div class="progress-steps">
        <div class="prog-step">
          <div class="prog-dot done">✓</div>
          <div class="prog-line done"></div>
        </div>
        <div class="prog-step">
          <div class="prog-dot active">2</div>
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
        <span style="color:var(--accent3);font-weight:600;">Profile ✓</span>
        <span style="color:var(--accent);font-weight:600;">Questions</span>
        <span>AI Analysis</span>
        <span>Results</span>
      </div>
    </div>

    <div class="page-header" data-aos="fade-up">
      <h1>❓ Quick Assessment</h1>
      <p>5 questions to understand your financial behavior. Takes less than 2 minutes.</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-err">⚠️ <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="" id="qForm">
      <?php foreach ($questions as $i => $q): ?>
      <div class="question-card" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
        <div class="q-label">
          <div class="q-num"><?= $i+1 ?></div>
          <span><?= $q['icon'] ?> <?= $q['question'] ?></span>
        </div>
        <div class="radio-group">
          <?php foreach (['Yes','No','Not Sure'] as $opt): ?>
          <div class="radio-item">
            <input type="radio" name="<?= $q['key'] ?>"
                   id="<?= $q['key'].$opt ?>"
                   value="<?= $opt ?>"
                   <?= ($existing[$q['key']] ?? 'Not Sure') === $opt ? 'checked' : '' ?> />
            <label for="<?= $q['key'].$opt ?>">
              <?= $opt === 'Yes' ? '✅' : ($opt === 'No' ? '❌' : '🤔') ?> <?= $opt ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <div data-aos="fade-up" data-aos-delay="420">
        <button type="submit" class="btn btn-primary btn-full btn-lg" id="submitBtn">
          🤖 Run AI Analysis
        </button>
        <p style="text-align:center;color:var(--muted);font-size:0.82rem;margin-top:12px;">
          AI will analyze your complete profile and generate personalized financial plans
        </p>
      </div>
    </form>
  </div>

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({ once: true, duration: 600, easing: 'ease-out-cubic' });

    // Auto-select "Not Sure" for any unanswered
    document.getElementById('qForm').addEventListener('submit', function() {
      ['q1','q2','q3','q4','q5'].forEach(q => {
        if (!document.querySelector(`input[name="${q}"]:checked`)) {
          document.getElementById(q + 'Not Sure').checked = true;
        }
      });
      document.getElementById('submitBtn').textContent = '⏳ Processing...';
      document.getElementById('submitBtn').disabled = true;
    });
  </script>
</body>
</html>
