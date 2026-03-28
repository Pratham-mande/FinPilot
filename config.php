<?php
// ─── DB CONFIG ─────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'finpath_ai');

// ─── GEMINI API CONFIG ─────────────────────────────────────
define('AI_API_KEY', 'REMOVED_API_KEY');

define('AI_API_URL', 'https://api.groq.com/openai/v1/chat/completions');

define('AI_MODEL', 'llama-3.1-8b-instant');
// ─── SESSION ───────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── DB CONNECT ────────────────────────────────────────────
function getDB(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('DB Error: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// ─── AUTH ──────────────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUser(): array {
    if (!isLoggedIn()) return [];
    $db = getDB();
    $id = (int)$_SESSION['user_id'];
    $res = $db->query("SELECT id, name, email FROM users WHERE id=$id LIMIT 1");
    return $res ? ($res->fetch_assoc() ?: []) : [];
}

// ─── SANITIZE ──────────────────────────────────────────────
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

// ─── GEMINI AI CALL (FIXED) ────────────────────────────────
function callAI(string $prompt): ?array {

    $payload = json_encode([
        "model" => AI_MODEL,
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => 0.7,
        "max_tokens" => 1500
    ]);

    $ch = curl_init(AI_API_URL);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AI_API_KEY
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);

    curl_close($ch);

    if ($err || !$response) return null;

    $decoded = json_decode($response, true);

    $text = $decoded['choices'][0]['message']['content'] ?? null;

    if (!$text) return null;

    // Clean JSON
    $text = preg_replace('/^```json\s*/i', '', trim($text));
    $text = preg_replace('/```$/', '', $text);

    preg_match('/\{.*\}/s', $text, $matches);
    $json = $matches[0] ?? '';

    return json_decode($json, true);
}
// ─── PROMPT BUILDER ───────────────────────────────────────
function buildFinancialPrompt(array $fd, array $resp): string {

    $q_labels = [
        'Emergency Fund',
        'Monthly Investing',
        'Expense Tracking',
        'Insurance',
        'Long-term Investment'
    ];

    $behavior = "";
    for ($i = 1; $i <= 5; $i++) {
        $behavior .= $q_labels[$i-1] . ": " . ($resp["q$i"] ?? 'Not Sure') . "\n";
    }

    return "
You are an expert financial advisor for Indian users. Return ONLY valid JSON.

User:
Age: {$fd['age']}
Income: {$fd['income']}
Expenses: {$fd['expenses']}
Savings: {$fd['savings']}
Debt: {$fd['debt']}
Risk: {$fd['risk']}

Behavior:
$behavior

Return:
{
  \"score\": number,
  \"scoreLabel\": \"string\",
  \"insights\": [\"3 points\"],
  \"personality\": \"string\",
  \"personalityIcon\": \"emoji\",
  \"personalityDesc\": \"string\",
  \"stats\": {
    \"savingsRate\": number,
    \"debtRatio\": number,
    \"expenseRatio\": number,
    \"investmentScore\": number
  },
  \"plans\": [
    {
      \"name\": \"Safe Plan\",
      \"steps\": [\"3 steps\"]
    },
    {
      \"name\": \"Balanced Plan\",
      \"steps\": [\"3 steps\"]
    },
    {
      \"name\": \"Growth Plan\",
      \"steps\": [\"3 steps\"]
    },
    {
      \"name\": \"Goal Plan\",
      \"steps\": [\"3 steps\"]
    }
  ]
}";
}