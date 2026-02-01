<?php
require '../includes/db.php';
require '../includes/auth.php';

checkLogin();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

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

<h1>Profile</h1>

<p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
<p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
<p><strong>Joined:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>

<h2 style="margin-top:25px;">Badges</h2>
<?php if (!$badges): ?>
	<p>No badges yet.</p>
<?php else: ?>
	<ul>
		<?php foreach ($badges as $b): ?>
			<li>
				<strong><?php echo htmlspecialchars($b['name']); ?></strong>
				(<?php echo htmlspecialchars($b['earned_at']); ?>)<br>
				<span style="font-size:13px;color:#555;"><?php echo htmlspecialchars($b['description']); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>