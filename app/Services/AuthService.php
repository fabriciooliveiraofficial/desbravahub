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
    private const SESSION_LIFETIME_HOURS = 8760; // 1 year

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
     * Attempt login as Super Admin
     * Ignores tenant limitations
     */
    public function attemptSuperAdmin(string $email, string $password): ?array
    {
        $user = db_fetch_one(
            "SELECT u.*, r.name as role_name 
             FROM users u 
             LEFT JOIN roles r ON u.role_id = r.id 
             WHERE u.email = ? AND u.is_superadmin = 1 AND u.status = 'active' AND u.deleted_at IS NULL",
            [$email]
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
            "SELECT s.*, u.*, r.name as role_name, units.name as unit_name
             FROM user_sessions s
             JOIN users u ON s.user_id = u.id
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN units ON u.unit_id = units.id
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
     * Refresh a session's expiration time (Rolling Session).
     * Throttled to once per hour via PHP session to avoid a DB UPDATE on every request.
     * With SESSION_LIFETIME_HOURS = 8760 (1 year), refreshing more often is pure waste.
     */
    public function refreshSession(string $token, string $cookiePath = '/', ?string $slug = null): void
    {
        // Use a short key derived from the token to avoid exposing the raw token in the session.
        $sessionKey = 'auth_refreshed_' . substr(hash('sha256', $token), 0, 12);

        // Skip the DB UPDATE if we already refreshed within the last hour.
        if (!empty($_SESSION[$sessionKey]) && (time() - $_SESSION[$sessionKey]) < 3600) {
            return;
        }

        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::SESSION_LIFETIME_HOURS . ' hours'));

        db_update('user_sessions', ['expires_at' => $expiresAt], 'token_hash = ?', [$tokenHash]);

        // Re-issue cookie to extend its lifetime in the browser
        $this->setAuthCookie($token, $cookiePath, $slug);

        // Record the refresh timestamp so subsequent requests skip the DB hit
        $_SESSION[$sessionKey] = time();
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
     * 
     * Priority: Authorization header (per-tab) > tenant cookie > legacy cookie
     * 
     * @param string|null $slug Optional tenant slug to look for specific cookie
     */
    public function getTokenFromRequest(?string $slug = null): ?string
    {
        // 1. Authorization header FIRST (per-tab session via sessionStorage + HTMX)
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/', $header, $matches)) {
            return $matches[1];
        }

        // 2. Tenant-specific cookie (fallback for full page loads)
        if ($slug) {
            $name = 'auth_token_' . $slug;
            if (isset($_COOKIE[$name])) {
                return $_COOKIE[$name];
            }
        }

        // 3. Legacy global cookie
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }

        return null;
    }

    /**
     * Set auth cookie
     * 
     * @param string $token Session token
     * @param string $cookiePath Cookie path scope (e.g. '/tenant-slug/' for tenant isolation)
     * @param string|null $slug Optional tenant slug for unique cookie name
     */
    public function setAuthCookie(string $token, string $cookiePath = '/', ?string $slug = null): void
    {
        $expires = time() + (self::SESSION_LIFETIME_HOURS * 3600);
        
        // Only use Secure cookies if on HTTPS AND not in dev/local environment
        $secure = is_https() && !is_dev();
        
        // Use unique name per tenant if slug provided
        $cookieName = $slug ? 'auth_token_' . $slug : 'auth_token';

        setcookie($cookieName, $token, [
            'expires' => $expires,
            'path' => $cookiePath,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Clear auth cookie
     * 
     * @param string $cookiePath Cookie path scope
     * @param string|null $slug Optional tenant slug for unique cookie name
     */
    public function clearAuthCookie(string $cookiePath = '/', ?string $slug = null): void
    {
        // Use unique name per tenant if slug provided
        $cookieName = $slug ? 'auth_token_' . $slug : 'auth_token';

        setcookie($cookieName, '', [
            'expires' => time() - 3600,
            'path' => $cookiePath,
        ]);
    }
}
