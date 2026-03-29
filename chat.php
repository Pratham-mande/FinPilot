<?php
require_once 'config.php';
requireLogin();

$user = currentUser();
$uid  = (int)$_SESSION['user_id'];
$db   = getDB();

// Get user's financial context
$fd     = $db->query("SELECT * FROM financial_data WHERE user_id = $uid LIMIT 1")->fetch_assoc();
$aiRes  = $db->query("SELECT * FROM ai_results WHERE user_id = $uid ORDER BY updated_at DESC LIMIT 1")->fetch_assoc();
$score  = (int)($aiRes['score'] ?? 0);
$selectedPlan = $aiRes['selected_plan'] ?? 'Not chosen yet';
$goals  = json_decode($fd['goals'] ?? '[]', true) ?: [];
$goalsStr = implode(', ', $goals) ?: 'Not specified';

// Handle AJAX chat request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    header('Content-Type: application/json');
    $userMsg = clean($_POST['message'] ?? '');
    if (!$userMsg) { echo json_encode(['error' => 'Empty message']); exit; }

    // Save user message
    $stmt = $db->prepare("INSERT INTO chat_history (user_id, role, message) VALUES (?, 'user', ?)");
    $stmt->bind_param('is', $uid, $userMsg);
    $stmt->execute();

    // Build context prompt
    $context = "You are FinPilot, an expert AI financial advisor for Indian users. " .
        "User Context: Monthly Income ₹" . ($fd['income']??'unknown') .
        ", Expenses ₹" . ($fd['expenses']??'unknown') .
        ", Savings ₹" . ($fd['savings']??'unknown') .
        ", Debt ₹" . ($fd['debt']??'unknown') .
        ", Risk: " . ($fd['risk']??'Medium') .
        ", Goals: $goalsStr" .
        ", Money Health Score: $score/100" .
        ", Selected Plan: $selectedPlan. " .
        "Answer questions about personal finance, investments, savings, tax-saving, debt management. " .
        "Keep answers concise, practical, and relevant to Indian financial context (mention ₹, PPF, ELSS, NPS, etc. where relevant). " .
        "User asks: $userMsg";

    // For chat, bypass JSON parsing — call API directly
    $payload = json_encode([
        'model'    => AI_MODEL,
        'messages' => [['role' => 'user', 'content' => $context]],
        'max_tokens' => 600,
        'temperature' => 0.7,
    ]);
    $ch = curl_init(AI_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json','Authorization: Bearer '.AI_API_KEY],
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    $dec = json_decode($raw, true);
    $aiText = $dec['choices'][0]['message']['content'] ?? 'Sorry, I could not process your request. Please check your API key.';

    // Save AI reply
    $stmt2 = $db->prepare("INSERT INTO chat_history (user_id, role, message) VALUES (?, 'assistant', ?)");
    $stmt2->bind_param('is', $uid, $aiText);
    $stmt2->execute();

    echo json_encode(['reply' => $aiText]);
    exit;
}

// Load chat history
$history = [];
$hRes = $db->query("SELECT role, message, created_at FROM chat_history WHERE user_id = $uid ORDER BY created_at ASC LIMIT 50");
if ($hRes) { while ($row = $hRes->fetch_assoc()) $history[] = $row; }

$suggestions = [
    "How can I improve my credit score?",
    "What is the best way to save for retirement in India?",
    "Explain SIP vs lump sum investments",
    "How much emergency fund should I have?",
    "Which tax-saving investments should I choose?",
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AI Chat — FinPilot AI</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
</head>
<body>
  <div class="grid-bg"></div>

  <nav>
    <a href="index.html" class="nav-logo">⚡ FinPilot</a>
    <div class="nav-links">
      <a href="dashboard.php">Dashboard</a>
      <a href="chat.php" style="color:var(--accent)">AI Chat</a>
      <a href="profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </div>
  </nav>

  <div style="max-width:880px;margin:0 auto;padding:24px 20px;position:relative;z-index:1;">

    <div class="page-header" data-aos="fade-up">
      <h1>💬 AI Financial Advisor</h1>
      <p>Ask anything about your finances, investments, tax, or your personalized plan.</p>
    </div>

    <!-- Context Bar -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;" data-aos="fade-up" data-aos-delay="80">
      <div class="feat-chip">🎯 Score: <strong style="color:var(--accent);margin-left:4px;"><?= $score ?>/100</strong></div>
      <div class="feat-chip">📋 Plan: <strong style="margin-left:4px;"><?= clean($selectedPlan) ?></strong></div>
      <div class="feat-chip">🎯 Goals: <strong style="margin-left:4px;"><?= clean($goalsStr) ?></strong></div>
    </div>

    <!-- Chat Container -->
    <div class="card" style="padding:0;overflow:hidden;" data-aos="fade-up" data-aos-delay="160">
      <div class="chat-messages" id="chatMessages">

        <!-- Welcome message -->
        <div class="chat-bubble ai">
          <div class="bubble-avatar ai">🤖</div>
          <div class="bubble-text">
            Namaste <?= clean($user['name'] ?? 'there') ?>! 👋<br><br>
            I'm your AI Financial Advisor. I know your complete financial profile including your
            <strong style="color:var(--accent)"><?= $score ?>/100 money health score</strong> and selected plan.<br><br>
            Ask me anything — SIP returns, tax saving, debt strategies, investment options, or specific steps for your <?= clean($selectedPlan) ?>.
          </div>
        </div>

        <?php foreach ($history as $h): ?>
        <div class="chat-bubble <?= $h['role'] === 'user' ? 'user' : 'ai' ?>">
          <div class="bubble-avatar <?= $h['role'] === 'user' ? 'user' : 'ai' ?>">
            <?= $h['role'] === 'user' ? '👤' : '🤖' ?>
          </div>
          <div class="bubble-text" style="white-space:pre-wrap;"><?= nl2br(clean($h['message'])) ?></div>
        </div>
        <?php endforeach; ?>

        <!-- Typing indicator (hidden) -->
        <div class="chat-bubble ai hidden" id="typingIndicator">
          <div class="bubble-avatar ai">🤖</div>
          <div class="bubble-text">
            <div style="display:flex;gap:4px;align-items:center;">
              <div style="width:7px;height:7px;border-radius:50%;background:var(--muted);animation:typingDot .8s ease-in-out infinite;"></div>
              <div style="width:7px;height:7px;border-radius:50%;background:var(--muted);animation:typingDot .8s ease-in-out infinite;animation-delay:.15s;"></div>
              <div style="width:7px;height:7px;border-radius:50%;background:var(--muted);animation:typingDot .8s ease-in-out infinite;animation-delay:.3s;"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Suggestions -->
      <div id="suggestionsBar" style="padding:12px 16px;border-top:1px solid var(--border);<?= !empty($history) ? 'display:none;' : '' ?>">
        <div style="font-size:0.75rem;color:var(--muted);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Quick Questions</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <?php foreach ($suggestions as $sug): ?>
          <button class="suggestion-btn"
                  onclick="sendSuggestion(this.dataset.msg)"
                  data-msg="<?= htmlspecialchars($sug) ?>"
                  style="padding:6px 12px;border-radius:20px;border:1px solid var(--border);background:var(--bg3);color:var(--muted);cursor:pointer;font-size:0.8rem;font-family:var(--font-body);transition:all .2s;"
                  onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                  onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
            <?= htmlspecialchars($sug) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Input -->
      <div class="chat-input-bar">
        <textarea class="chat-input" id="chatInput"
                  placeholder="Ask about investments, tax saving, debt, SIPs..."
                  rows="1"
                  onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"></textarea>
        <button onclick="sendMessage()" class="btn btn-primary btn-sm" id="sendBtn">
          Send →
        </button>
      </div>
    </div>

    <p style="text-align:center;color:var(--muted);font-size:0.78rem;margin-top:12px;">
      ⚠️ FinPilot AI provides general guidance only. Not SEBI registered. Consult a certified advisor for formal advice.
    </p>
  </div>

  <style>
    @keyframes typingDot {
      0%, 60%, 100% { transform: translateY(0); opacity: .4; }
      30% { transform: translateY(-4px); opacity: 1; }
    }
  </style>

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({ once:true, duration:500 });

    const chatMessages = document.getElementById('chatMessages');
    const chatInput    = document.getElementById('chatInput');
    const sendBtn      = document.getElementById('sendBtn');
    const typing       = document.getElementById('typingIndicator');
    const sugBar       = document.getElementById('suggestionsBar');

    function scrollToBottom() {
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    function addBubble(role, text) {
      const div = document.createElement('div');
      div.className = 'chat-bubble ' + role;
      div.innerHTML = `
        <div class="bubble-avatar ${role}">${role === 'user' ? '👤' : '🤖'}</div>
        <div class="bubble-text" style="white-space:pre-wrap;">${text.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
      `;
      chatMessages.insertBefore(div, typing);
      scrollToBottom();
    }

    async function sendMessage() {
      const msg = chatInput.value.trim();
      if (!msg) return;

      addBubble('user', msg);
      chatInput.value = '';
      sendBtn.disabled = true;
      sendBtn.textContent = '...';
      typing.classList.remove('hidden');
      sugBar.style.display = 'none';
      scrollToBottom();

      try {
        const res  = await fetch('chat.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'message=' + encodeURIComponent(msg),
        });
        const data = await res.json();
        typing.classList.add('hidden');
        addBubble('ai', data.reply || 'Sorry, no response received.');
      } catch (e) {
        typing.classList.add('hidden');
        addBubble('ai', '⚠️ Connection error. Please try again.');
      } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send →';
        chatInput.focus();
      }
    }

    function sendSuggestion(msg) {
      chatInput.value = msg;
      sendMessage();
    }

    // Auto-resize textarea
    chatInput.addEventListener('input', function() {
      this.style.height = 'auto';
      this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
  </script>
</body>
</html>
