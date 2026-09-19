<?php
require_once __DIR__ . '/AuthMiddleware.php';

class WardenMiddleware {
    public static function handle() {
        AuthMiddleware::handle();
        if (!isWarden()) {
            http_response_code(403);
            die('Access denied. Warden area only.');
        }
        return true;
    }
}