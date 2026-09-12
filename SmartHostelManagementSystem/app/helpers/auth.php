<?php
/**
 * Authentication helper functions
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUserRole() {
    return $_SESSION['role'] ?? null;
}

function currentUserFullName() {
    return $_SESSION['full_name'] ?? null;
}

function currentUserEmail() {
    return $_SESSION['email'] ?? null;
}

function hasRole($role) {
    return currentUserRole() === $role;
}

function isStudent() {
    return hasRole(ROLE_STUDENT);
}

function isWarden() {
    return hasRole(ROLE_WARDEN);
}

function isAdmin() {
    return hasRole(ROLE_ADMIN);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
        redirect('/login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        http_response_code(403);
        die('Access denied. You do not have permission to view this page.');
    }
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
}

function logoutUser() {
    session_destroy();
}

function sidebarBadgeCount($key, $count) {
    $seen = $_SESSION['sidebar_seen'][$key] ?? 0;
    return max(0, (int) $count - (int) $seen);
}

function markSidebarSeen($key, $count) {
    $_SESSION['sidebar_seen'][$key] = (int) $count;
}

function getRedirectUrl() {
    $role = currentUserRole();
    $urls = [
        ROLE_STUDENT => '/student/dashboard',
        ROLE_WARDEN => '/warden/dashboard',
        ROLE_ADMIN => '/admin/dashboard'
    ];
    return $urls[$role] ?? '/login.php';
}
?>