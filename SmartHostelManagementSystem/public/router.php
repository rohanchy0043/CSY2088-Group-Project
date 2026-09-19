<?php
if (PHP_SAPI !== 'cli-server') {
    return false;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/redirect.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/helpers/session.php';
require_once __DIR__ . '/../app/helpers/response.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/middleware/StudentMidddleware.php';
require_once __DIR__ . '/../app/middleware/WardenMiddleware.php';
require_once __DIR__ . '/../app/middleware/AdminMiddleware.php';

require_once __DIR__ . '/../app/controllers/StudentController.php';
require_once __DIR__ . '/../app/controllers/WardenController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/MealController.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($path === '/') {
    require __DIR__ . '/index.php';
    exit;
}

if ($path === '/student/dashboard' && $method === 'GET') {
    StudentController::dashboard();
    exit;
}

if (preg_match('#^/student/(room|fees|complaints|visitors|notifications|profile)$#', $path, $matches) && $method === 'GET') {
    StudentController::section($matches[1]);
    exit;
}
if ($path === '/student/complaint-store' && $method === 'POST') {
    StudentController::createComplaint();
    exit;
}
if ($path === '/student/visitor-store' && $method === 'POST') {
    StudentController::createVisitor();
    exit;
}
if ($path === '/student/profile-update' && $method === 'POST') {
    StudentController::updateProfile();
    exit;
}
if ($path === '/student/room-request' && $method === 'POST') {
    StudentController::requestRoom();
    exit;
}
if (preg_match('#^/student/fee-receipt/(\d+)$#', $path, $matches) && $method === 'GET') {
    StudentController::receipt((int) $matches[1]);
    exit;
}
if ($path === '/student/meals' && $method === 'GET') { MealController::student(); exit; }
if ($path === '/student/meal-feedback-store' && $method === 'POST') { MealController::feedbackStore(); exit; }
if ($path === '/student/food-complaint-store' && $method === 'POST') { MealController::complaintStore(); exit; }

$wardenRoutes = [
    '/warden/dashboard' => ['GET', 'dashboard'],
    '/warden/students' => ['GET', 'students'],
    '/warden/rooms' => ['GET', 'rooms'],
    '/warden/allocations' => ['GET', 'allocations'],
    '/warden/fee-store' => ['POST', 'feeStore'],
    '/warden/fee-create' => ['GET', 'feeCreate'],
    '/warden/fees' => ['GET', 'fees'],
    '/warden/fee-structure-create' => ['GET', 'feeStructureCreate'],
    '/warden/complaints' => ['GET', 'complaints'],
    '/warden/visitors' => ['GET', 'visitors'],
    '/warden/reports' => ['GET', 'reports'],
];

if (isset($wardenRoutes[$path])) {
    [$allowedMethod, $action] = $wardenRoutes[$path];
    if ($method !== $allowedMethod) {
        abort(405, 'Method Not Allowed');
    }
    WardenController::$action();
    exit;
}
if ($path === '/warden/meals' && $method === 'GET') { MealController::warden(); exit; }
if ($path === '/warden/meal-menu-store' && $method === 'POST') { MealController::menuStore(); exit; }
if ($path === '/warden/meal-attendance-store' && $method === 'POST') { MealController::attendanceStore(); exit; }
if ($path === '/warden/food-complaint-update' && $method === 'POST') { MealController::complaintUpdate(); exit; }

if (preg_match('#^/warden/student-view/(\d+)$#', $path, $matches) && $method === 'GET') {
    WardenController::studentView((int) $matches[1]);
    exit;
}
if ($path === '/warden/student-create' && $method === 'GET') { WardenController::studentCreate(); exit; }
if ($path === '/warden/student-store' && $method === 'POST') { WardenController::studentStore(); exit; }
if (preg_match('#^/warden/student-update/(\d+)$#', $path, $matches) && $method === 'POST') { WardenController::studentUpdate((int) $matches[1]); exit; }
if (preg_match('#^/warden/(notifications|profile)$#', $path, $matches) && $method === 'GET') {
    WardenController::section($matches[1]);
    exit;
}
if ($path === '/warden/profile-update' && $method === 'POST') { WardenController::profileUpdate(); exit; }
if ($path === '/warden/notice-create' && $method === 'GET') { WardenController::noticeCreate(); exit; }
if (preg_match('#^/warden/notice-edit/(\d+)$#', $path, $matches) && $method === 'GET') { WardenController::noticeEdit((int)$matches[1]); exit; }
if ($path === '/warden/notice-store' && $method === 'POST') { WardenController::noticeStore(); exit; }
if (preg_match('#^/warden/notice-delete/(\d+)$#', $path, $matches) && $method === 'POST') { WardenController::noticeDelete((int) $matches[1]); exit; }
if ($path === '/warden/fee-structure-store' && $method === 'POST') {
    WardenController::feeStructureStore();
    exit;
}
if ($path === '/warden/fee-payment-store' && $method === 'POST') {
    WardenController::feePaymentStore();
    exit;
}
if (preg_match('#^/warden/fee-structure-edit/(\d+)$#', $path, $matches)) {
    if ($method === 'GET') {
        WardenController::feeStructureEdit((int) $matches[1]);
    } elseif ($method === 'POST') {
        WardenController::feeStructureUpdate((int) $matches[1]);
    } else {
        abort(405, 'Method Not Allowed');
    }
    exit;
}
if (preg_match('#^/warden/fee-payment/(\d+)$#', $path, $matches)) {
    if ($method === 'GET') {
        WardenController::feePaymentCreate((int) $matches[1]);
    } elseif ($method === 'POST') {
        WardenController::feePaymentStore();
    } else {
        abort(405, 'Method Not Allowed');
    }
    exit;
}
if (preg_match('#^/warden/complaint-view/(\d+)$#', $path, $matches)) {
    if ($method === 'GET') {
        WardenController::complaintView((int) $matches[1]);
    } elseif ($method === 'POST') {
        WardenController::complaintUpdate((int) $matches[1]);
    } else {
        abort(405, 'Method Not Allowed');
    }
    exit;
}
if (preg_match('#^/warden/visitor-(approve|check-in|check-out)/(\d+)$#', $path, $matches)) {
    if ($method !== 'POST') {
        abort(405, 'Method Not Allowed');
    }
    $actions = [
        'approve' => 'visitorApprove',
        'check-in' => 'visitorCheckIn',
        'check-out' => 'visitorCheckOut',
    ];
    WardenController::{$actions[$matches[1]]}((int) $matches[2]);
    exit;
}
if ($path === '/warden/allocation-action' && $method === 'POST') {
    WardenController::approveAllocation();
    exit;
}
if ($path === '/warden/assign-room' && $method === 'POST') {
    WardenController::assignRoom();
    exit;
}

$adminRoutes = [
    '/admin/dashboard' => ['GET', 'dashboard'],
    '/admin/users' => ['GET', 'users'],
    '/admin/user-create' => ['GET', 'userCreate'],
    '/admin/user-store' => ['POST', 'userStore'],
    '/admin/students' => ['GET', 'students'],
    '/admin/rooms' => ['GET', 'rooms'],
    '/admin/room-store' => ['POST', 'roomStore'],
    '/admin/hostels' => ['GET', 'hostels'],
    '/admin/hostel-store' => ['POST', 'hostelStore'],
    '/admin/fees' => ['GET', 'fees'],
    '/admin/complaints' => ['GET', 'complaints'],
    '/admin/visitors' => ['GET', 'visitors'],
    '/admin/visitor-store' => ['POST', 'visitorStore'],
    '/admin/wardens' => ['GET', 'wardens'],
    '/admin/reports' => ['GET', 'reports'],
    '/admin/settings' => ['GET', 'settings'],
    '/admin/profile' => ['GET', 'profile'],
    '/admin/notifications' => ['GET', 'notifications'],
    '/admin/notice-create' => ['GET', 'noticeCreate'],
    '/admin/notice-store' => ['POST', 'noticeStore'],
    '/admin/invite-warden' => ['POST', 'createWardenInvitation'],
];

if (isset($adminRoutes[$path])) {
    [$allowedMethod, $action] = $adminRoutes[$path];
    if ($method !== $allowedMethod) {
        abort(405, 'Method Not Allowed');
    }
    AdminController::$action();
    exit;
}

if (preg_match('#^/admin/user-edit/(\d+)$#', $path, $matches)) {
    if ($method === 'GET') {
        AdminController::userEdit((int) $matches[1]);
    } elseif ($method === 'POST') {
        AdminController::userUpdate((int) $matches[1]);
    } else {
        abort(405, 'Method Not Allowed');
    }
    exit;
}
if (preg_match('#^/admin/hostel/(\d+)$#', $path, $matches) && $method === 'POST') {
    AdminController::hostelUpdate((int) $matches[1]);
    exit;
}
if (preg_match('#^/admin/room-update/(\d+)$#', $path, $matches) && $method === 'POST') {
    AdminController::roomUpdate((int) $matches[1]);
    exit;
}
if (preg_match('#^/admin/room-delete/(\d+)$#', $path, $matches) && $method === 'POST') {
    AdminController::roomDelete((int) $matches[1]);
    exit;
}
if (preg_match('#^/admin/user-delete/(\d+)$#', $path, $matches) && $method === 'POST') {
    AdminController::userDelete((int) $matches[1]);
    exit;
}
if (preg_match('#^/admin/user-status/(\d+)$#', $path, $matches) && $method === 'POST') {
    AdminController::userStatus((int) $matches[1]);
    exit;
}
if ($path === '/admin/settings-update' && $method === 'POST') {
    AdminController::updateSettings();
    exit;
}
if ($path === '/admin/profile-update' && $method === 'POST') {
    AdminController::profileUpdate();
    exit;
}
if ($path === '/admin/meals' && $method === 'GET') { MealController::admin(); exit; }
if ($path === '/admin/meal-attendance' && $method === 'GET') { MealController::adminAttendance(); exit; }
if ($path === '/admin/meal-menu-store' && $method === 'POST') { MealController::adminMenuStore(); exit; }
if (preg_match('#^/admin/notice-delete/(\d+)$#', $path, $matches) && $method === 'POST') {
    AdminController::noticeDelete((int) $matches[1]);
    exit;
}

abort(404, 'Page Not Found');