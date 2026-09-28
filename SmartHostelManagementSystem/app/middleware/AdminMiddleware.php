<?php
require_once __DIR__ . '/AuthMiddleware.php';

class AdminMiddleware {
    public static function handle() {
        AuthMiddleware::handle();
        if (!isAdmin()) {
            http_response_code(403);
            die('Access denied. Admin area only.');
        }
        return true;
    }
}