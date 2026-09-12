<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Warden.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Fee.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Visitor.php';
require_once __DIR__ . '/../models/WardenInvitation.php';
require_once __DIR__ . '/../models/Notice.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Hostel.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/session.php';

class AdminController {
    public static function createWardenInvitation() {
        AdminMiddleware::handle();
        $email = $_POST['email'] ?? '';
        $expiresAt = str_replace('T', ' ', $_POST['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+7 days')));
        $errors = validate([
            'email' => $email,
            'expires_at' => $expiresAt
        ], [
            'email' => 'required|email',
            'expires_at' => 'required'
        ]);

        if (!empty($errors)) {
            redirectWithErrors('/admin/users', $errors);
            return;
        }

        $code = strtoupper(bin2hex(random_bytes(4)));
        WardenInvitation::create($email, $code, $expiresAt, currentUserId());
        $_SESSION['invitation_code'] = $code;
        $_SESSION['invitation_email'] = $email;
        $_SESSION['invitation_expires_at'] = $expiresAt;
        redirectWithSuccess('/admin/users', 'Warden invitation created.');
    }

    public static function dashboard() {
        AdminMiddleware::handle();
        $notificationCount = sidebarBadgeCount('admin_notifications', Notification::unreadCount(currentUserId()));
        $visitorCount = sidebarBadgeCount('admin_visitors', Visitor::countPending());
        $complaintCount = sidebarBadgeCount('admin_complaints', Complaint::countPending());
        $stats = [
            'total_users' => User::count(),
            'total_students' => Student::count(),
            'total_wardens' => Warden::count(),
            'total_rooms' => Room::count(),
            'available_rooms' => Room::countAvailable(),
            'occupied_rooms' => Room::countOccupied(),
            'pending_complaints' => Complaint::countPending(),
            'unpaid_fees' => Fee::countUnpaid(),
            'pending_wardens' => db()->query("SELECT COUNT(*) FROM users WHERE role = 'warden' AND account_status = 'pending'")->fetchColumn(),
            'total_collected' => Fee::getTotalCollected(),
            'total_due' => Fee::getTotalDue()
        ];
        
        $recentActivities = db()->query("SELECT * FROM logs ORDER BY created_at DESC LIMIT 10")->fetchAll();
        $overviewRows = db()->query("SELECT DATE_FORMAT(u.created_at, '%b %e') AS label,
                       DATE(u.created_at) AS overview_date,
                       COUNT(*) AS registrations,
                       COALESCE((SELECT SUM(amount) FROM fees WHERE status = 'paid' AND DATE(paid_at) = DATE(u.created_at)), 0) AS revenue
                   FROM users u
                   WHERE u.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                   GROUP BY DATE(u.created_at), DATE_FORMAT(u.created_at, '%b %e')
                   ORDER BY DATE(u.created_at)")->fetchAll();
        $overviewByDate = [];
        foreach ($overviewRows as $row) {
            $overviewByDate[$row['overview_date']] = $row;
        }
        $monthlyOverview = [];
        for ($offset = 6; $offset >= 0; $offset--) {
            $date = date('Y-m-d', strtotime("-$offset days"));
            $monthlyOverview[] = $overviewByDate[$date] ?? [
                'label' => date('M j', strtotime($date)),
                'overview_date' => $date,
                'registrations' => 0,
                'revenue' => 0,
            ];
        }

        view('admin/dashboard', compact('stats', 'recentActivities', 'monthlyOverview', 'notificationCount', 'visitorCount', 'complaintCount'));
    }

    public static function users() {
        AdminMiddleware::handle();
        $users = User::all();
        $invitations = WardenInvitation::all();
        view('admin/users', compact('users', 'invitations'));
    }

    public static function userCreate() {
        AdminMiddleware::handle();
        view('admin/users-create');
    }

    public static function userStore() {
        AdminMiddleware::handle();
        $data = $_POST;

        $role = $data['role'] ?? '';
        if (empty($data['username']) && !empty($data['email'])) {
            $username = strtolower(preg_replace('/[^a-z0-9]+/i', '.', strstr($data['email'], '@', true)));
            $username = trim($username, '.') ?: $role;
            $baseUsername = $username;
            $suffix = 1;
            while (User::findByUsername($username)) {
                $username = $baseUsername . $suffix++;
            }
            $data['username'] = $username;
        }

        $errors = validate($data, [
            'username' => 'required|min:3|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'full_name' => 'required|min:2',
            'role' => 'required|in:student,warden,admin',
            'phone' => 'numeric'
        ]);

        if (($data['role'] ?? '') === ROLE_STUDENT) {
            $studentErrors = validate($data, ['student_id' => 'required|unique:students']);
            $errors = array_merge($errors, $studentErrors);
        }

        if (!empty($errors)) {
            redirectWithErrors('/admin/users', $errors);
            return;
        }

        db()->beginTransaction();
        try {
            $userId = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role'],
                'full_name' => $data['full_name'],
                'phone' => $data['phone'] ?? ''
            ]);

            if ($data['role'] === ROLE_STUDENT) {
                Student::create([
                    'user_id' => $userId,
                    'student_id' => $data['student_id'],
                    'parent_contact' => $data['parent_contact'] ?? '',
                    'address' => $data['address'] ?? ''
                ]);
            } elseif ($data['role'] === ROLE_WARDEN) {
                Warden::create([
                    'user_id' => $userId,
                    'assigned_block' => $data['assigned_block'] ?? ''
                ]);
            }
            db()->commit();
        } catch (Throwable $exception) {
            db()->rollBack();
            redirectWithError('/admin/users', 'Unable to create the account. Please check the submitted details and try again.');
            return;
        }

        redirectWithSuccess('/admin/users', 'User created successfully!');
    }

    public static function userEdit($id) {
        AdminMiddleware::handle();
        $user = User::find($id);
        if (!$user) {
            redirectWithError('/admin/users', 'User not found');
            return;
        }
        view('admin/users-edit', compact('user'));
    }

    public static function userUpdate($id) {
        AdminMiddleware::handle();
        $data = $_POST;

        $errors = validate($data, [
            'email' => 'required|email',
            'full_name' => 'required|min:2',
            'phone' => 'numeric'
        ]);

        if (!empty($errors)) {
            redirectWithErrors('/admin/users-edit/' . $id, $errors);
            return;
        }

        $updateData = [
            'email' => $data['email'],
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? ''
        ];

        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                redirectWithError('/admin/users-edit/' . $id, 'Password must be at least 6 characters');
                return;
            }
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        User::update($id, $updateData);
        $user = User::find($id);
        if (($user['role'] ?? '') === ROLE_WARDEN) {
            $warden = Warden::findByUserId($id);
            if ($warden) {
                Warden::update($warden['id'], ['assigned_block' => trim($data['assigned_block'] ?? '')]);
            } else {
                Warden::create(['user_id' => $id, 'assigned_block' => trim($data['assigned_block'] ?? '')]);
            }
        }
        redirectWithSuccess('/admin/users', 'User updated successfully!');
    }

    public static function userDelete($id) {
        AdminMiddleware::handle();
        if ($id == currentUserId()) {
            redirectWithError('/admin/users', 'You cannot delete yourself');
            return;
        }
        User::delete($id);
        redirectWithSuccess('/admin/users', 'User deleted successfully!');
    }

    public static function userStatus($id) {
        AdminMiddleware::handle();
        if ($id == currentUserId()) {
            redirectWithError('/admin/users', 'You cannot deactivate yourself.');
            return;
        }
        $user = User::find($id);
        if (!$user) {
            redirectWithError('/admin/users', 'User not found.');
            return;
        }
        $status = ($_POST['status'] ?? '') === 'approved' ? 'approved' : 'rejected';
        User::update($id, ['account_status' => $status]);
        redirectWithSuccess('/admin/users', 'Account status updated.');
    }

    public static function students() {
        AdminMiddleware::handle();
        $search = trim($_GET['search'] ?? '');
        $stmt = db()->prepare("SELECT s.*, u.email, u.full_name, u.phone, r.room_number, r.block FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE (? = '' OR u.full_name LIKE ? OR s.student_id LIKE ? OR u.email LIKE ?) ORDER BY u.full_name");
        $term = '%' . $search . '%';
        $stmt->execute([$search, $term, $term, $term]);
        $students = $stmt->fetchAll();
        $rows = $students;
        $title = 'Students'; $backUrl = '/admin/dashboard';
        $columns = ['Name' => 'full_name', 'Student ID' => 'student_id', 'Email' => 'email', 'Room' => 'room_number', 'Phone' => 'phone'];
        view('management/table', compact('title', 'backUrl', 'columns', 'rows', 'search'));
    }

    public static function rooms() {
        AdminMiddleware::handle();
        $rooms = Room::all();
        view('admin/rooms', compact('rooms'));
    }

    public static function roomStore() {
        AdminMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, [
            'room_number' => 'required|unique:rooms',
            'block' => 'required|max:10',
            'floor' => 'required|numeric',
            'capacity' => 'required|numeric|min:1'
        ]);
        if (!empty($errors)) {
            redirectWithErrors('/admin/rooms', $errors);
            return;
        }
        Room::create(['room_number' => trim($data['room_number']), 'block' => trim($data['block']), 'floor' => (int) $data['floor'], 'capacity' => (int) $data['capacity'], 'status' => ROOM_AVAILABLE]);
        redirectWithSuccess('/admin/rooms', 'Room added successfully.');
    }

    public static function roomUpdate($id) {
        AdminMiddleware::handle();
        $room = Room::find($id);
        $data = $_POST;
        if (!$room) {
            redirectWithError('/admin/rooms', 'Room not found.');
            return;
        }
        $errors = validate($data, ['room_number' => 'required', 'block' => 'required|max:10', 'floor' => 'required|numeric', 'capacity' => 'required|numeric|min:1', 'status' => 'required|in:available,occupied,maintenance,full']);
        $stmt = db()->prepare('SELECT COUNT(*) FROM rooms WHERE room_number = ? AND id <> ?');
        $stmt->execute([$data['room_number'] ?? '', $id]);
        if ((int) $stmt->fetchColumn() > 0) { $errors['room_number'][] = 'Room number already exists.'; }
        if ((int) ($data['capacity'] ?? 0) < (int) $room['current_occupancy']) { $errors['capacity'][] = 'Capacity cannot be lower than current occupancy.'; }
        if (!empty($errors)) {
            redirectWithErrors('/admin/rooms', $errors);
            return;
        }
        Room::update($id, ['room_number' => trim($data['room_number']), 'block' => trim($data['block']), 'floor' => (int) $data['floor'], 'capacity' => (int) $data['capacity'], 'status' => $data['status']]);
        redirectWithSuccess('/admin/rooms', 'Room updated successfully.');
    }

    public static function roomDelete($id) {
        AdminMiddleware::handle();
        $room = Room::find($id);
        if (!$room || (int) $room['current_occupancy'] > 0) {
            redirectWithError('/admin/rooms', 'Occupied or missing rooms cannot be deleted.');
            return;
        }
        Room::delete($id);
        redirectWithSuccess('/admin/rooms', 'Room deleted successfully.');
    }

    public static function hostels() {
        AdminMiddleware::handle();
        $hostels = Hostel::all();
        view('admin/hostels-summary', compact('hostels'));
    }

    public static function hostelStore() {
        AdminMiddleware::handle();
        redirectWithError('/admin/hostels', 'This system supports one hostel only. Edit the existing hostel profile instead.');
    }

    public static function hostelUpdate($id) {
        AdminMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['name' => 'required|max:100', 'block' => 'required|max:10', 'status' => 'required|in:active,inactive']);
        if (!empty($errors)) {
            redirectWithErrors('/admin/hostels', $errors);
            return;
        }
        Hostel::update($id, $data);
        redirectWithSuccess('/admin/hostels', 'Hostel updated successfully.');
    }

    public static function fees() {
        AdminMiddleware::handle();
        Fee::generateCurrentMonth();
        $fees = Fee::all();
        view('admin/fees', compact('fees'));
    }

    public static function complaints() {
        AdminMiddleware::handle();
        markSidebarSeen('admin_complaints', Complaint::countPending());
        $complaints = Complaint::all();
        $stats = Complaint::getStatistics();
        $rows = $complaints;
        $title = 'Complaints'; $backUrl = '/admin/dashboard';
        $columns = ['Student' => 'full_name', 'Subject' => 'subject', 'Category' => 'category', 'Priority' => 'priority', 'Status' => 'status', 'Created' => 'created_at'];
        view('management/table', compact('title', 'backUrl', 'columns', 'rows'));
    }

    public static function visitors() {
        AdminMiddleware::handle();
        markSidebarSeen('admin_visitors', Visitor::countPending());
        $visitors = Visitor::all();
        $students = Student::all();
        $stats = [
            'total' => count($visitors),
            'pending' => Visitor::countPending(),
            'approved' => Visitor::countApproved()
        ];
        view('admin/visitors', compact('visitors', 'students', 'stats'));
    }

    public static function visitorStore() {
        AdminMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, [
            'student_id' => 'required',
            'visitor_name' => 'required|max:100',
            'contact' => 'required|numeric',
            'purpose' => 'required|max:255'
        ]);

        if (!empty($errors)) {
            redirectWithErrors('/admin/visitors', $errors);
            return;
        }

        $student = Student::find((int) $data['student_id']);
        if (!$student) {
            redirectWithError('/admin/visitors', 'Please select a valid student.');
            return;
        }

        Visitor::create([
            'student_id' => (int) $data['student_id'],
            'visitor_name' => trim($data['visitor_name']),
            'contact' => trim($data['contact']),
            'purpose' => trim($data['purpose'])
        ]);
        redirectWithSuccess('/admin/visitors', 'Visitor registered successfully.');
    }

    public static function wardens() {
        AdminMiddleware::handle();
        $wardens = Warden::all();
        $rows = $wardens;
        $title = 'Wardens'; $backUrl = '/admin/dashboard';
        $columns = ['Name' => 'full_name', 'Email' => 'email', 'Phone' => 'phone', 'Block' => 'assigned_block'];
        view('management/table', compact('title', 'backUrl', 'columns', 'rows'));
    }

    public static function reports() {
        AdminMiddleware::handle();
        $occupancyReport = db()->query("SELECT room_number, capacity, current_occupancy, 
                                        (current_occupancy / capacity * 100) as occupancy_percent 
                                        FROM rooms")->fetchAll();
        
        $feeReport = Fee::all();
        $complaintStats = Complaint::getStatistics();
        $studentReport = db()->query("SELECT u.full_name, s.student_id, r.room_number, 
                                     (SELECT COUNT(*) FROM fees WHERE student_id=s.id AND status='unpaid') as unpaid_fees,
                                     (SELECT COUNT(*) FROM complaints WHERE student_id=s.id AND status!='resolved') as open_complaints
                                     FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id")->fetchAll();
        $title = 'Reports'; $backUrl = '/admin/dashboard';
        $sections = [
            ['title' => 'Room occupancy', 'columns' => ['Room' => 'room_number', 'Capacity' => 'capacity', 'Occupied' => 'current_occupancy', 'Occupancy %' => 'occupancy_percent'], 'rows' => $occupancyReport],
            ['title' => 'Student overview', 'columns' => ['Student' => 'full_name', 'Student ID' => 'student_id', 'Room' => 'room_number', 'Unpaid fees' => 'unpaid_fees', 'Open complaints' => 'open_complaints'], 'rows' => $studentReport]
        ];
        view('management/report', compact('title', 'backUrl', 'sections'));
    }

    public static function settings() {
        AdminMiddleware::handle();
        $role = 'Administrator'; $backUrl = '/admin/dashboard';
        view('management/settings', compact('role', 'backUrl'));
    }

    public static function updateSettings() {
        AdminMiddleware::handle();
        // Implement settings update logic
        redirectWithSuccess('/admin/settings', 'Settings updated successfully!');
    }

    public static function profile() {
        AdminMiddleware::handle();
        $admin = User::find(currentUserId());
        view('admin/profile', compact('admin'));
    }

    public static function profileUpdate() {
        AdminMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['full_name' => 'required|min:2', 'phone' => 'numeric']);
        if (!empty($errors)) {
            redirectWithErrors('/admin/profile', $errors);
            return;
        }
        $update = ['full_name' => $data['full_name'], 'phone' => $data['phone'] ?? ''];
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                redirectWithError('/admin/profile', 'Password must be at least 6 characters.');
                return;
            }
            $update['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        User::update(currentUserId(), $update);
        $_SESSION['full_name'] = $data['full_name'];
        redirectWithSuccess('/admin/profile', 'Profile updated successfully.');
    }

    public static function notifications() {
        AdminMiddleware::handle();
        markSidebarSeen('admin_notifications', Notification::unreadCount(currentUserId()));
        $notices = Notice::all();
        view('admin/notifications', compact('notices'));
    }

    public static function noticeCreate() {
        AdminMiddleware::handle();
        view('admin/notice-form');
    }

    public static function noticeStore() {
        AdminMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['title' => 'required|max:150', 'message' => 'required', 'scope' => 'required|in:system,hostel']);
        if (!empty($errors)) {
            redirectWithErrors('/admin/notice-create', $errors);
            return;
        }
        Notice::create(['title' => $data['title'], 'message' => $data['message'], 'scope' => $data['scope'], 'block' => null, 'created_by' => currentUserId()]);
        $stmt = db()->query("SELECT id FROM users WHERE role IN ('student', 'warden')");
        Notification::sendToUsers(
            $stmt->fetchAll(PDO::FETCH_COLUMN),
            $data['title'],
            $data['message'],
            'info'
        );
        redirectWithSuccess('/admin/notifications', 'Notice published successfully.');
    }

    public static function noticeDelete($id) {
        AdminMiddleware::handle();
        Notice::delete($id);
        redirectWithSuccess('/admin/notifications', 'Notice deleted successfully.');
    }
}
?>