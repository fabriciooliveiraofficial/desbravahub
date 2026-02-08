<?php
/**
 * Auth Service
 * 
 * Handles authentication, sessions, and password management.
 */

namespace App\Services;

use App\Core\App;

class AuthService
{
    private const TOKEN_LENGTH = 64;
    private const SESSION_LIFETIME_HOURS = 24;

    /**
     * Attempt login with credentials
     * 
     * @return array|null User data if successful, null if failed
     */
    public function attempt(string $email, string $password, int $tenantId): ?array
    {
        $user = db_fetch_one(
            "SELECT u.*, r.name as role_name 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.email = ? AND u.tenant_id = ? AND u.status = 'active' AND u.deleted_at IS NULL",
            [$email, $tenantId]
        );

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        // Update last login
        db_update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        return $user;
    }

    /**
     * Create a new session for user
     */
    public function createSession(int $userId): string
    {
        // Generate secure token
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH / 2));
        $tokenHash = hash('sha256', $token);

        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::SESSION_LIFETIME_HOURS . ' hours'));

        db_insert('user_sessions', [
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    /**
     * Validate session token and return user
     */
    public function validateSession(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);

        $session = db_fetch_one(
            "SELECT s.*, u.*, r.name as role_name
             FROM user_sessions s
             JOIN users u ON s.user_id = u.id
             JOIN roles r ON u.role_id = r.id
             WHERE s.token_hash = ? AND s.expires_at > NOW() AND u.status = 'active'",
            [$tokenHash]
        );

        if (!$session) {
            return null;
        }

        // Remove session fields from user data
        unset($session['token_hash'], $session['expires_at']);

        return $session;
    }

    /**
     * Destroy a session
     */
    public function destroySession(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        db_delete('user_sessions', 'token_hash = ?', [$tokenHash]);
    }

    /**
     * Destroy all sessions for a user
     */
    public function destroyAllSessions(int $userId): void
    {
        db_delete('user_sessions', 'user_id = ?', [$userId]);
    }

    /**
     * Hash a password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Get session token from request
     */
    public function getTokenFromRequest(): ?string
    {
        // Check cookie first
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }

        // Check Authorization header
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Set auth cookie
     */
    public function setAuthCookie(string $token): void
    {
        $expires = time() + (self::SESSION_LIFETIME_HOURS * 3600);
        
        // Only use Secure cookies if on HTTPS AND not in dev/local environment
        // Browsers block Secure cookies on http://localhost exception, but it's safer to be explicit
        $secure = is_https() && !is_dev();

        setcookie('auth_token', $token, [
            'expires' => $expires,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Clear auth cookie
     */
    public function clearAuthCookie(): void
    {
        setcookie('auth_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
        ]);
    }
}
