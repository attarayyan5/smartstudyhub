<?php
require '../includes/db.php';
require '../includes/auth.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';

	if ($email === '' || $password === '') {
		$error = 'Please enter email and password.';
	} else {
		$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
		$stmt->execute([$email]);
		$user = $stmt->fetch();

		if ($user && password_verify($password, $user['password'])) {
			session_start();
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['user_name'] = $user['name'];
			header("Location: dashboard.php");
			exit;
		} else {
			$error = 'Invalid email or password.';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Login - SmartStudy Hub</title>
	<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
	<div class="auth-wrapper">
		<h1>Login</h1>
		<?php if (isset($_GET['registered'])): ?>
			<div class="alert alert-success">Registration successful. Please log in.</div>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>
		<form method="post">
			<div class="form-group">
				<label>Email</label>
				<input type="email" name="email" required>
			</div>
			<div class="form-group">
				<label>Password</label>
				<input type="password" name="password" required>
			</div>
			<button type="submit">Login</button>
		</form>
		<p style="margin-top:10px;font-size:14px;">
			No account? <a href="register.php">Register</a>
		</p>
	</div>
</body>
</html>