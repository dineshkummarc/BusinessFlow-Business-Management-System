<?php
/**
 * Auth
 * Central authentication service: login, register, logout, session guards.
 */
class Auth
{
    private User $userModel;
    private ActivityLog $log;

    public function __construct()
    {
        $this->userModel = new User();
        $this->log = new ActivityLog();
    }

    public function attemptLogin(string $loginId, string $password, bool $remember = false): array
    {
        $user = $this->userModel->findByLogin($loginId);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid username/email or password.'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'This account is not active. Contact an administrator.'];
        }

        $this->startSession($user);
        $this->userModel->updateLastLogin((int) $user['id']);
        $this->log->record((int) $user['id'], 'auth', 'User logged in');

        if ($remember) {
            $this->issueRememberToken((int) $user['id']);
        }

        return ['success' => true, 'message' => 'Login successful.'];
    }

    public function register(array $data): array
    {
        if ($this->userModel->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'An account with this email already exists.'];
        }
        if ($this->userModel->usernameExists($data['username'])) {
            return ['success' => false, 'message' => 'This username is already taken.'];
        }

        $id = $this->userModel->create($data);
        $user = $this->userModel->findById($id);
        $this->startSession($user);
        $this->log->record($id, 'auth', 'New account registered');

        return ['success' => true, 'message' => 'Account created successfully.'];
    }

    private function startSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['full_name']  = $user['full_name'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['logged_in']  = true;
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_PATH . '/login.php');
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array($_SESSION['role'], $roles, true)) {
            http_response_code(403);
            die('403 — You do not have permission to access this page.');
        }
    }

    public function logout(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->log->record((int) $_SESSION['user_id'], 'auth', 'User logged out');
        }
        $this->clearRememberToken();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    // ---------------------------------------------------------------
    // Phase 3 — "Remember Me" persistent login
    //
    // A random token is generated on login (if the box is checked) and
    // its hash stored in `auth_tokens`. The raw token lives only in an
    // httponly browser cookie as "<user_id>:<rawToken>". On a future
    // visit with no active session, attemptAutoLogin() looks the token
    // up, verifies it, logs the user back in, and rotates the token.
    // ---------------------------------------------------------------

    private const REMEMBER_COOKIE = 'bms_remember';
    private const REMEMBER_DAYS = 30;

    public function issueRememberToken(int $userId): void
    {
        $raw = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);
        $expiresAt = date('Y-m-d H:i:s', time() + self::REMEMBER_DAYS * 86400);

        $db = Database::getInstance();
        $db->execute('INSERT INTO auth_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)', [$userId, $hash, $expiresAt]);

        $this->setRememberCookie($userId, $raw);
    }

    private function setRememberCookie(int $userId, string $rawToken): void
    {
        $value = $userId . ':' . $rawToken;
        $expire = time() + self::REMEMBER_DAYS * 86400;
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie(self::REMEMBER_COOKIE, $value, [
            'expires'  => $expire,
            'path'     => BASE_PATH !== '' ? BASE_PATH . '/' : '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function clearRememberToken(): void
    {
        if (empty($_COOKIE[self::REMEMBER_COOKIE])) {
            return;
        }
        [$userId, $raw] = array_pad(explode(':', $_COOKIE[self::REMEMBER_COOKIE], 2), 2, '');
        if ($userId !== '' && $raw !== '') {
            $hash = hash('sha256', $raw);
            Database::getInstance()->execute(
                'DELETE FROM auth_tokens WHERE user_id = ? AND token_hash = ?',
                [(int) $userId, $hash]
            );
        }
        setcookie(self::REMEMBER_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => BASE_PATH !== '' ? BASE_PATH . '/' : '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Called on every request (see config.php). If the person isn't
     * logged in but has a valid remember-me cookie, log them back in
     * silently and rotate the token for security.
     */
    public function attemptAutoLogin(): bool
    {
        if (self::isLoggedIn() || empty($_COOKIE[self::REMEMBER_COOKIE])) {
            return false;
        }

        [$userId, $raw] = array_pad(explode(':', $_COOKIE[self::REMEMBER_COOKIE], 2), 2, '');
        if ($userId === '' || $raw === '') {
            return false;
        }

        $db = Database::getInstance();
        $hash = hash('sha256', $raw);
        $row = $db->selectOne(
            'SELECT * FROM auth_tokens WHERE user_id = ? AND token_hash = ? AND expires_at > NOW()',
            [(int) $userId, $hash]
        );
        if (!$row) {
            $this->clearRememberToken();
            return false;
        }

        $user = $this->userModel->findById((int) $userId);
        if (!$user || $user['status'] !== 'active') {
            $this->clearRememberToken();
            return false;
        }

        // Rotate: remove the old token, issue a fresh one.
        $db->execute('DELETE FROM auth_tokens WHERE id = ?', [$row['id']]);
        $this->startSession($user);
        $this->userModel->updateLastLogin((int) $user['id']);
        $this->issueRememberToken((int) $user['id']);
        $this->log->record((int) $user['id'], 'auth', 'Logged in automatically via "remember me"');

        return true;
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
