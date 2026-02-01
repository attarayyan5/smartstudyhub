<?php
require '../includes/db.php';
require '../includes/auth.php';

checkLogin();
$user_id = $_SESSION['user_id'];
$message = '';

// load subjects
$stmt = $pdo->prepare("SELECT id, name FROM subjects WHERE user_id = ? ORDER BY name");
$stmt->execute([$user_id]);
$subjects = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$subject_id = (int)($_POST['subject_id'] ?? 0);
	$title = trim($_POST['title'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$due_date = $_POST['due_date'] ?? null;
	$priority = $_POST['priority'] ?? 'Medium';

	if ($subject_id && $title !== '') {
		$stmt = $pdo->prepare("
			INSERT INTO tasks (user_id, subject_id, title, description, due_date, priority, status, created_at)
			VALUES (?,?,?,?,?,?, 'Pending', NOW())
		");
		$stmt->execute([$user_id, $subject_id, $title, $description, $due_date, $priority]);
		$message = 'Task added.';
	}
}

if (isset($_GET['complete'])) {
	$id = (int)$_GET['complete'];
	$stmt = $pdo->prepare("UPDATE tasks SET status = 'Completed' WHERE id = ? AND user_id = ?");
	$stmt->execute([$id, $user_id]);
	$message = 'Task marked as Completed.';
}

$stmt = $pdo->prepare("
	SELECT t.*, s.name AS subject_name
	FROM tasks t
	JOIN subjects s ON t.subject_id = s.id
	WHERE t.user_id = ?
	ORDER BY t.due_date IS NULL, t.due_date, t.created_at DESC
");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Tasks</h1>

<?php if ($message): ?>
	<div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h2>Add New Task</h2>
<form method="post">
	<div class="form-group">
		<label>Subject</label>
		<select name="subject_id" required>
			<option value="">Select subject</option>
			<?php foreach ($subjects as $sub): ?>
				<option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['name']); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="form-group">
		<label>Title</label>
		<input type="text" name="title" required>
	</div>
	<div class="form-group">
		<label>Description</label>
		<textarea name="description"></textarea>
	</div>
	<div class="form-group">
		<label>Due Date</label>
		<input type="date" name="due_date">
	</div>
	<div class="form-group">
		<label>Priority</label>
		<select name="priority">
			<option>Low</option>
			<option selected>Medium</option>
			<option>High</option>
		</select>
	</div>
	<button type="submit">Add Task</button>
</form>

<h2 style="margin-top:25px;">Your Tasks</h2>
<table class="table">
	<tr>
		<th>Title</th>
		<th>Subject</th>
		<th>Due</th>
		<th>Priority</th>
		<th>Status</th>
		<th>Actions</th>
	</tr>
	<?php foreach ($tasks as $task): ?>
	<tr>
		<td><?php echo htmlspecialchars($task['title']); ?></td>
		<td><?php echo htmlspecialchars($task['subject_name']); ?></td>
		<td><?php echo htmlspecialchars($task['due_date']); ?></td>
		<td><?php echo htmlspecialchars($task['priority']); ?></td>
		<td><?php echo htmlspecialchars($task['status']); ?></td>
		<td>
			<?php if ($task['status'] !== 'Completed'): ?>
				<a href="?complete=<?php echo $task['id']; ?>">Mark Completed</a>
			<?php else: ?>
				—
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>