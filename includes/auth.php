<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function checkLogin() {
	if (!isset($_SESSION['user_id'])) {
		header("Location: index.php");
		exit;
	}
}

function redirectIfLoggedIn() {
	if (isset($_SESSION['user_id'])) {
		header("Location: dashboard.php");
		exit;
	}
}