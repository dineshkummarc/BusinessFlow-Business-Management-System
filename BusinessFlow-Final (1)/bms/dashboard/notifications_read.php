<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
    $notificationModel = new Notification();
    $notificationModel->markAllRead((int) $_SESSION['user_id']);
}

$redirect = $_POST['redirect'] ?? 'index.php';
// Only allow redirecting back within this app, never to an external host.
if (!preg_match('#^/#', $redirect) || strpos($redirect, '//') === 0) {
    $redirect = BASE_PATH . '/dashboard/index.php';
}
header('Location: ' . $redirect);
exit;
