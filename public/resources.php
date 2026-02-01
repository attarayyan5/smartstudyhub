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

// handle new resource
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$subject_id = (int)($_POST['subject_id'] ?? 0);
	$title = trim($_POST['title'] ?? '');
	$type = $_POST['type'] ?? 'youtube';
	$url = trim($_POST['url'] ?? '');
	$content = trim($_POST['content'] ?? '');

	if ($subject_id && $title !== '') {
		// if type is note, url is optional; if link types, url is required
		if (($type === 'youtube' || $type === 'article') && $url === '') {
			$message = 'Please provide a URL for YouTube or Article.';
		} else {
			$stmt = $pdo->prepare("
				INSERT INTO resources (user_id, subject_id, title, type, url, content, created_at)
				VALUES (?,?,?,?,?,?,NOW())
			");
			$stmt->execute([$user_id, $subject_id, $title, $type, $url, $content]);
			$message = 'Resource added.';
		}
	}
}

// delete resource
if (isset($_GET['delete'])) {
	$id = (int)$_GET['delete'];
	$stmt = $pdo->prepare("DELETE FROM resources WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
	$message = 'Resource deleted.';
}

// filter by subject (optional)
$filter_subject = (int)($_GET['subject_id'] ?? 0);

if ($filter_subject) {
	$stmt = $pdo->prepare("
		SELECT r.*, s.name AS subject_name
		FROM resources r
		JOIN subjects s ON r.subject_id = s.id
		WHERE r.user_id = ? AND r.subject_id = ?
		ORDER BY r.created_at DESC
	");
	$stmt->execute([$user_id, $filter_subject]);
} else {
	$stmt = $pdo->prepare("
		SELECT r.*, s.name AS subject_name
		FROM resources r
		JOIN subjects s ON r.subject_id = s.id
		WHERE r.user_id = ?
		ORDER BY r.created_at DESC
	");
	$stmt->execute([$user_id]);
}
$resources = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Learning Resources</h1>

<?php if ($message): ?>
	<div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<h2>Add New Resource</h2>
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
		<label>Type</label>
		<select name="type" id="res-type" onchange="toggleResourceFields()">
			<option value="youtube">YouTube Video</option>
			<option value="article">Article / Blog</option>
			<option value="note">Note (stored text)</option>
		</select>
	</div>
	<div class="form-group" id="url-group">
		<label>URL (YouTube / Article)</label>
		<input type="text" name="url" placeholder="https://...">
	</div>
	<div class="form-group" id="content-group">
		<label>Note Content (for type = Note)</label>
		<textarea name="content" placeholder="Write your note here..."></textarea>
	</div>
	<button type="submit">Add Resource</button>
</form>

<script>
function toggleResourceFields() {
	const typeSelect = document.getElementById('res-type');
	const urlGroup = document.getElementById('url-group');
	const contentGroup = document.getElementById('content-group');

	if (typeSelect.value === 'note') {
		urlGroup.style.display = 'none';
		contentGroup.style.display = 'block';
	} else {
		urlGroup.style.display = 'block';
		contentGroup.style.display = 'none';
	}
}
toggleResourceFields();
</script>

<h2 style="margin-top:25px;">Your Resources</h2>

<form method="get" style="margin-bottom:10px;">
	<div class="form-group">
		<label>Filter by Subject (optional)</label>
		<select name="subject_id" onchange="this.form.submit()">
			<option value="0">All subjects</option>
			<?php foreach ($subjects as $sub): ?>
				<option value="<?php echo $sub['id']; ?>"
					<?php if ($filter_subject == $sub['id']) echo 'selected'; ?>>
					<?php echo htmlspecialchars($sub['name']); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</form>

<table class="table">
	<tr>
		<th>Title</th>
		<th>Subject</th>
		<th>Type</th>
		<th>Details</th>
		<th>Actions</th>
	</tr>
	<?php foreach ($resources as $r): ?>
	<tr>
		<td><?php echo htmlspecialchars($r['title']); ?></td>
		<td><?php echo htmlspecialchars($r['subject_name']); ?></td>
		<td><?php echo htmlspecialchars(ucfirst($r['type'])); ?></td>
		<td>
			<?php if ($r['type'] === 'note'): ?>
				<div style="max-width:300px; white-space:pre-wrap; font-size:13px;">
					<?php echo nl2br(htmlspecialchars($r['content'])); ?>
				</div>
			<?php else: ?>
				<?php if ($r['url']): ?>
					<a href="<?php echo htmlspecialchars($r['url']); ?>" target="_blank">Open Link</a>
				<?php endif; ?>
			<?php endif; ?>
		</td>
		<td>
			<a href="?delete=<?php echo $r['id']; ?>" onclick="return confirm('Delete this resource?');">Delete</a>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>