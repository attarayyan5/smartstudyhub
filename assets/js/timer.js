let startTime = null;
let timerInterval = null;

function startTimer() {
	if (timerInterval !== null) return;
	startTime = new Date();
	timerInterval = setInterval(updateDisplay, 1000);
}

function stopTimer(subjectId) {
	if (timerInterval === null) return;

	clearInterval(timerInterval);
	timerInterval = null;
	const endTime = new Date();
	const diffMs = endTime - startTime;
	const minutes = Math.max(1, Math.round(diffMs / 60000)); // at least 1 minute

	const params = new URLSearchParams();
	params.append('subject_id', subjectId);
	params.append('minutes', minutes);

	fetch('../ajax/save_session.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: params.toString()
	})
	.then(res => res.text())
	.then(text => {
		alert('Study session saved: ' + minutes + ' minutes');
		location.reload();
	})
	.catch(err => {
		alert('Error saving session');
		console.error(err);
	});
}

function updateDisplay() {
	if (!startTime) return;
	const now = new Date();
	const seconds = Math.floor((now - startTime) / 1000);
	const mins = Math.floor(seconds / 60);
	const secs = seconds % 60;
	const display = document.getElementById('timer-display');
	if (display) {
		display.textContent = mins + 'm ' + secs + 's';
	}
}