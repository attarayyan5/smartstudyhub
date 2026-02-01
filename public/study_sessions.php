<?php
require '../includes/db.php';
require '../includes/auth.php';

checkLogin();
$user_id = $_SESSION['user_id'];

// subjects
$stmt = $pdo->prepare("SELECT id, name FROM subjects WHERE user_id = ? ORDER BY name");
$stmt->execute([$user_id]);
$subjects = $stmt->fetchAll();

// sessions
$stmt = $pdo->prepare("
	SELECT ss.*, s.name AS subject_name
	FROM study_sessions ss
	JOIN subjects s ON ss.subject_id = s.id
	WHERE ss.user_id = ?
	ORDER BY ss.date DESC, ss.created_at DESC
	LIMIT 50
");
$stmt->execute([$user_id]);
$sessions = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Study Sessions</h1>

<h2>Start a Timed Session</h2>
<p>Select a subject, then click "Start" and "Stop" when finished.</p>

<div class="form-group">
	<label>Subject</label>
	<select id="timer-subject">
		<?php foreach ($subjects as $sub): ?>
			<option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['name']); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<div class="form-group">
	<span id="timer-display">0m 0s</span>
</div>
<div class="form-group">
	<button type="button" onclick="startTimer()">Start</button>
	<button type="button" onclick="stopTimer(document.getElementById('timer-subject').value)">Stop & Save</button>
</div>

<script src="../assets/js/timer.js"></script>

<h2 style="margin-top:25px;">Recent Sessions</h2>
<table class="table">
	<tr>
		<th>Date</th>
		<th>Subject</th>
		<th>Duration (minutes)</th>
	</tr>
	<?php foreach ($sessions as $sess): ?>
	<tr>
		<td><?php echo htmlspecialchars($sess['date']); ?></td>
		<td><?php echo htmlspecialchars($sess['subject_name']); ?></td>
		<td><?php echo (int)$sess['duration_minutes']; ?></td>
	</tr>
	<?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>