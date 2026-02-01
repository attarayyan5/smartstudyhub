<?php
require '../includes/db.php';
require '../includes/auth.php';
require '../includes/stats.php';

checkLogin();

$user_id = $_SESSION['user_id'];

// total subjects
$stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM subjects WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalSubjects = $stmt->fetch()['c'] ?? 0;

// pending tasks
$stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM tasks WHERE user_id = ? AND status != 'Completed'");
$stmt->execute([$user_id]);
$pendingTasks = $stmt->fetch()['c'] ?? 0;

// today study minutes
$stmt = $pdo->prepare("SELECT COALESCE(SUM(duration_minutes),0) AS m FROM study_sessions WHERE user_id = ? AND date = CURDATE()");
$stmt->execute([$user_id]);
$todayMinutes = $stmt->fetch()['m'] ?? 0;

// streak + totals
$currentStreak = getCurrentStreak($pdo, $user_id);
$totalMinutes  = getTotalMinutes($pdo, $user_id);
$totalSessions = getTotalSessions($pdo, $user_id);

// last 7 days chart data
$stmt = $pdo->prepare("
	SELECT date, SUM(duration_minutes) AS mins
	FROM study_sessions
	WHERE user_id = ?
	GROUP BY date
	ORDER BY date DESC
	LIMIT 7
");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll();
$chartData = array_reverse($rows);

// badges
$stmt = $pdo->prepare("
	SELECT b.name, b.description, ub.earned_at
	FROM user_badges ub
	JOIN badges b ON ub.badge_id = b.id
	WHERE ub.user_id = ?
	ORDER BY ub.earned_at DESC
");
$stmt->execute([$user_id]);
$badges = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>

<div class="flex">
	<div class="card">
		<h3>Total Subjects</h3>
		<p><?php echo (int)$totalSubjects; ?></p>
	</div>
	<div class="card">
		<h3>Pending Tasks</h3>
		<p><?php echo (int)$pendingTasks; ?></p>
	</div>
	<div class="card">
		<h3>Today's Study Time</h3>
		<p><?php echo (int)$todayMinutes; ?> minutes</p>
	</div>
</div>

<div class="flex" style="margin-top:20px;">
	<div class="card">
		<h3>Current Streak</h3>
		<p><?php echo (int)$currentStreak; ?> day(s) in a row</p>
		<p style="font-size:13px;color:#555;">
			Study every day to increase your streak.
		</p>
	</div>
	<div class="card">
		<h3>Overall Study</h3>
		<p><?php echo (int)$totalMinutes; ?> total minutes</p>
		<p><?php echo (int)$totalSessions; ?> sessions</p>
	</div>
</div>

<h2 style="margin-top:30px;">Last 7 Days Study Time</h2>
<canvas id="studyChart" width="600" height="250" style="border:1px solid #eee;"></canvas>

<script>
const data = <?php echo json_encode($chartData); ?>;

const canvas = document.getElementById('studyChart');
const ctx = canvas.getContext('2d');

const padding = 30;
const width = canvas.width - padding * 2;
const height = canvas.height - padding * 2;

const maxVal = data.length ? Math.max(...data.map(d => parseInt(d.mins))) : 0;
const labels = data.map(d => d.date);
const values = data.map(d => parseInt(d.mins));

ctx.clearRect(0, 0, canvas.width, canvas.height);
ctx.translate(padding, padding);

// axes
ctx.beginPath();
ctx.moveTo(0, 0);
ctx.lineTo(0, height);
ctx.lineTo(width, height);
ctx.strokeStyle = '#aaa';
ctx.stroke();

if (!data.length || maxVal === 0) {
	ctx.fillStyle = '#555';
	ctx.fillText('No data yet', 10, 20);
	ctx.setTransform(1, 0, 0, 1, 0, 0);
} else {
	const stepX = width / (data.length - 1 || 1);
	const scaleY = height / maxVal;

	ctx.beginPath();
	values.forEach((v, i) => {
		const x = stepX * i;
		const y = height - v * scaleY;
		if (i === 0) ctx.moveTo(x, y);
		else ctx.lineTo(x, y);
	});
	ctx.strokeStyle = '#3949ab';
	ctx.lineWidth = 2;
	ctx.stroke();

	// points
	ctx.fillStyle = '#3949ab';
	values.forEach((v, i) => {
		const x = stepX * i;
		const y = height - v * scaleY;
		ctx.beginPath();
		ctx.arc(x, y, 3, 0, Math.PI * 2);
		ctx.fill();
	});

	// labels
	ctx.fillStyle = '#333';
	ctx.font = '10px sans-serif';
	labels.forEach((lbl, i) => {
		const x = stepX * i;
		ctx.fillText(lbl, x - 10, height + 12);
	});

	ctx.setTransform(1, 0, 0, 1, 0, 0);
}
</script>

<h2 style="margin-top:30px;">Your Badges</h2>
<?php if (!$badges): ?>
	<p>You have no badges yet. Start studying to earn some!</p>
<?php else: ?>
	<ul>
		<?php foreach ($badges as $b): ?>
			<li>
				<strong><?php echo htmlspecialchars($b['name']); ?></strong>
				(earned at <?php echo htmlspecialchars($b['earned_at']); ?>)<br>
				<span style="font-size:13px;color:#555;"><?php echo htmlspecialchars($b['description']); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>