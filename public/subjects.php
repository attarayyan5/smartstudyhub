<?php
require '../includes/db.php';
require '../includes/auth.php';

checkLogin();
$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$target = (int)($_POST['target_hours_per_week'] ?? 0);

	if ($name !== '') {
		$stmt = $pdo->prepare("
			INSERT INTO subjects (user_id, name, description, target_hours_per_week, created_at)
			VALUES (?,?,?,?,NOW())
		");
		$stmt->execute([$user_id, $name, $description, $target]);
		$message = 'Subject added.';
	}
}

if (isset($_GET['delete'])) {
	$id = (int)$_GET['delete'];
	$stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ? AND user_id = ?");
	$stmt->execute([$id, $user_id]);
	$message = 'Subject deleted.';
}

$stmt = $pdo->prepare("SELECT * FROM subjects WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$subjects = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Subjects</h1>

<?php if ($message): ?>
	<div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h2>Add New Subject</h2>
<form method="post">
	<div class="form-group">
		<label>Name</label>
		<input type="text" name="name" required>
	</div>
	<div class="form-group">
		<label>Description</label>
		<textarea name="description"></textarea>
	</div>
	<div class="form-group">
		<label>Target hours per week</label>
		<input type="number" name="target_hours_per_week" min="0" value="0">
	</div>
	<button type="submit">Add Subject</button>
</form>

<h2 style="margin-top:25px;">Your Subjects</h2>
<table class="table">
	<tr>
		<th>Name</th>
		<th>Target hrs/week</th>
		<th>Actions</th>
	</tr>
	<?php foreach ($subjects as $sub): ?>
	<tr>
		<td><?php echo htmlspecialchars($sub['name']); ?></td>
		<td><?php echo (int)$sub['target_hours_per_week']; ?></td>
		<td>
			<a href="?delete=<?php echo $sub['id']; ?>" onclick="return confirm('Delete subject?');">Delete</a>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>