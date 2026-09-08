<?php
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Warden.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Fee.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Visitor.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Notice.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/session.php';

class WardenController {
    public static function dashboard() {
        WardenMiddleware::handle();
        $occupancy = db()->query("SELECT COALESCE(SUM(current_occupancy), 0) AS occupied, COALESCE(SUM(capacity), 0) AS capacity FROM rooms")->fetch();
        $stats = [
            'total_students' => Student::count(),
            'total_rooms' => Room::count(),
            'available_rooms' => Room::countAvailable(),
            'occupied_rooms' => Room::countOccupied(),
            'occupancy_percent' => $occupancy['capacity'] > 0 ? round(($occupancy['occupied'] / $occupancy['capacity']) * 100) : 0,
            'pending_complaints' => Complaint::countPending(),
            'pending_allocations' => db()->query("SELECT COUNT(*) FROM room_allocations WHERE status = 'pending'")->fetchColumn(),
            'pending_visitors' => Visitor::countPending(),
            'unpaid_fees' => Fee::countUnpaid()
        ];

        $recentComplaints = array_slice(Complaint::all(), 0, 5);
        $recentActivities = db()->query("SELECT action, details, created_at FROM logs ORDER BY created_at DESC LIMIT 5")->fetchAll();
        view('warden/dashboard', compact('stats', 'recentComplaints', 'recentActivities'));
    }

    public static function section($section) {
        WardenMiddleware::handle();
        $allowedSections = ['notifications', 'profile', 'settings'];
        if (!in_array($section, $allowedSections, true)) {
            abort(404, 'Warden page not found');
        }

        $warden = Warden::findByUserId(currentUserId());
        $items = $section === 'notifications' ? Notification::forUser(currentUserId()) : [];
        $notices = $section === 'notifications' ? Notice::forWarden($warden['assigned_block'] ?? '') : [];
        view('warden/section', compact('section', 'warden', 'items', 'notices'));
    }

    public static function students() {
        WardenMiddleware::handle();
        $warden = Warden::findByUserId(currentUserId());
        $search = trim($_GET['search'] ?? '');
        $sql = "SELECT s.*, u.email, u.full_name, u.phone, r.room_number, r.block FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE (? = '' OR r.block = ? OR s.room_id IS NULL) AND (? = '' OR u.full_name LIKE ? OR s.student_id LIKE ? OR u.email LIKE ?) ORDER BY u.full_name";
        $stmt = db()->prepare($sql);
        $term = '%' . $search . '%';
        $stmt->execute([$warden['assigned_block'] ?? '', $warden['assigned_block'] ?? '', $search, $term, $term, $term]);
        $students = $stmt->fetchAll();
        $rows = $students;
        $title = 'Students'; $backUrl = '/warden/dashboard';
        $columns = ['Name' => 'full_name', 'Student ID' => 'student_id', 'Email' => 'email', 'Room' => 'room_number', 'Phone' => 'phone'];
        view('warden/students', compact('rows', 'search'));
    }

        public static function studentCreate() {
            WardenMiddleware::handle();
            $warden = Warden::findByUserId(currentUserId());
            $stmt = db()->prepare("SELECT * FROM rooms WHERE block = ? AND status IN ('available','occupied') AND current_occupancy < capacity ORDER BY room_number");
            $stmt->execute([$warden['assigned_block'] ?? '']);
            $rooms = $stmt->fetchAll();
            view('warden/student-form', compact('rooms'));
        }

        public static function studentStore() {
            WardenMiddleware::handle();
            $data = $_POST;
            $warden = Warden::findByUserId(currentUserId());
            $errors = validate($data, ['full_name' => 'required|min:2', 'email' => 'required|email|unique:users', 'student_id' => 'required|unique:students', 'password' => 'required|min:6|confirmed', 'phone' => 'numeric']);
            if (!empty($data['room_id'])) {
                $room = Room::find((int) $data['room_id']);
                if (!$room || $room['block'] !== ($warden['assigned_block'] ?? '') || (int) $room['current_occupancy'] >= (int) $room['capacity']) {
                    $errors['room_id'][] = 'Choose an available room in your assigned block.';
                }
            }
            if (!empty($errors)) { redirectWithErrors('/warden/student-create', $errors); return; }
            $username = strtolower(preg_replace('/[^a-z0-9]+/i', '.', strstr($data['email'], '@', true)));
            $base = trim($username, '.') ?: 'student'; $username = $base; $suffix = 1;
            while (User::findByUsername($username)) { $username = $base . $suffix++; }
            db()->beginTransaction();
            try {
                $userId = User::create(['username' => $username, 'email' => $data['email'], 'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT), 'role' => ROLE_STUDENT, 'full_name' => $data['full_name'], 'phone' => $data['phone'] ?? '', 'account_status' => 'approved']);
                Student::create(['user_id' => $userId, 'student_id' => $data['student_id'], 'room_id' => $data['room_id'] ?: null, 'parent_contact' => $data['parent_contact'] ?? '', 'address' => $data['address'] ?? '', 'emergency_contact' => $data['emergency_contact'] ?? '']);
                if (!empty($data['room_id'])) { Room::updateOccupancy((int) $data['room_id']); Room::updateStatus((int) $data['room_id']); }
                db()->commit();
            } catch (Throwable $exception) { db()->rollBack(); redirectWithError('/warden/student-create', 'Unable to create student.'); return; }
            redirectWithSuccess('/warden/students', 'Student created successfully.');
        }

    public static function studentView($id) {
        WardenMiddleware::handle();
        $student = Student::find($id);
        if (!$student) {
            redirectWithError('/warden/students', 'Student not found');
            return;
        }
        $fees = Fee::forStudent($student['id']);
        $complaints = Complaint::forStudent($student['id']);
        $visitors = Visitor::forStudent($student['id']);
        $warden = Warden::findByUserId(currentUserId());
        if ($student['block'] !== null && ($student['block'] ?? null) !== ($warden['assigned_block'] ?? null)) {
            abort(403, 'Student is outside your assigned block.');
        }
        $stmt = db()->prepare("SELECT * FROM rooms WHERE block = ? AND status IN ('available','occupied') AND current_occupancy < capacity ORDER BY room_number");
        $stmt->execute([$warden['assigned_block'] ?? '']);
        $rooms = $stmt->fetchAll();
        view('warden/student-view', compact('student', 'fees', 'complaints', 'visitors', 'rooms'));
    }

    public static function studentUpdate($id) {
        WardenMiddleware::handle();
        $student = Student::find($id);
        $warden = Warden::findByUserId(currentUserId());
        if (!$student || ($student['block'] ?? null) !== ($warden['assigned_block'] ?? null)) {
            abort(403, 'Student is outside your assigned block.');
        }
        $data = $_POST;
        $errors = validate($data, ['full_name' => 'required|min:2', 'phone' => 'numeric']);
        if (!empty($data['room_id'])) {
            $room = Room::find((int) $data['room_id']);
            if (!$room || $room['block'] !== $warden['assigned_block']) {
                $errors['room_id'][] = 'Room must be in your assigned block.';
            }
        }
        if (!empty($errors)) { redirectWithErrors('/warden/student-view/' . $id, $errors); return; }
        User::update($student['user_id'], ['full_name' => $data['full_name'], 'phone' => $data['phone'] ?? '']);
        Student::update($id, ['parent_contact' => $data['parent_contact'] ?? '', 'address' => $data['address'] ?? '', 'emergency_contact' => $data['emergency_contact'] ?? '', 'room_id' => $data['room_id'] ?: null]);
        if (!empty($data['room_id'])) { Room::updateOccupancy((int) $data['room_id']); Room::updateStatus((int) $data['room_id']); }
        redirectWithSuccess('/warden/student-view/' . $id, 'Student updated successfully.');
    }

    public static function rooms() {
        WardenMiddleware::handle();
        $rooms = Room::all();
        $rows = $rooms;
        $title = 'Rooms'; $backUrl = '/warden/dashboard';
        $columns = ['Room' => 'room_number', 'Block' => 'block', 'Floor' => 'floor', 'Capacity' => 'capacity', 'Occupied' => 'current_occupancy', 'Status' => 'status'];
        $resource = 'rooms';
        $createUrl = '/warden/room-create';
        view('management/table', compact('title', 'backUrl', 'columns', 'rows', 'resource', 'createUrl'));
    }

    public static function roomCreate() {
        WardenMiddleware::handle();
        view('warden/room-create');
    }

    public static function roomStore() {
        WardenMiddleware::handle();
        $data = $_POST;
        $warden = Warden::findByUserId(currentUserId());
        if (!$warden || !$warden['assigned_block'] || $data['block'] !== $warden['assigned_block']) {
            redirectWithError('/warden/rooms', 'You can only manage rooms in your assigned hostel block.');
            return;
        }

        $errors = validate($data, [
            'room_number' => 'required|unique:rooms',
            'block' => 'required',
            'floor' => 'required|numeric',
            'capacity' => 'required|numeric|min:1'
        ]);

        if (!empty($errors)) {
            redirectWithErrors('/warden/room-create', $errors);
            return;
        }

        Room::create([
            'room_number' => $data['room_number'],
            'block' => $data['block'],
            'floor' => $data['floor'],
            'capacity' => $data['capacity'],
            'status' => ROOM_AVAILABLE
        ]);

        redirectWithSuccess('/warden/rooms', 'Room added successfully!');
    }

    public static function roomEdit($id) {
        WardenMiddleware::handle();
        $room = Room::find($id);
        if (!$room) {
            redirectWithError('/warden/rooms', 'Room not found');
            return;
        }
        view('warden/room-edit', compact('room'));
    }

    public static function roomUpdate($id) {
        WardenMiddleware::handle();
        $data = $_POST;
        $warden = Warden::findByUserId(currentUserId());
        $room = Room::find($id);
        if (!$warden || !$room || !$warden['assigned_block'] || $room['block'] !== $warden['assigned_block'] || $data['block'] !== $warden['assigned_block']) {
            redirectWithError('/warden/rooms', 'You can only manage rooms in your assigned hostel block.');
            return;
        }

        $errors = validate($data, [
            'room_number' => 'required',
            'block' => 'required',
            'floor' => 'required|numeric',
            'capacity' => 'required|numeric|min:1',
            'status' => 'required|in:available,occupied,maintenance,full'
        ]);

        if (!empty($errors)) {
            redirectWithErrors('/warden/room-edit/' . $id, $errors);
            return;
        }

        Room::update($id, $data);
        redirectWithSuccess('/warden/rooms', 'Room updated successfully!');
    }

    public static function roomDelete($id) {
        WardenMiddleware::handle();
        $room = Room::find($id);
        if (!$room || (int) $room['current_occupancy'] > 0) {
            redirectWithError('/warden/rooms', 'Occupied or missing rooms cannot be deleted.');
            return;
        }
        Room::delete($id);
        redirectWithSuccess('/warden/rooms', 'Room deleted successfully!');
    }

    public static function allocations() {
        WardenMiddleware::handle();
        $allocations = db()->query("SELECT ra.*, u.full_name, s.student_id, r.room_number 
                                   FROM room_allocations ra 
                                   JOIN students s ON ra.student_id = s.id 
                                   JOIN users u ON s.user_id = u.id
                                   JOIN rooms r ON ra.room_id = r.id 
                                   WHERE ra.status = 'pending' 
                                   ORDER BY ra.request_date ASC")->fetchAll();
        $rooms = db()->query("SELECT id, room_number, block, capacity, current_occupancy FROM rooms WHERE status IN ('available','occupied') AND current_occupancy < capacity ORDER BY block, room_number")->fetchAll();
        view('warden/allocations', compact('allocations', 'rooms'));
    }

    public static function approveAllocation() {
        WardenMiddleware::handle();
        $data = $_POST;
        $allocationId = $data['allocation_id'] ?? null;
        $action = $data['action'] ?? '';

        if (!$allocationId || !in_array($action, ['approve', 'reject'])) {
            redirectWithError('/warden/allocations', 'Invalid request');
            return;
        }

        $stmt = db()->prepare("SELECT * FROM room_allocations WHERE id = ? AND status = 'pending'");
        $stmt->execute([$allocationId]);
        $allocation = $stmt->fetch();

        if (!$allocation) {
            redirectWithError('/warden/allocations', 'Allocation not found or already processed');
            return;
        }

        $status = $action === 'approve' ? ALLOCATION_APPROVED : ALLOCATION_REJECTED;

        $stmt = db()->prepare("UPDATE room_allocations SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
        $stmt->execute([$status, currentUserId(), $allocationId]);

        if ($action === 'approve') {
            // Assign room to student
            $stmt = db()->prepare("UPDATE students SET room_id = ? WHERE id = ?");
            $stmt->execute([$allocation['room_id'], $allocation['student_id']]);

            // Update room occupancy
            Room::updateOccupancy($allocation['room_id']);
            Room::updateStatus($allocation['room_id']);

            // Send notification to student
            $student = Student::find($allocation['student_id']);
            Notification::send($student['user_id'], 'Room Allocation Approved',
                'Your room allocation request has been approved. You have been assigned room ' . 
                db()->query("SELECT room_number FROM rooms WHERE id = " . $allocation['room_id'])->fetchColumn(),
                'success'
            );
        }

        redirectWithSuccess('/warden/allocations', 'Allocation ' . $action . 'd successfully!');
    }

    public static function fees() {
        WardenMiddleware::handle();
        $fees = Fee::all();
        $students = Student::all();
        $rows = $fees;
        $title = 'Fees'; $backUrl = '/warden/dashboard';
        $columns = ['Student' => 'full_name', 'Student ID' => 'student_id', 'Amount' => 'amount', 'Due date' => 'due_date', 'Status' => 'status'];
        $resource = 'fees';
        $createUrl = '/warden/fee-create';
        view('management/table', compact('title', 'backUrl', 'columns', 'rows', 'resource', 'createUrl'));
    }

    public static function feeCreate() {
        WardenMiddleware::handle();
        $students = Student::all();
        view('warden/fee-create', compact('students'));
    }

    public static function feeStore() {
        WardenMiddleware::handle();
        $data = $_POST;

        $errors = validate($data, [
            'student_id' => 'required|exists:students',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date'
        ]);

        if (!empty($errors)) {
            redirectWithErrors('/warden/fees', $errors);
            return;
        }

        Fee::create([
            'student_id' => $data['student_id'],
            'amount' => $data['amount'],
            'due_date' => $data['due_date'],
            'status' => FEE_UNPAID,
            'notes' => $data['notes'] ?? ''
        ]);

        redirectWithSuccess('/warden/fees', 'Fee recorded successfully!');
    }

    public static function feeMarkPaid($id) {
        WardenMiddleware::handle();
        $data = $_POST;
        Fee::markPaid($id, $data['payment_method'] ?? 'cash', $data['transaction_id'] ?? null);
        
        // Send notification to student
        $fee = Fee::find($id);
        $student = Student::find($fee['student_id']);
        Notification::send($student['user_id'], 'Fee Paid',
            'Your fee of Rs. ' . number_format($fee['amount'], 2) . ' has been marked as paid.',
            'success'
        );
        
        redirectWithSuccess('/warden/fees', 'Fee marked as paid!');
    }

    public static function complaints() {
        WardenMiddleware::handle();
        $complaints = Complaint::all();
        $rows = $complaints;
        $title = 'Complaints'; $backUrl = '/warden/dashboard';
        $columns = ['Student' => 'full_name', 'Subject' => 'subject', 'Priority' => 'priority', 'Status' => 'status', 'Created' => 'created_at'];
        $resource = 'complaints';
        view('management/table', compact('title', 'backUrl', 'columns', 'rows', 'resource'));
    }

    public static function complaintView($id) {
        WardenMiddleware::handle();
        $complaint = Complaint::find($id);
        if (!$complaint) {
            redirectWithError('/warden/complaints', 'Complaint not found');
            return;
        }
        view('warden/complaints-view', compact('complaint'));
    }

    public static function complaintUpdate($id) {
        WardenMiddleware::handle();
        $data = $_POST;

        $errors = validate($data, [
            'status' => 'required|in:pending,in-progress,resolved',
            'resolution_notes' => 'max:500'
        ]);

        if (!empty($errors)) {
            redirectWithErrors('/warden/complaint-view/' . $id, $errors);
            return;
        }

        if ($data['status'] === COMPLAINT_RESOLVED) {
            Complaint::resolve($id, currentUserId(), $data['resolution_notes'] ?? '');
        } else {
            Complaint::update($id, ['status' => $data['status']]);
        }

        // Send notification to student
        $complaint = Complaint::find($id);
        $student = Student::find($complaint['student_id']);
        Notification::send($student['user_id'], 'Complaint Update',
            'Your complaint "' . $complaint['subject'] . '" has been updated. Status: ' . $data['status'],
            'info'
        );

        redirectWithSuccess('/warden/complaints', 'Complaint updated successfully!');
    }

    public static function visitors() {
        WardenMiddleware::handle();
        $visitors = Visitor::all();
        $rows = $visitors;
        $title = 'Visitors'; $backUrl = '/warden/dashboard';
        $columns = ['Student' => 'full_name', 'Visitor' => 'visitor_name', 'Contact' => 'contact', 'Purpose' => 'purpose', 'Status' => 'status'];
        $resource = 'visitors';
        view('management/table', compact('title', 'backUrl', 'columns', 'rows', 'resource'));
    }

    public static function visitorApprove($id) {
        WardenMiddleware::handle();
        $data = $_POST;
        $action = $data['action'] ?? '';

        if (!in_array($action, ['approve', 'reject'])) {
            redirectWithError('/warden/visitors', 'Invalid action');
            return;
        }

        if ($action === 'approve') {
            Visitor::approve($id, currentUserId());
            $visitor = Visitor::find($id);
            Notification::send($visitor['user_id'], 'Visitor Approved',
                'Your visitor "' . $visitor['visitor_name'] . '" has been approved.',
                'success'
            );
        } else {
            Visitor::reject($id, currentUserId());
        }

        redirectWithSuccess('/warden/visitors', 'Visitor ' . $action . 'd!');
    }

    public static function visitorCheckIn($id) {
        WardenMiddleware::handle();
        Visitor::checkIn($id);
        redirectWithSuccess('/warden/visitors', 'Visitor checked in!');
    }

    public static function visitorCheckOut($id) {
        WardenMiddleware::handle();
        Visitor::checkOut($id);
        redirectWithSuccess('/warden/visitors', 'Visitor checked out!');
    }

    public static function reports() {
        WardenMiddleware::handle();
        $occupancyReport = db()->query("SELECT room_number, capacity, current_occupancy, 
                                        (current_occupancy / capacity * 100) as occupancy_percent 
                                        FROM rooms")->fetchAll();
        
        $feeReport = Fee::all();
        $complaintStats = Complaint::getStatistics();
        
        $title = 'Reports'; $backUrl = '/warden/dashboard';
        $sections = [
            ['title' => 'Room occupancy', 'columns' => ['Room' => 'room_number', 'Capacity' => 'capacity', 'Occupied' => 'current_occupancy', 'Occupancy %' => 'occupancy_percent'], 'rows' => $occupancyReport],
            ['title' => 'Fee records', 'columns' => ['Student' => 'full_name', 'Student ID' => 'student_id', 'Amount' => 'amount', 'Due date' => 'due_date', 'Status' => 'status'], 'rows' => $feeReport]
        ];
        view('management/report', compact('title', 'backUrl', 'sections'));
    }

    public static function profileUpdate() {
        WardenMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['full_name' => 'required|min:2', 'phone' => 'numeric']);
        if (!empty($errors)) {
            redirectWithErrors('/warden/profile', $errors);
            return;
        }
        User::update(currentUserId(), ['full_name' => $data['full_name'], 'phone' => $data['phone'] ?? '']);
        $_SESSION['full_name'] = $data['full_name'];
        redirectWithSuccess('/warden/profile', 'Profile updated successfully.');
    }

    public static function noticeCreate() {
        WardenMiddleware::handle();
        view('warden/notice-form', ['notice' => null, 'backUrl' => '/warden/notifications']);
    }

    public static function noticeEdit($id) {
        WardenMiddleware::handle();
        $warden = Warden::findByUserId(currentUserId());
        $notice = Notice::find($id);
        if (!$notice || $notice['scope'] !== 'hostel' || ($notice['block'] && $notice['block'] !== $warden['assigned_block'])) { abort(403, 'Notice is outside your assigned hostel.'); }
        view('warden/notice-form', compact('notice'));
    }

    public static function noticeStore() {
        WardenMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['title' => 'required|max:150', 'message' => 'required', 'scope' => 'required|in:hostel']);
        if (!empty($errors)) {
            redirectWithErrors('/warden/notice-create', $errors);
            return;
        }
        $warden = Warden::findByUserId(currentUserId());
        $block = $warden['assigned_block'] ?? '';
        if (!empty($data['block']) && $data['block'] !== $block) { redirectWithError('/warden/notice-create', 'Notice must belong to your assigned block.'); return; }
        if (!empty($data['id'])) {
            $notice = Notice::find((int)$data['id']);
            if (!$notice || $notice['scope'] !== 'hostel' || ($notice['block'] && $notice['block'] !== $block)) { abort(403, 'Notice is outside your assigned hostel.'); }
            Notice::update((int)$data['id'], ['title' => $data['title'], 'message' => $data['message'], 'scope' => 'hostel', 'block' => $block]);
        } else {
            Notice::create(['title' => $data['title'], 'message' => $data['message'], 'scope' => 'hostel', 'block' => $block, 'created_by' => currentUserId()]);
        }
        redirectWithSuccess('/warden/notifications', 'Notice published successfully.');
    }

    public static function noticeDelete($id) {
        WardenMiddleware::handle();
        $warden = Warden::findByUserId(currentUserId());
        $notice = Notice::find($id);
        if (!$notice || $notice['scope'] !== 'hostel' || ($notice['block'] && $notice['block'] !== $warden['assigned_block'])) { abort(403, 'Notice is outside your assigned hostel.'); }
        Notice::delete($id);
        redirectWithSuccess('/warden/notifications', 'Notice deleted successfully.');
    }
}
?>