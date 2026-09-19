<?php
/**
 * Redirect helper functions
 */

function redirect($url, $statusCode = 302) {
    header("Location: $url", true, $statusCode);
    exit;
}

function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    redirect($referer);
}

function redirectWith($url, $key, $value) {
    $_SESSION[$key] = $value;
    redirect($url);
}

function redirectWithError($url, $message) {
    redirectWith($url, 'error', $message);
}

function redirectWithSuccess($url, $message) {
    redirectWith($url, 'success', $message);
}

function redirectWithErrors($url, $errors) {
    $_SESSION['errors'] = $errors;
    redirect($url);
}

function redirectWithOld($url, $data) {
    $_SESSION['old'] = $data;
    redirect($url);
}
?>