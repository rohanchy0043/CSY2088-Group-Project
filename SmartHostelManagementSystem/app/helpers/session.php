<?php
/**
 * Session helper functions
 */

function sessionSet($key, $value) {
    $_SESSION[$key] = $value;
}

function sessionGet($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

function sessionHas($key) {
    return isset($_SESSION[$key]);
}

function sessionFlash($key) {
    $value = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $value;
}

function sessionClear() {
    session_destroy();
}

function sessionOld($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function sessionErrors() {
    return sessionFlash('errors') ?? [];
}

function sessionHasErrors() {
    return !empty($_SESSION['errors']);
}

function sessionSuccess() {
    return sessionFlash('success');
}

function sessionError() {
    return sessionFlash('error');
}

function withOldData($data) {
    $_SESSION['old'] = $data;
}

function old($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}
?>