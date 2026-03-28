<?php
require_once 'config.php';
requireLogin();

$user = currentUser();
$uid  = (int)$_SESSION['user_id'];
$db   = getDB();

$aiRes = $db->query("SELECT * FROM ai_results WHERE user_id = $uid ORDER BY updated_at DESC LIMIT 1")->fetch_assoc();
if (!$aiRes) { header('Location: profile.php'); exit; }

$plans       = json_decode($aiRes['plans'] ?? '[]', true) ?: [];
$selectedPlan = $aiRes['selected_plan'] ?? null;
$score        = (int)($aiRes['score'] ?? 0);
$color        = $score;

// Handle plan selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_plan'])) {
    $planName = clean($_POST['select_plan']);
    $stmt = $db->prepare("UPDATE ai_results SET selected_plan=? WHERE user_id=?");
    $stmt->bind_param('si', $planName, $uid);
    $stmt->execute();
    $selectedPlan = $planName;
}

$planBadgeColors = [
    'safe'     => ['color'=>'#34d399','bg'=>'rgba(52,211,153,0.12)'],
    'balanced' => ['color'=>'#38bdf8','bg'=>'rgba(56,189,248,0.12)'],
    'growth'   => ['color'=>'#818cf8','bg'=>'rgba(129,140,248,0.12)'],
    'goal'     => ['color'=>'#fbbf24','bg'=>'rgba(251,191,36,0.12)'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Your Roadmap — FinPilot AI</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
</head>
<body>
  <div class="grid-bg"></div>

  <nav>
    <a href="index.html" class="nav-logo">⚡ FinPilot</a>
    <div class="nav-links">
      <a href="dashboard.php">Dashboard</a>
      <a href="roadmap.php" style="color:var(--accent)">Plans</a>
      <a href="chat.php">AI Chat</a>
      <a href="profile.php">Edit Profile</a>
      <a href="logout.php">Logout</a>
    </div>
  </nav>

  <div class="page-wrap-wide" style="position:relative;z-index:1;">
    <div class="page-header" data-aos="fade-up">
      <h1>🗺️ Your Financial Roadmaps</h1>
      <p>4 AI-generated plans tailored to your profile. Choose the one that fits your goals.</p>
    </div>

    <?php if ($selectedPlan): ?>
    <div class="alert alert-ok" data-aos="fade-in" style="margin-bottom:24px;">
      🏆 You've selected the <strong><?= clean($selectedPlan) ?></strong>!
      Scroll down to see your plan details, or <a href="chat.php" style="color:var(--accent3)">ask the AI</a> for more guidance.
    </div>
    <?php endif; ?>

    <!-- Score Summary -->
    <div class="card mb-24" data-aos="fade-up" style="background:linear-gradient(135deg,rgba(56,189,248,0.05),rgba(129,140,248,0.05));">
      <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        <div style="font-size:3rem;font-weight:800;font-family:var(--font-head);color:<?= $color ?>"><?= $score ?><span style="font-size:1.2rem;color:var(--muted)">/100</span></div>
        <div>
          <div style="font-family:var(--font-head);font-weight:700;font-size:1.1rem;margin-bottom:4px;">
            <?= scoreLabel($score) ?> Financial Health
          </div>
          <div style="color:var(--muted);font-size:0.87rem;">
            <?= clean($aiRes['personality'] ?? 'Balanced Planner') ?> — Choose a plan below that matches your ambitions
          </div>
        </div>
        <div style="margin-left:auto;display:flex;gap:10px;flex-wrap:wrap;">
          <a href="dashboard.php" class="btn btn-secondary btn-sm">📊 Dashboard</a>
          <a href="chat.php" class="btn btn-outline btn-sm">💬 Ask AI</a>
        </div>
      </div>
    </div>

    <!-- Plan Cards Grid -->
    <?php if (!empty($plans)): ?>
    <div class="plan-grid">
      <?php foreach ($plans as $idx => $plan):
        $type   = $plan['type'] ?? 'balanced';
        $bc     = $planBadgeColors[$type] ?? $planBadgeColors['balanced'];
        $isSelected = $selectedPlan && strtolower($selectedPlan) === strtolower($plan['name'] ?? '');
      ?>
      <div class="plan-card <?= $type ?> <?= $isSelected ? 'selected' : '' ?>"
           data-aos="fade-up" data-aos-delay="<?= $idx * 80 ?>">

        <div>
          <div class="plan-badge" style="background:<?= $bc['bg'] ?>;color:<?= $bc['color'] ?>">
            <?= clean($plan['badge'] ?? ucfirst($type).' Plan') ?>
          </div>
        </div>

        <div>
          <div class="plan-name"><?= clean($plan['name'] ?? 'Plan') ?></div>
          <div class="plan-desc"><?= clean($plan['description'] ?? '') ?></div>
        </div>

        <div>
          <div style="font-size:0.78rem;color:var(--muted);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:10px;">
            Action Steps
          </div>
          <div class="plan-steps">
            <?php foreach ($plan['steps'] ?? [] as $si => $step): ?>
            <div class="plan-step">
              <div class="step-num"><?= $si+1 ?></div>
              <span><?= clean($step) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="margin-top:auto;">
          <?php if ($isSelected): ?>
          <div class="btn btn-success btn-full" style="cursor:default;">
            ✅ Currently Selected
          </div>
          <?php else: ?>
          <form method="POST" action="" data-ajax-plan>
            <input type="hidden" name="select_plan" value="<?= clean($plan['name'] ?? '') ?>" />
            <button type="submit" class="btn btn-outline btn-full">
              Choose This Plan →
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="card" style="text-align:center;padding:60px;">
      <div style="font-size:3rem;margin-bottom:16px;">🤖</div>
      <div style="font-family:var(--font-head);font-weight:700;margin-bottom:8px;">No Plans Generated</div>
      <div style="color:var(--muted);margin-bottom:24px;">The AI analysis did not return plans. This may be an API issue.</div>
      <a href="processing.php" class="btn btn-primary">🔄 Re-run Analysis</a>
    </div>
    <?php endif; ?>

    <!-- Plan Comparison Table -->
    <?php if (count($plans) >= 2): ?>
    <div class="card mt-32" data-aos="fade-up">
      <div class="card-header">
        <div class="card-icon">📊</div>
        <div class="card-title">Plan Comparison</div>
      </div>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:0.86rem;">
          <thead>
            <tr style="border-bottom:1px solid var(--border);">
              <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600;">Feature</th>
              <?php foreach ($plans as $plan): ?>
              <th style="text-align:center;padding:10px 12px;color:var(--text);font-family:var(--font-head);">
                <?= clean($plan['name'] ?? 'Plan') ?>
              </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php
            $comparisons = [
              ['Risk Level',     ['🟢 Low','🟡 Medium','🔴 High','🎯 Varies']],
              ['Time Horizon',   ['5+ Years','3–5 Years','5–10 Years','Goal Based']],
              ['Liquidity',      ['High','Medium','Low','Mixed']],
              ['Expected Return',['6–8% p.a.','10–14% p.a.','14–18% p.a.','Varies']],
            ];
            foreach ($comparisons as [$label, $vals]):
            ?>
            <tr style="border-bottom:1px solid rgba(99,179,237,0.06);">
              <td style="padding:10px 12px;color:var(--muted);"><?= $label ?></td>
              <?php foreach ($vals as $v): ?>
              <td style="padding:10px 12px;text-align:center;"><?= $v ?></td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- CTA -->
    <div style="text-align:center;margin-top:40px;" data-aos="fade-up">
      <a href="chat.php" class="btn btn-primary btn-lg">
        💬 Ask AI About Your Plan
      </a>
    </div>

  </div>

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script src="js/utils.js"></script>
  <script>
    AOS.init({ once: true, duration: 600, easing: 'ease-out-cubic' });

    // AJAX plan selection — no full page reload
    document.querySelectorAll('form[data-ajax-plan]').forEach(form => {
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const planName = this.querySelector('[name="select_plan"]').value;
        const btn      = this.querySelector('button[type="submit"]');
        btn.textContent = '⏳ Saving...';
        btn.disabled    = true;

        try {
          const res  = await fetch('select_plan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'plan=' + encodeURIComponent(planName),
          });
          const data = await res.json();
          if (data.success) {
            // Update all plan card states
            document.querySelectorAll('.plan-card').forEach(c => {
              c.classList.remove('selected');
              const f = c.querySelector('form[data-ajax-plan]');
              if (f) {
                const b = f.querySelector('button');
                b.textContent = 'Choose This Plan →';
                b.disabled = false;
                b.className = 'btn btn-outline btn-full';
              }
            });
            // Mark this card
            const thisCard = this.closest('.plan-card');
            thisCard.classList.add('selected');
            btn.textContent = '✅ Currently Selected';
            btn.disabled    = true;
            btn.className   = 'btn btn-success btn-full';
            showToast('🏆 ' + data.message);
          } else {
            btn.textContent = 'Choose This Plan →';
            btn.disabled    = false;
            showToast('❌ Could not save selection', 'error');
          }
        } catch (err) {
          btn.textContent = 'Choose This Plan →';
          btn.disabled    = false;
          showToast('❌ Network error', 'error');
        }
      });
    });
  </script>
</body>
</html>
