<?php
/**
 * Global Configuration
 * Business Management System — Phase 1 + 2 + 3
 */

// ---- Error reporting (turn off display_errors in production) ----
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---- Session ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Database credentials ----
// Configured for the confirmed local environment: XAMPP on Windows,
// Apache 2.4.58 + PHP 8.2.12, MariaDB 10.4.32 listening on port 3307
// (moved off the default 3306, a common XAMPP conflict when another
// MySQL/MariaDB service is already using that port). If you move this
// project to a different machine, update DB_PORT to match that
// environment's MySQL/MariaDB port (3306 is the standard default).
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'bms_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- Site info ----
define('SITE_NAME', 'BMS | Business Management System');
define('SITE_SHORT_NAME', 'BMS');
define('SITE_URL', 'http://localhost/http://localhost/BusinessFlow-Business-Management-System/');

// ---- Base path so links work the same whether a page lives in the
//      project root (index.php) or in a sub-folder (dashboard/index.php) ----
// This resolves the *project root's* web path once, based on the real
// filesystem location of this config file, so every include can safely
// build links like BASE_PATH . '/assets/css/style.css'.
$projectRoot = dirname(__DIR__); // filesystem path to project root
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$normalizedRoot = str_replace('\\', '/', $projectRoot);

if ($docRoot !== '' && stripos($normalizedRoot, $docRoot) === 0) {
    // Windows filesystem paths are case-insensitive (e.g. PHP may report
    // "C:/xampp/htdocs" while Apache's DOCUMENT_ROOT reports
    // "c:/xampp/htdocs") — stripos() avoids a false-negative match here
    // that would otherwise silently break BASE_PATH (and therefore every
    // asset/link on the site) on a Windows/XAMPP setup.
    $basePath = substr($normalizedRoot, strlen($docRoot));
} else {
    // Fallback: assume config.php is always one level inside the project root.
    $basePath = '';
}
define('BASE_PATH', rtrim($basePath, '/'));
unset($projectRoot, $docRoot, $normalizedRoot, $basePath);

// ---- Developer / footer contact info ----
// TODO: Replace these placeholders with the real details you provide.
# define('DEV_NAME', 'Rana Umar Draz');
// TODO: Replace these with your real profile URLs and contact email.
define('DEV_NAME', 'Rana Umar Draz');
define('DEV_LINKEDIN', 'https://www.linkedin.com/in/rana-umar-draz-95951733b/');
define('DEV_INSTAGRAM', 'https://www.instagram.com/___umar_009/');
define('DEV_GITHUB', 'https://github.com/ranaumar9276-umar');
define('DEV_EMAIL', 'ranaumar9276@gmail.com');

// ---- Autoload OOP classes ----
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

// ---- Phase 3: silent "Remember Me" auto-login ----
// If the person isn't logged in this session but has a valid remember-me
// cookie from a previous visit, log them back in transparently.
if (!Auth::isLoggedIn() && !empty($_COOKIE['bms_remember'])) {
    (new Auth())->attemptAutoLogin();
}
