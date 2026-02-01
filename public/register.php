<?php
require '../includes/db.php';
require '../includes/auth.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$confirm = $_POST['confirm_password'] ?? '';

	if ($name === '' || $email === '' || $password === '' || $confirm === '') {
		$error = 'Please fill all fields.';
	} elseif ($password !== $confirm) {
		$error = 'Passwords do not match.';
	} else {
		try {
			$hash = password_hash($password, PASSWORD_DEFAULT);
			$stmt = $pdo->prepare("INSERT INTO users (name,email,password,created_at) VALUES (?,?,?,NOW())");
			$stmt->execute([$name, $email, $hash]);
			header("Location: index.php?registered=1");
			exit;
		} catch (PDOException $e) {
			if ($e->getCode() === '23000') {
				$error = 'Email already exists.';
			} else {
				$error = 'Error: ' . $e->getMessage();
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Register - SmartStudy Hub</title>
	<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
	<div class="auth-wrapper">
		<h1>Register</h1>
		<?php if ($error): ?>
			<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>
		<form method="post">
			<div class="form-group">
				<label>Name</label>
				<input type="text" name="name" required>
			</div>
			<div class="form-group">
				<label>Email</label>
				<input type="email" name="email" required>
			</div>
			<div class="form-group">
				<label>Password</label>
				<input type="password" name="password" required>
			</div>
			<div class="form-group">
				<label>Confirm Password</label>
				<input type="password" name="confirm_password" required>
			</div>
			<button type="submit">Register</button>
		</form>
		<p style="margin-top:10px;font-size:14px;">
			Already have an account? <a href="index.php">Login</a>
		</p>
	</div>
</body>
</html>