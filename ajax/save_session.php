<?php
session_start();
// require '../includes/db.php';
require '../includes/stats.php';

if (!isset($_SESSION['user_id'])) {
	http_response_code(403);
	echo "Not logged in";
	exit;
}

$user_id = $_SESSION['user_id'];
$subject_id = (int)($_POST['subject_id'] ?? 0);
$minutes = (int)($_POST['minutes'] ?? 0);

if ($subject_id <= 0 || $minutes <= 0) {
	http_response_code(400);
	echo "Invalid data";
	exit;
}

// verify subject belongs to user
$stmt = $pdo->prepare("SELECT id FROM subjects WHERE id = ? AND user_id = ?");
$stmt->execute([$subject_id, $user_id]);
if (!$stmt->fetch()) {
	http_response_code(403);
	echo "Subject not found";
	exit;
}

// insert session
$stmt = $pdo->prepare("
	INSERT INTO study_sessions (user_id, subject_id, date, duration_minutes, created_at)
	VALUES (?,?,CURDATE(),?,NOW())
");
$stmt->execute([$user_id, $subject_id, $minutes]);

// check & award badges
checkAndAwardBadges($pdo, $user_id);

echo "OK";