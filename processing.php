<?php
require_once 'config.php';
requireLogin();

$uid = (int)$_SESSION['user_id'];
$db  = getDB();

// Fetch profile & responses
$fdRes = $db->query("SELECT * FROM financial_data WHERE user_id = $uid LIMIT 1");
$fd    = $fdRes ? $fdRes->fetch_assoc() : null;
$brRes = $db->query("SELECT * FROM behavioral_responses WHERE user_id = $uid LIMIT 1");
$br    = $brRes ? $brRes->fetch_assoc() : null;

if (!$fd || !$br) { header('Location: profile.php'); exit; }
$fd['goals'] = json_decode($fd['goals'] ?? '[]', true) ?: [];

// Call AI
$prompt  = buildFinancialPrompt($fd, $br);
$aiResult = callAI($prompt);

$aiJson  = json_encode($aiResult);
$success = $aiResult !== null;

if ($success) {
    $score    = $aiResult['score'] ?? 0;
    $rawStore = addslashes(json_encode($aiResult));

    // Upsert ai_results
    $existingAi = $db->query("SELECT id FROM ai_results WHERE user_id = $uid LIMIT 1")->fetch_assoc();
    $insightsJ  = addslashes(json_encode($aiResult['insights'] ?? []));
    $plansJ     = addslashes(json_encode($aiResult['plans'] ?? []));
    $statsJ     = addslashes(json_encode($aiResult['stats'] ?? []));
    $personality = addslashes($aiResult['personality'] ?? '');
    $pIcon       = addslashes($aiResult['personalityIcon'] ?? '🧠');
    $pDesc       = addslashes($aiResult['personalityDesc'] ?? '');
    $sLabel      = addslashes($aiResult['scoreLabel'] ?? scoreLabel($score));

    if ($existingAi) {
        $db->query("UPDATE ai_results SET score=$score, score_label='$sLabel',
            insights='$insightsJ', personality='$personality', personality_icon='$pIcon',
            personality_desc='$pDesc', plans='$plansJ', stats='$statsJ',
            raw_response='$rawStore' WHERE user_id=$uid");
    } else {
        $db->query("INSERT INTO ai_results (user_id,score,score_label,insights,personality,personality_icon,personality_desc,plans,stats,raw_response)
            VALUES ($uid,$score,'$sLabel','$insightsJ','$personality','$pIcon','$pDesc','$plansJ','$statsJ','$rawStore')");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AI Analysis — FinPilot AI</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="grid-bg"></div>

  <?php if ($success): ?>
  <!-- SUCCESS — animate then redirect -->
  <div class="processing-wrap">
    <div class="ai-orb">
      <div class="orb-ring2"></div>
      <div class="orb-ring"></div>
      <div class="orb-inner"></div>
    </div>

    <div style="text-align:center;">
      <div style="font-family:var(--font-head);font-size:1.4rem;font-weight:800;margin-bottom:8px;">
        ⚡ AI Analysis Complete
      </div>
      <div style="color:var(--muted);font-size:0.9rem;">Preparing your personalized dashboard...</div>
    </div>

    <div class="processing-steps" id="steps">
      <div class="proc-step" id="s1">
        <span class="step-icon">📊</span>
        <span>Analyzing financial profile...</span>
        <div class="proc-spinner"></div>
      </div>
      <div class="proc-step" id="s2">
        <span class="step-icon">🧠</span>
        <span>Calculating money health score...</span>
        <div class="proc-spinner"></div>
      </div>
      <div class="proc-step" id="s3">
        <span class="step-icon">🗺️</span>
        <span>Generating personalized roadmaps...</span>
        <div class="proc-spinner"></div>
      </div>
      <div class="proc-step" id="s4">
        <span class="step-icon">✅</span>
        <span>Plans ready! Redirecting...</span>
        <span class="proc-check">✓</span>
      </div>
    </div>

    <div style="font-size:0.82rem;color:var(--muted);">
      Score: <strong style="color:var(--accent)"><?= $aiResult['score'] ?? 'N/A' ?>/100</strong> •
      <?= clean($aiResult['personality'] ?? 'Analyzing...') ?>
    </div>
  </div>

  <script>
    const steps = [
      {id: 's1', delay: 400},
      {id: 's2', delay: 900},
      {id: 's3', delay: 1500},
      {id: 's4', delay: 2200},
    ];
    steps.forEach(s => {
      setTimeout(() => {
        const el = document.getElementById(s.id);
        el.classList.add('active');
      }, s.delay);
    });
    // Redirect after animation
    setTimeout(() => { window.location.href = 'dashboard.php'; }, 3400);
  </script>

  <?php else: ?>
  <!-- AI CALL FAILED -->
  <div class="processing-wrap">
    <div style="font-size:4rem;">⚠️</div>
    <div style="text-align:center;">
      <div style="font-family:var(--font-head);font-size:1.3rem;font-weight:800;margin-bottom:8px;color:var(--danger);">
        AI Analysis Failed
      </div>
      <div style="color:var(--muted);font-size:0.9rem;max-width:400px;line-height:1.6;">
        Could not connect to the AI service. This usually means the API key is not configured.
        Please check your <code>config.php</code> file.
      </div>
    </div>
    <div style="display:flex;gap:12px;">
      <a href="questions.php" class="btn btn-secondary">← Try Again</a>
      <a href="profile.php" class="btn btn-outline">Edit Profile</a>
    </div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;max-width:500px;font-size:0.8rem;color:var(--muted);font-family:monospace;">
      <strong>Debug:</strong> Set AI_API_KEY in config.php<br>
      API URL: <?= AI_API_URL ?>
    </div>
  </div>
  <?php endif; ?>
</body>
</html>
