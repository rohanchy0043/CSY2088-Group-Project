<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

// The home page is public even when a user is logged in.
AuthController::showLanding();