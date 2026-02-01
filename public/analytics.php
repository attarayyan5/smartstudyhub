<?php
require '../includes/db.php';
require '../includes/auth.php';

checkLogin();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
	SELECT s.id, s.name,
		COALESCE(SUM(ss.duration_minutes),0) AS total_minutes,
		(SELECT COUNT(*) FROM tasks t WHERE t.user_id = s.user_id AND t.subject_id = s.id) AS total_tasks,
		(SELECT COUNT(*) FROM tasks t WHERE t.user_id = s.user_id AND t.subject_id = s.id AND t.status = 'Completed') AS completed_tasks
	FROM subjects s
	LEFT JOIN study_sessions ss ON ss.subject_id = s.id AND ss.user_id = s.user_id
	WHERE s.user_id = ?
	GROUP BY s.id, s.name
	ORDER BY s.name
");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Analytics</h1>

<table class="table">
	<tr>
		<th>Subject</th>
		<th>Total Study Time (minutes)</th>
		<th>Tasks (Completed / Total)</th>
		<th>Completion %</th>
	</tr>
	<?php foreach ($rows as $r): 
		$total = (int)$r['total_tasks'];
		$comp = (int)$r['completed_tasks'];
		$percent = $total ? round($comp * 100 / $total) : 0;
	?>
	<tr>
		<td><?php echo htmlspecialchars($r['name']); ?></td>
		<td><?php echo (int)$r['total_minutes']; ?></td>
		<td><?php echo $comp . ' / ' . $total; ?></td>
		<td><?php echo $percent; ?>%</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>