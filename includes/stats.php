<?php
if (!isset($pdo)) {
	// require __DIR__ . '/db.php';
}

/**
 * Get current streak (consecutive days with any study).
 */
function getCurrentStreak(PDO $pdo, int $user_id): int {
	$stmt = $pdo->prepare("
		SELECT DISTINCT date
		FROM study_sessions
		WHERE user_id = ?
		ORDER BY date DESC
		LIMIT 30
	");
	$stmt->execute([$user_id]);
	$dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

	if (!$dates) return 0;

	$today = new DateTime();
	$todayStr = $today->format('Y-m-d');

	$streak = 0;
	$expected = new DateTime(); // start from today

	foreach ($dates as $d) {
		$dateObj = new DateTime($d);
		if ($dateObj->format('Y-m-d') === $expected->format('Y-m-d')) {
			$streak++;
			$expected->modify('-1 day');
		} else {
			// allow starting from yesterday if nothing today
			if ($streak === 0 && $dateObj->format('Y-m-d') === (new DateTime('yesterday'))->format('Y-m-d')) {
				$streak++;
				$expected = new DateTime('2 days ago');
			} else {
				break;
			}
		}
	}
	return $streak;
}

/**
 * Get total minutes studied.
 */
function getTotalMinutes(PDO $pdo, int $user_id): int {
	$stmt = $pdo->prepare("SELECT COALESCE(SUM(duration_minutes),0) FROM study_sessions WHERE user_id = ?");
	$stmt->execute([$user_id]);
	return (int)$stmt->fetchColumn();
}

/**
 * Get total number of sessions.
 */
function getTotalSessions(PDO $pdo, int $user_id): int {
	$stmt = $pdo->prepare("SELECT COUNT(*) FROM study_sessions WHERE user_id = ?");
	$stmt->execute([$user_id]);
	return (int)$stmt->fetchColumn();
}

/**
 * Award a badge to a user if not already owned.
 */
function awardBadge(PDO $pdo, int $user_id, string $slug): void {
	$stmt = $pdo->prepare("SELECT id FROM badges WHERE slug = ?");
	$stmt->execute([$slug]);
	$badge = $stmt->fetch();
	if (!$badge) return;

	$badge_id = (int)$badge['id'];

	$stmt = $pdo->prepare("SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?");
	$stmt->execute([$user_id, $badge_id]);
	if ($stmt->fetch()) return;

	$stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id, earned_at) VALUES (?,?,NOW())");
	$stmt->execute([$user_id, $badge_id]);
}

/**
 * Check & award all badges based on current stats.
 */
function checkAndAwardBadges(PDO $pdo, int $user_id): void {
	$totalMinutes = getTotalMinutes($pdo, $user_id);
	$totalSessions = getTotalSessions($pdo, $user_id);
	$streak = getCurrentStreak($pdo, $user_id);

	if ($totalSessions >= 1) {
		awardBadge($pdo, $user_id, 'first_session');
	}
	if ($totalSessions >= 5) {
		awardBadge($pdo, $user_id, '5_sessions');
	}
	if ($totalSessions >= 10) {
		awardBadge($pdo, $user_id, '10_sessions');
	}
	if ($totalMinutes >= 60) {
		awardBadge($pdo, $user_id, '1_hour_total');
	}
	if ($streak >= 7) {
		awardBadge($pdo, $user_id, '7_day_streak');
	}
}