<?php
require_once __DIR__ . '/AuthMiddleware.php';

class StudentMiddleware {
    public static function handle() {
        AuthMiddleware::handle();
        if (!isStudent()) {
            http_response_code(403);
            die('Access denied. Student area only.');
        }
        return true;
    }
}