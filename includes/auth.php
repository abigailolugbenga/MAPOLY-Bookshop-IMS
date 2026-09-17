<?php
/**
 * Session + role-based access control helpers.
 * Include this at the TOP of every protected page, before any HTML output.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Stop the page unless someone is logged in. */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /mapoly_bookshop/auth/login.php');
        exit;
    }
}

/** Stop the page unless the logged-in user has the given role. */
function require_role(string $role): void
{
    require_login();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        die('Access denied: this page is restricted to ' . htmlspecialchars($role) . ' accounts.');
    }
}
