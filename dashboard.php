<?php
require_once 'config.php';
requireLogin();

$user = currentUser();
$uid  = (int)$_SESSION['user_id'];
$db   = getDB();

$aiRes = $db->query("SELECT * FROM ai_results WHERE user_id = $uid ORDER BY updated_at DESC LIMIT 1")->fetch_assoc();
if (!$aiRes) { header('Location: profile.php'); exit; }

$score       = (int)($aiRes['score'] ?? 0);
$scoreLabel  = $aiRes['score_label'] ?: scoreLabel($score);
$insights    = json_decode($aiRes['insights'] ?? '[]', true) ?: [];
$personality = $aiRes['personality'] ?? 'Balanced Planner';
$pIcon       = $aiRes['personality_icon'] ?? '🧠';
$pDesc       = $aiRes['personality_desc'] ?? '';
$stats       = json_decode($aiRes['stats'] ?? '{}', true) ?: [];
$color       = $score;
$selectedPlan = $aiRes['selected_plan'] ?? null;

// Circumference for SVG ring
$radius      = 60;
$circumference= 2 * M_PI * $radius;
$dash        = $circumference - ($circumference * $score / 100);

$insightColors = ['#38bdf8','#34d399','#fbbf24','#f87171','#818cf8'];

$statBars = [
  ['label'=>'Savings Rate',      'key'=>'savingsRate',      'color'=>'#34d399'],
  ['label'=>'Debt Ratio',        'key'=>'debtRatio',        'color'=>'#f87171'],
  ['label'=>'Expense Ratio',     'key'=>'expenseRatio',     'color'=>'#fbbf24'],
  ['label'=>'Investment Score',  'key'=>'investmentScore',  'color'=>'#818cf8'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard — FinPilot AI</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
</head>
<body>
  <div class="grid-bg"></div>

  <nav>
    <a href="index.html" class="nav-logo">⚡ FinPilot</a>
    <div class="nav-links">
      <a href="dashboard.php" style="color:var(--accent)">Dashboard</a>
     <!-- <a href="roadmap.php">Plans</a>-->
      <a href="chat.php">AI Chat</a>
      <a href="profile.php">Edit Profile</a>
      <a href="logout.php">Logout</a>
    </div>
  </nav>

  <div class="page-wrap-wide" style="position:relative;z-index:1;">
    <div class="page-header" data-aos="fade-up">
      <h1>🧠 Your Financial Dashboard</h1>
      <p>AI-powered analysis of your complete financial health</p>
    </div>

    <?php if ($selectedPlan): ?>
    <div class="alert alert-ok" data-aos="fade-in">
      ✅ You've selected the <strong><?= clean($selectedPlan) ?></strong>. View your full roadmap below.
    </div>
    <?php endif; ?>

    <div class="dash-grid">

      <!-- SCORE CARD -->
      <div class="card dash-score" data-aos="fade-up">
        <div class="card-header">
          <div class="card-icon">🎯</div>
          <div class="card-title">Money Health Score</div>
        </div>
        <div style="display:flex;align-items:center;gap:32px;flex-wrap:wrap;">
          <div class="score-ring-wrap">
            <div class="score-ring">
              <svg width="160" height="160" viewBox="0 0 160 160">
                <circle class="ring-bg" cx="80" cy="80" r="<?= $radius ?>" />
                <circle class="ring-fill" cx="80" cy="80" r="<?= $radius ?>"
                  stroke="<?= $color ?>"
                  stroke-dasharray="<?= $circumference ?>"
                  stroke-dashoffset="<?= $circumference ?>"
                  id="ringFill" />
              </svg>
              <div class="score-center">
                <div class="score-num" style="color:<?= $color ?>" id="scoreNum">0</div>
                <div class="score-denom">/100</div>
              </div>
            </div>
            <div class="score-label" style="color:<?= $color ?>"><?= $scoreLabel ?></div>
          </div>
          <div style="flex:1;min-width:200px;">
            <div style="font-size:0.85rem;color:var(--muted);margin-bottom:16px;line-height:1.6;">
              Your financial health score is calculated based on your savings rate, debt obligations, investment habits and behavioral patterns.
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <?php
              $grades = [
                [80,'#34d399','Excellent 🌟'],
                [65,'#38bdf8','Good 👍'],
                [50,'#fbbf24','Average ⚡'],
                [35,'#f87171','Needs Work ⚠️'],
                [0, '#f87171','Critical 🚨'],
              ];
              foreach ($grades as [$threshold, $gc, $gl]):
                $isActive = $score >= $threshold && ($threshold === 0 || true);
              ?>
              <div style="display:flex;align-items:center;gap:10px;font-size:0.82rem;opacity:<?= $score >= $threshold ? '1' : '0.3' ?>">
                <div style="width:10px;height:10px;border-radius:50%;background:<?= $gc ?>;flex-shrink:0;"></div>
                <?= $threshold ?>+ — <?= $gl ?>
              </div>
              <?php break; // show only matching
              endforeach; ?>
              <?php foreach ($grades as [$threshold, $gc, $gl]): ?>
              <div style="display:flex;align-items:center;gap:10px;font-size:0.82rem;opacity:<?= ($score >= $threshold) ? '1':'0.3' ?>">
                <div style="width:10px;height:10px;border-radius:50%;background:<?= $gc ?>;flex-shrink:0;"></div>
                <?= $threshold ?>+ — <?= $gl ?>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- INSIGHTS -->
      <div class="card dash-insights" data-aos="fade-up" data-aos-delay="80">
        <div class="card-header">
          <div class="card-icon">💡</div>
          <div class="card-title">AI Insights</div>
        </div>
        <div class="insight-list">
          <?php foreach ($insights as $idx => $insight): ?>
          <div class="insight-item" data-aos="fade-left" data-aos-delay="<?= ($idx+1)*100 ?>">
            <div class="insight-dot" style="background:<?= $insightColors[$idx % count($insightColors)] ?>"></div>
            <span><?= clean($insight) ?></span>
          </div>
          <?php endforeach; ?>
          <?php if (empty($insights)): ?>
          <div class="insight-item"><div class="insight-dot" style="background:var(--muted)"></div><span style="color:var(--muted)">No insights available.</span></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- PERSONALITY -->
      <div class="card dash-personality" data-aos="fade-up" data-aos-delay="160" style="text-align:center;">
        <div class="card-header">
          <div class="card-icon">🧬</div>
          <div class="card-title">Financial Personality</div>
        </div>
        <div class="personality-icon"><?= $pIcon ?></div>
        <div class="personality-title"><?= clean($personality) ?></div>
        <?php if ($pDesc): ?>
        <div class="personality-desc"><?= clean($pDesc) ?></div>
        <?php endif; ?>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
          <a href="roadmap.php" class="btn btn-primary btn-full">
            View Your Plans →
          </a>
        </div>
      </div>

      <!-- STATS -->
      <div class="card dash-stats" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header">
          <div class="card-icon">📊</div>
          <div class="card-title">Financial Stats</div>
        </div>
        <?php if (!empty($stats)): ?>
        <div class="stat-bar-wrap">
          <?php foreach ($statBars as $bar):
            $val = min(100, max(0, (int)($stats[$bar['key']] ?? 0)));
          ?>
          <div class="stat-bar">
            <div class="stat-bar-header">
              <span style="font-size:0.85rem;font-weight:500;"><?= $bar['label'] ?></span>
              <span style="font-size:0.85rem;color:var(--muted);" id="stat_<?= $bar['key'] ?>">0%</span>
            </div>
            <div class="stat-bar-track">
              <div class="stat-bar-fill"
                   id="fill_<?= $bar['key'] ?>"
                   style="width:0%;background:<?= $bar['color'] ?>;"
                   data-target="<?= $val ?>"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="color:var(--muted);font-size:0.88rem;">Stats not available for this analysis.</div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div style="margin-top:24px;display:flex;gap:10px;flex-wrap:wrap;">
          <a href="roadmap.php" class="btn btn-primary btn-sm">📊 View Plans</a>
          <a href="chat.php" class="btn btn-secondary btn-sm">💬 Ask AI</a>
          <a href="profile.php" class="btn btn-outline btn-sm">✏️ Edit Profile</a>
        </div>
      </div>

    </div><!-- /.dash-grid -->
  </div><!-- /.page-wrap-wide -->

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({ once: true, duration: 600, easing: 'ease-out-cubic' });

    const targetScore = <?= $score ?>;
    const circumference = <?= $circumference ?>;

    // Animate score ring
    let current = 0;
    const ring = document.getElementById('ringFill');
    const numEl = document.getElementById('scoreNum');
    const step = targetScore / 60;

    const scoreAnim = setInterval(() => {
      current = Math.min(current + step, targetScore);
      const offset = circumference - (circumference * current / 100);
      ring.style.strokeDashoffset = offset;
      numEl.textContent = Math.round(current);
      if (current >= targetScore) clearInterval(scoreAnim);
    }, 25);

    // Animate stat bars
    setTimeout(() => {
      document.querySelectorAll('.stat-bar-fill').forEach(fill => {
        const target = parseInt(fill.dataset.target);
        const key    = fill.id.replace('fill_', '');
        const label  = document.getElementById('stat_' + key);
        fill.style.width = target + '%';
        if (label) {
          let c = 0;
          const si = setInterval(() => {
            c = Math.min(c + 2, target);
            label.textContent = c + '%';
            if (c >= target) clearInterval(si);
          }, 20);
        }
      });
    }, 500);
  </script>
</body>
</html>
