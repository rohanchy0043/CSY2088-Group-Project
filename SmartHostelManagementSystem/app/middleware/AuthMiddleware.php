<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../models/User.php';

class AuthMiddleware {
    public static function handle() {
        requireLogin();
        $user = User::find(currentUserId());
        if (!$user || ($user['account_status'] ?? 'approved') !== 'approved') {
            logoutUser();
            redirect('/login.php');
            exit;
        }
        return true;
    }
}