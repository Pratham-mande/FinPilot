<?php
require_once 'config.php';
header('Content-Type: application/json');
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}
$uid      = (int)$_SESSION['user_id'];
$planName = clean($_POST['plan'] ?? '');
if (!$planName) {
    echo json_encode(['success' => false, 'error' => 'No plan specified']);
    exit;
}
$db   = getDB();
$stmt = $db->prepare("UPDATE ai_results SET selected_plan = ? WHERE user_id = ?");
$stmt->bind_param('si', $planName, $uid);
$ok   = $stmt->execute();
echo json_encode([
    'success'  => $ok,
    'plan'     => $planName,
    'message'  => $ok ? "You selected the $planName!" : 'Update failed',
]);
