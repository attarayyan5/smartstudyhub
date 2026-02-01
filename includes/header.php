<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>SmartStudy Hub</title>
	<link rel="stylesheet" href="../assets/css/style.css">
	<script src="../assets/js/main.js" defer></script>
</head>
<body>
	<header class="topbar">
		<div class="logo">SmartStudy Hub</div>
		<?php if (isset($_SESSION['user_id'])): ?>
			<nav class="nav">
	<a href="dashboard.php">Dashboard</a>
	<a href="subjects.php">Subjects</a>
	<a href="tasks.php">Tasks</a>
	<a href="study_sessions.php">Study Sessions</a>
	<a href="analytics.php">Analytics</a>
	<a href="resources.php">Resources</a>   <!-- NEW -->
	<a href="profile.php">Profile</a>
	<a href="logout.php">Logout</a>
</nav>
		<?php endif; ?>
	</header>
	<main class="container">