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
    private static function canManageStudent($studentId, $block) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM students s LEFT JOIN rooms r ON s.room_id = r.id WHERE s.id = ? AND (s.room_id IS NULL OR UPPER(TRIM(r.block)) = UPPER(TRIM(?)))");
        $stmt->execute([(int) $studentId, trim((string) $block)]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private static function canManageFee($feeId, $block) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM fees f JOIN students s ON f.student_id = s.id LEFT JOIN rooms r ON s.room_id = r.id WHERE f.id = ? AND (s.room_id IS NULL OR UPPER(TRIM(r.block)) = UPPER(TRIM(?)))");
        $stmt->execute([(int) $feeId, trim((string) $block)]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private static function assignedBlock() {
        $warden = Warden::findByUserId(currentUserId());
        $block = trim($warden['assigned_block'] ?? '');
        return strtoupper(preg_replace('/^block\s+/i', '', $block));
    }

    public static function dashboard() {
        WardenMiddleware::handle();
        $notificationCount = sidebarBadgeCount('warden_notifications', Notification::unreadCount(currentUserId()));
        $block = self::assignedBlock();
        $stmt = db()->prepare("SELECT COALESCE(SUM(current_occupancy), 0) AS occupied, COALESCE(SUM(capacity), 0) AS capacity FROM rooms WHERE block = ?");
        $stmt->execute([$block]);
        $occupancy = $stmt->fetch();
        $stmt = db()->prepare("SELECT COUNT(*) FROM rooms WHERE block = ?");
        $stmt->execute([$block]);
        $totalRooms = (int) $stmt->fetchColumn();
        $stmt = db()->prepare("SELECT COUNT(*) FROM rooms WHERE block = ? AND status = 'available'");
        $stmt->execute([$block]);
        $availableRooms = (int) $stmt->fetchColumn();
        $stmt = db()->prepare("SELECT COUNT(*) FROM rooms WHERE block = ? AND current_occupancy > 0");
        $stmt->execute([$block]);
        $occupiedRooms = (int) $stmt->fetchColumn();
        $stats = [
            'total_students' => 0,
            'total_rooms' => $totalRooms,
            'available_rooms' => $availableRooms,
            'occupied_rooms' => $occupiedRooms,
            'occupancy_percent' => $occupancy['capacity'] > 0 ? round(($occupancy['occupied'] / $occupancy['capacity']) * 100) : 0,
            'pending_complaints' => 0,
            'pending_allocations' => 0,
            'pending_visitors' => 0,
            'unpaid_fees' => 0
        ];

        $stmt = db()->prepare("SELECT COUNT(*) FROM students s LEFT JOIN rooms r ON s.room_id = r.id WHERE s.room_id IS NULL OR r.block = ?");
        $stmt->execute([$block]);
        $stats['total_students'] = (int) $stmt->fetchColumn();
        $stmt = db()->prepare("SELECT COUNT(*) FROM complaints c JOIN students s ON c.student_id = s.id LEFT JOIN rooms r ON s.room_id = r.id WHERE c.status IN ('pending', 'in-progress') AND (s.room_id IS NULL OR r.block = ?)");
        $stmt->execute([$block]);
        $stats['pending_complaints'] = (int) $stmt->fetchColumn();
        $stats['pending_complaints_badge'] = sidebarBadgeCount('warden_complaints', $stats['pending_complaints']);
        $stmt = db()->prepare("SELECT COUNT(*) FROM room_allocations a JOIN rooms r ON a.room_id = r.id WHERE a.status = 'pending' AND r.block = ?");
        $stmt->execute([$block]);
        $stats['pending_allocations'] = (int) $stmt->fetchColumn();
        $stmt = db()->prepare("SELECT COUNT(*) FROM visitors v JOIN students s ON v.student_id = s.id LEFT JOIN rooms r ON s.room_id = r.id WHERE v.status = 'pending' AND (s.room_id IS NULL OR r.block = ?)");
        $stmt->execute([$block]);
        $stats['pending_visitors'] = (int) $stmt->fetchColumn();
        $stats['pending_visitors_badge'] = sidebarBadgeCount('warden_visitors', $stats['pending_visitors']);
        $stmt = db()->prepare("SELECT COUNT(*) FROM fees f JOIN students s ON f.student_id = s.id LEFT JOIN rooms r ON s.room_id = r.id WHERE f.status IN ('unpaid', 'partial', 'overdue') AND (s.room_id IS NULL OR r.block = ?)");
        $stmt->execute([$block]);
        $stats['unpaid_fees'] = (int) $stmt->fetchColumn();

        $stmt = db()->prepare("SELECT c.*, u.full_name, s.student_id FROM complaints c JOIN students s ON c.student_id = s.id JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE s.room_id IS NULL OR r.block = ? ORDER BY c.created_at DESC LIMIT 5");
        $stmt->execute([$block]);
        $recentComplaints = $stmt->fetchAll();
        $recentActivities = db()->query("SELECT action, details, created_at FROM logs ORDER BY created_at DESC LIMIT 5")->fetchAll();
        view('warden/dashboard', compact('stats', 'recentComplaints', 'recentActivities', 'notificationCount'));
    }

    public static function section($section) {
        WardenMiddleware::handle();
        $allowedSections = ['notifications', 'profile'];
        if (!in_array($section, $allowedSections, true)) {
            abort(404, 'Warden page not found');
        }

        $warden = Warden::findByUserId(currentUserId());
        if ($section === 'notifications') { markSidebarSeen('warden_notifications', Notification::unreadCount(currentUserId())); }
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
        view('warden/student-details', compact('rows', 'search'));
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
            if (!empty($data['phone'])) {
                $data['phone'] = preg_replace('/^\+977\s*/', '+977 ', trim($data['phone']));
                $data['phone'] = preg_replace('/\s+/', ' ', $data['phone']);
            }
            $errors = validate($data, ['full_name' => 'required|min:2', 'email' => 'required|email|unique:users', 'student_id' => 'required|unique:students', 'password' => 'required|min:6|confirmed', 'phone' => 'numeric']);
            if (!empty($data['room_id'])) {
                $room = Room::find((int) $data['room_id']);
                if (!$room || strtoupper(trim($room['block'])) !== self::assignedBlock() || $room['status'] === ROOM_MAINTENANCE || (int) $room['current_occupancy'] >= (int) $room['capacity']) {
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
        if (!$student || !self::canManageStudent((int) $student['id'], $warden['assigned_block'] ?? '')) {
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
        if (!$student || !self::canManageStudent((int) $student['id'], $warden['assigned_block'] ?? '')) {
            abort(403, 'Student is outside your assigned block.');
        }
        $data = $_POST;
        $errors = validate($data, ['full_name' => 'required|min:2', 'phone' => 'numeric']);
        if (!empty($data['room_id'])) {
            $room = Room::find((int) $data['room_id']);
            if (!$room || strtoupper(trim($room['block'])) !== self::assignedBlock() || $room['status'] === ROOM_MAINTENANCE || (int) $room['current_occupancy'] >= (int) $room['capacity']) {
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
        $warden = Warden::findByUserId(currentUserId());
        $stmt = db()->prepare("SELECT r.*, GROUP_CONCAT(u.full_name ORDER BY u.full_name SEPARATOR ', ') AS assigned_students
                       FROM rooms r
                       LEFT JOIN students s ON s.room_id = r.id
                       LEFT JOIN users u ON u.id = s.user_id
                       WHERE r.block = ?
                       GROUP BY r.id
                       ORDER BY r.floor, r.room_number");
        $stmt->execute([$warden['assigned_block'] ?? '']);
        $rooms = $stmt->fetchAll();
        view('warden/rooms', compact('rooms'));
    }

    public static function roomCreate() {
        WardenMiddleware::handle();
        $warden = Warden::findByUserId(currentUserId());
        view('warden/room-create', compact('warden'));
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
        $warden = Warden::findByUserId(currentUserId());
        if (!$room || $room['block'] !== ($warden['assigned_block'] ?? '')) {
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

        $capacity = (int) $data['capacity'];
        $occupancy = (int) $room['current_occupancy'];
        if ($capacity < $occupancy) {
            redirectWithErrors('/warden/room-edit/' . $id, ['capacity' => ['Capacity cannot be lower than current occupancy.']]);
            return;
        }

        $stmt = db()->prepare('SELECT COUNT(*) FROM rooms WHERE room_number = ? AND id <> ?');
        $stmt->execute([$data['room_number'], $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            redirectWithErrors('/warden/room-edit/' . $id, ['room_number' => ['Room number already exists.']]);
            return;
        }

        $status = $data['status'] === ROOM_MAINTENANCE
            ? ROOM_MAINTENANCE
            : ($occupancy >= $capacity ? ROOM_FULL : ($occupancy > 0 ? ROOM_OCCUPIED : ROOM_AVAILABLE));
        Room::update($id, [
            'room_number' => trim($data['room_number']),
            'floor' => (int) $data['floor'],
            'capacity' => $capacity,
            'status' => $status
        ]);
        redirectWithSuccess('/warden/rooms', 'Room updated successfully!');
    }

    public static function roomDelete($id) {
        WardenMiddleware::handle();
        $room = Room::find($id);
        $warden = Warden::findByUserId(currentUserId());
        if (!$room || $room['block'] !== ($warden['assigned_block'] ?? '') || (int) $room['current_occupancy'] > 0) {
            redirectWithError('/warden/rooms', 'Occupied or missing rooms cannot be deleted.');
            return;
        }
        Room::delete($id);
        redirectWithSuccess('/warden/rooms', 'Room deleted successfully!');
    }

    public static function allocations() {
        WardenMiddleware::handle();
        $block = self::assignedBlock();
        $stmt = db()->prepare("SELECT ra.*, u.full_name, s.student_id, r.room_number 
                       FROM room_allocations ra 
                       JOIN students s ON ra.student_id = s.id 
                       JOIN users u ON s.user_id = u.id
                       JOIN rooms r ON ra.room_id = r.id 
                       WHERE ra.status = 'pending' AND r.block = ?
                       ORDER BY ra.request_date ASC");
        $stmt->execute([$block]);
        $allocations = $stmt->fetchAll();
        $stmt = db()->prepare("SELECT id, room_number, block, capacity, current_occupancy FROM rooms WHERE block = ? AND status IN ('available','occupied') AND current_occupancy < capacity ORDER BY room_number");
        $stmt->execute([$block]);
        $rooms = $stmt->fetchAll();
        $stmt = db()->prepare("SELECT s.id, s.student_id, u.full_name FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE s.room_id IS NULL AND NOT EXISTS (SELECT 1 FROM room_allocations ra WHERE ra.student_id = s.id AND ra.status = 'pending') ORDER BY u.full_name");
        $stmt->execute();
        $unassignedStudents = $stmt->fetchAll();
        view('warden/allocations', compact('allocations', 'rooms', 'unassignedStudents'));
    }

    public static function assignRoom() {
        WardenMiddleware::handle();
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $roomId = (int) ($_POST['room_id'] ?? 0);
        $block = self::assignedBlock();
        $student = Student::find($studentId);
        $room = Room::find($roomId);
        if (!$student || !empty($student['room_id']) || !$room || $room['block'] !== $block || $room['status'] === ROOM_MAINTENANCE || (int) $room['current_occupancy'] >= (int) $room['capacity']) {
            redirectWithError('/warden/allocations', 'Select an unassigned student and an available room in your block.');
            return;
        }
        $stmt = db()->prepare("SELECT COUNT(*) FROM room_allocations WHERE student_id = ? AND status = 'pending'");
        $stmt->execute([$studentId]);
        if ((int) $stmt->fetchColumn() > 0) {
            redirectWithError('/warden/allocations', 'This student already has a pending room request.');
            return;
        }
        db()->beginTransaction();
        try {
            $stmt = db()->prepare('UPDATE students SET room_id = ? WHERE id = ?');
            $stmt->execute([$roomId, $studentId]);
            Room::updateOccupancy($roomId);
            Room::updateStatus($roomId);
            db()->commit();
            Fee::generateForStudent($studentId);
        } catch (Throwable $exception) {
            db()->rollBack();
            redirectWithError('/warden/allocations', 'Room assignment failed.');
            return;
        }
        redirectWithSuccess('/warden/allocations', 'Room assigned successfully.');
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

        $room = Room::find($allocation['room_id']);
        if (!$room || $room['block'] !== self::assignedBlock()) {
            redirectWithError('/warden/allocations', 'You can only manage allocations in your assigned hostel block.');
            return;
        }
        $student = Student::find($allocation['student_id']);
        if (!$student || !empty($student['room_id'])) {
            redirectWithError('/warden/allocations', 'This student already has a room assigned.');
            return;
        }
        if ($action === 'approve' && ($room['status'] === ROOM_MAINTENANCE || (int) $room['current_occupancy'] >= (int) $room['capacity'])) {
            redirectWithError('/warden/allocations', 'This room is no longer available.');
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
            Fee::generateForStudent((int) $allocation['student_id']);

            // Send notification to student
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
        Fee::generateCurrentMonth();
        $stmt = db()->prepare("SELECT s.id AS student_record_id, u.full_name, s.student_id, COALESCE(SUM(f.amount), 0) AS total_fee, COALESCE(SUM(f.paid_amount), 0) AS paid_amount, GREATEST(SUM(f.amount) - SUM(f.paid_amount), 0) AS remaining_amount, CASE WHEN SUM(f.amount) <= SUM(f.paid_amount) THEN 'paid' WHEN MIN(f.due_date) < CURDATE() AND GREATEST(SUM(f.amount) - SUM(f.paid_amount), 0) > 0 THEN 'overdue' WHEN SUM(f.paid_amount) > 0 THEN 'partial' ELSE 'unpaid' END AS status FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN fees f ON f.student_id = s.id LEFT JOIN rooms r ON s.room_id = r.id WHERE s.room_id IS NULL OR UPPER(TRIM(r.block)) = UPPER(TRIM(?)) GROUP BY s.id, u.full_name, s.student_id ORDER BY u.full_name");
        $stmt->execute([self::assignedBlock()]);
        $students = $stmt->fetchAll();
        $structures = db()->query("SELECT * FROM fee_structures ORDER BY effective_from DESC, id DESC")->fetchAll();
        view('warden/fees', compact('students', 'structures'));
    }

    public static function feeStructureCreate() {
        WardenMiddleware::handle();
        $structure = db()->query("SELECT * FROM fee_structures ORDER BY effective_from DESC, id DESC LIMIT 1")->fetch();
        if (!$structure) {
            view('warden/fee-structure-create');
            return;
        }
        redirect('/warden/fee-structure-edit/' . (int) $structure['id']);
    }

    public static function feeStructureEdit($id) {
        WardenMiddleware::handle();
        $stmt = db()->prepare('SELECT * FROM fee_structures WHERE id = ?');
        $stmt->execute([(int) $id]);
        $structure = $stmt->fetch();
        if (!$structure) {
            redirectWithError('/warden/fees', 'Fee structure not found.');
            return;
        }
        view('warden/fee-structure-create', compact('structure'));
    }

    public static function feeStructureStore() {
        WardenMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, [
            'name' => 'required|max:100',
            'monthly_amount' => 'required|numeric|min:1',
            'due_day' => 'required|numeric|min:1',
            'status' => 'required|in:active,inactive'
        ]);
        if ((int) ($data['due_day'] ?? 0) < 1 || (int) ($data['due_day'] ?? 0) > 31) {
            $errors['due_day'][] = 'Due day must be between 1 and 31.';
        }
        if ((float) ($data['monthly_amount'] ?? 0) <= 0) {
            $errors['monthly_amount'][] = 'Monthly amount must be greater than 0.';
        }
        if (!empty($errors)) {
            redirectWithErrors('/warden/fee-structure-create', $errors);
            return;
        }
        $stmt = db()->prepare('INSERT INTO fee_structures (name, monthly_amount, due_day, effective_from, status, created_by) VALUES (?, ?, ?, CURDATE(), ?, ?)');
        $stmt->execute([trim($data['name']), $data['monthly_amount'], $data['due_day'], $data['status'], currentUserId()]);
        redirectWithSuccess('/warden/fees', 'Fee structure created successfully.');
    }

    public static function feeStructureUpdate($id) {
        WardenMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['name' => 'required|max:100', 'monthly_amount' => 'required|numeric|min:1', 'due_day' => 'required|numeric|min:1', 'status' => 'required|in:active,inactive']);
        if ((int) ($data['due_day'] ?? 0) < 1 || (int) ($data['due_day'] ?? 0) > 31) {
            $errors['due_day'][] = 'Due day must be between 1 and 31.';
        }
        if ((float) ($data['monthly_amount'] ?? 0) <= 0) {
            $errors['monthly_amount'][] = 'Monthly amount must be greater than 0.';
        }
        if (!empty($errors)) {
            redirectWithErrors('/warden/fee-structure-edit/' . (int) $id, $errors);
            return;
        }
        $stmt = db()->prepare('UPDATE fee_structures SET name = ?, monthly_amount = ?, due_day = ?, status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([trim($data['name']), $data['monthly_amount'], $data['due_day'], $data['status'], (int) $id]);
        redirectWithSuccess('/warden/fees', 'Fee structure updated successfully.');
    }

    public static function feeCreate() {
        WardenMiddleware::handle();
        $stmt = db()->prepare("SELECT s.*, u.email, u.username, u.full_name, u.phone, r.room_number, r.block FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE s.room_id IS NULL OR r.block = ? ORDER BY u.full_name");
        $stmt->execute([self::assignedBlock()]);
        $students = $stmt->fetchAll();
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

        if (!self::canManageStudent((int) $data['student_id'], self::assignedBlock())) {
            redirectWithError('/warden/fees', 'You can only manage students in your assigned block.');
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

    public static function feePaymentCreate($studentId = 0) {
        WardenMiddleware::handle();
        if ((int) $studentId <= 0) {
            sessionFlash('error');
            sessionFlash('errors');
            redirect('/warden/fees');
            return;
        }
        $stmt = db()->prepare("SELECT s.*, u.full_name, r.block FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE s.id = ? AND (s.room_id IS NULL OR UPPER(TRIM(r.block)) = UPPER(TRIM(?)))");
        $stmt->execute([(int) $studentId, self::assignedBlock()]);
        $selectedStudent = $stmt->fetch();
        $stmt = db()->prepare("SELECT s.id, s.student_id, u.full_name FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE s.room_id IS NULL OR UPPER(TRIM(r.block)) = UPPER(TRIM(?)) ORDER BY u.full_name");
        $stmt->execute([self::assignedBlock()]);
        $students = $stmt->fetchAll();
        $allFees = $selectedStudent ? Fee::forStudent($selectedStudent['id']) : [];
        $paymentHistory = $selectedStudent ? Fee::paymentsForStudent($selectedStudent['id']) : [];
        $fees = array_values(array_filter(
            $allFees,
            static fn($fee) => (float) $fee['amount'] > (float) $fee['paid_amount']
        ));
        usort($fees, static function ($first, $second) {
            $currentMonth = strtotime(date('Y-m-01'));
            $firstMonth = strtotime($first['fee_month'] ?: $first['due_date']);
            $secondMonth = strtotime($second['fee_month'] ?: $second['due_date']);
            $firstDistance = abs($firstMonth - $currentMonth);
            $secondDistance = abs($secondMonth - $currentMonth);
            return $firstDistance <=> $secondDistance ?: $secondMonth <=> $firstMonth;
        });
        view('warden/fee-payment', compact('students', 'selectedStudent', 'fees', 'paymentHistory'));
    }

    public static function feePaymentStore() {
        WardenMiddleware::handle();
        $data = $_POST;
        $fee = Fee::find((int) ($data['fee_id'] ?? 0));
        if (!$fee || !self::canManageFee((int) $fee['id'], self::assignedBlock())) {
            redirectWithError('/warden/fees', 'Fee record is outside your assigned block.');
            return;
        }
        $amount = filter_var($data['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($amount === false || $amount <= 0) {
            redirectWithError('/warden/fees', 'Enter a valid payment amount.');
            return;
        }
        $amount = round((float) $amount, 2);
        $paymentType = $data['payment_type'] ?? 'monthly';
        if (!in_array($paymentType, ['monthly', 'multiple_months', 'full_year'], true)) {
            redirectWithError('/warden/fee-payment/' . (int) $fee['student_id'], 'Select a valid payment type.');
            return;
        }
        $selectedMonth = date('Y-m', strtotime($fee['fee_month'] ?: $fee['due_date']));
        $from = $data['payment_from'] ?? $selectedMonth;
        $to = $data['payment_to'] ?? $selectedMonth;
        if ($paymentType === 'monthly' && ($from !== $selectedMonth || $to !== $selectedMonth)) {
            redirectWithError('/warden/fee-payment/' . (int) $fee['student_id'], 'The monthly payment period must match the selected fee month.');
            return;
        }
        $paymentDate = $data['payment_date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $paymentDate) || strtotime($paymentDate) === false || $paymentDate > date('Y-m-d')) {
            redirectWithError('/warden/fee-payment/' . (int) $fee['student_id'], 'Select a valid payment date.');
            return;
        }
        $feesToPay = [$fee];
        if ($paymentType !== 'monthly') {
            if (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to) || $from > $to) {
                redirectWithError('/warden/fees', 'Select a valid payment period.');
                return;
            }
            $limit = $paymentType === 'full_year' ? 12 : 36;
            $stmt = db()->prepare("SELECT * FROM fees WHERE student_id = ? AND COALESCE(fee_month, due_date) >= CONCAT(?, '-01') AND COALESCE(fee_month, due_date) < DATE_ADD(CONCAT(?, '-01'), INTERVAL 1 MONTH) ORDER BY COALESCE(fee_month, due_date) ASC, id ASC");
            $stmt->execute([(int) $fee['student_id'], $from, $to]);
            $feesToPay = $stmt->fetchAll();
            $feesToPay = array_values(array_filter(
                $feesToPay,
                static fn($item) => (float) $item['amount'] > (float) $item['paid_amount']
            ));
            if (!$feesToPay) {
                redirectWithError('/warden/fee-payment/' . (int) $fee['student_id'], 'No unpaid fee records were found for the selected payment period.');
                return;
            }
            if (count($feesToPay) > $limit) {
                redirectWithError('/warden/fees', 'The selected payment period is too long.');
                return;
            }
        }
        $outstanding = round(array_sum(array_map(static fn($item) => max(0, round((float) $item['amount'] - (float) $item['paid_amount'], 2)), $feesToPay)), 2);
        if ($amount > $outstanding + 0.01) {
            redirectWithError('/warden/fee-payment/' . (int) $fee['student_id'], 'Payment amount cannot exceed the selected period balance of NPR ' . number_format($outstanding, 2) . '.');
            return;
        }
        $amount = min($amount, $outstanding);
        try {
            $remainingPayment = $amount;
            foreach ($feesToPay as $feeToPay) {
                if ($remainingPayment <= 0) {
                    break;
                }
                $feeBalance = max(0, round((float) $feeToPay['amount'] - (float) $feeToPay['paid_amount'], 2));
                $installment = round(min($remainingPayment, $feeBalance), 2);
                if ($installment > 0) {
                    Fee::recordPayment((int) $feeToPay['id'], (int) $feeToPay['student_id'], $installment, $paymentDate, $data['payment_method'] ?? 'cash', '', '', currentUserId());
                    $remainingPayment -= $installment;
                }
            }
            if ($remainingPayment > 0.009) {
                throw new RuntimeException('The payment could not be fully applied to the selected fee period.');
            }
        } catch (Throwable $exception) {
            redirectWithError('/warden/fee-payment/' . (int) $fee['student_id'], 'Payment could not be saved: ' . $exception->getMessage());
            return;
        }
        if (!empty($fee['student_user_id'])) {
            $periodLabel = $paymentType === 'monthly'
                ? date('F Y', strtotime($selectedMonth . '-01'))
                : date('M Y', strtotime($from . '-01')) . ' - ' . date('M Y', strtotime($to . '-01'));
            Notification::send(
                (int) $fee['student_user_id'],
                'Fee Payment Received',
                'Your payment of NPR ' . number_format($amount, 2) . ' for ' . $periodLabel . ' has been recorded successfully. Your fee balance has been updated.',
                'success'
            );
        }
        redirectWithSuccess('/warden/fees', 'Payment of NPR ' . number_format($amount, 2) . ' recorded successfully.');
    }

    public static function complaints() {
        WardenMiddleware::handle();
        $stmt = db()->prepare("SELECT c.*, u.full_name, s.student_id FROM complaints c JOIN students s ON c.student_id = s.id JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE s.room_id IS NULL OR r.block = ? ORDER BY c.created_at DESC");
        $stmt->execute([self::assignedBlock()]);
        $complaints = $stmt->fetchAll();
        markSidebarSeen('warden_complaints', count(array_filter($complaints, static fn($complaint) => in_array($complaint['status'], ['pending', 'in-progress'], true))));
        $rows = $complaints;
        $title = 'Complaints'; $backUrl = '/warden/dashboard';
        $columns = ['Student' => 'full_name', 'Subject' => 'subject', 'Priority' => 'priority', 'Status' => 'status', 'Created' => 'created_at'];
        $resource = 'complaints';
        view('management/table', compact('title', 'backUrl', 'columns', 'rows', 'resource'));
    }

    public static function complaintView($id) {
        WardenMiddleware::handle();
        $complaint = Complaint::find($id);
        if (!$complaint || !self::canManageStudent((int) $complaint['student_record_id'], self::assignedBlock())) {
            redirectWithError('/warden/complaints', 'Complaint not found');
            return;
        }
        view('warden/complaints-view', compact('complaint'));
    }

    public static function complaintUpdate($id) {
        WardenMiddleware::handle();
        $data = $_POST;
        $complaint = Complaint::find($id);
        if (!$complaint || !self::canManageStudent((int) $complaint['student_record_id'], self::assignedBlock())) {
            redirectWithError('/warden/complaints', 'Complaint is outside your assigned block.');
            return;
        }

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
        $student = Student::find((int) $complaint['student_record_id']);
        if ($student) {
            Notification::send($student['user_id'], 'Complaint Update',
                'Your complaint "' . $complaint['subject'] . '" has been updated. Status: ' . $data['status'],
                'info'
            );
        }

        redirectWithSuccess('/warden/complaints', 'Complaint updated successfully!');
    }

    public static function visitors() {
        WardenMiddleware::handle();
        $stmt = db()->prepare("SELECT v.*, u.full_name, s.student_id FROM visitors v JOIN students s ON v.student_id = s.id JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE s.room_id IS NULL OR r.block = ? ORDER BY v.check_in DESC");
        $stmt->execute([self::assignedBlock()]);
        $visitors = $stmt->fetchAll();
        markSidebarSeen('warden_visitors', count(array_filter($visitors, static fn($visitor) => $visitor['status'] === 'pending')));
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
        $visitor = Visitor::find($id);
        if (!$visitor || !self::canManageStudent((int) $visitor['student_record_id'], self::assignedBlock())) {
            redirectWithError('/warden/visitors', 'Visitor record is outside your assigned block.');
            return;
        }

        if (!in_array($action, ['approve', 'reject'])) {
            redirectWithError('/warden/visitors', 'Invalid action');
            return;
        }

        if ($action === 'approve') {
            Visitor::approve($id, currentUserId());
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
        $visitor = Visitor::find($id);
        if (!$visitor || !self::canManageStudent((int) $visitor['student_record_id'], self::assignedBlock())) {
            redirectWithError('/warden/visitors', 'Visitor record is outside your assigned block.');
            return;
        }
        Visitor::checkIn($id);
        redirectWithSuccess('/warden/visitors', 'Visitor checked in!');
    }

    public static function visitorCheckOut($id) {
        WardenMiddleware::handle();
        $visitor = Visitor::find($id);
        if (!$visitor || !self::canManageStudent((int) $visitor['student_record_id'], self::assignedBlock())) {
            redirectWithError('/warden/visitors', 'Visitor record is outside your assigned block.');
            return;
        }
        Visitor::checkOut($id);
        redirectWithSuccess('/warden/visitors', 'Visitor checked out!');
    }

    public static function reports() {
        WardenMiddleware::handle();
        $block = self::assignedBlock();
        $stmt = db()->prepare("SELECT room_number, capacity, current_occupancy, 
                                        (current_occupancy / capacity * 100) as occupancy_percent 
                                        FROM rooms WHERE block = ?");
        $stmt->execute([$block]);
        $occupancyReport = $stmt->fetchAll();
        
        $stmt = db()->prepare("SELECT f.*, u.full_name, s.student_id FROM fees f JOIN students s ON f.student_id = s.id JOIN users u ON s.user_id = u.id LEFT JOIN rooms r ON s.room_id = r.id WHERE s.room_id IS NULL OR r.block = ? ORDER BY f.created_at DESC");
        $stmt->execute([$block]);
        $feeReport = $stmt->fetchAll();
        
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
        if (!$notice || $notice['scope'] !== 'hostel') { abort(403, 'Notice not found.'); }
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
        if (!empty($data['id'])) {
            $notice = Notice::find((int)$data['id']);
            if (!$notice || $notice['scope'] !== 'hostel') { abort(403, 'Notice not found.'); }
            Notice::update((int)$data['id'], ['title' => $data['title'], 'message' => $data['message'], 'scope' => 'hostel', 'block' => null]);
        } else {
            Notice::create(['title' => $data['title'], 'message' => $data['message'], 'scope' => 'hostel', 'block' => null, 'created_by' => currentUserId()]);
            $stmt = db()->query("SELECT u.id FROM users u JOIN students s ON s.user_id = u.id");
            Notification::sendToUsers(
                $stmt->fetchAll(PDO::FETCH_COLUMN),
                $data['title'],
                $data['message'],
                'info'
            );
        }
        redirectWithSuccess('/warden/notifications', 'Notice published successfully.');
    }

    public static function noticeDelete($id) {
        WardenMiddleware::handle();
        $notice = Notice::find($id);
        if (!$notice || $notice['scope'] !== 'hostel') { abort(403, 'Notice not found.'); }
        Notice::delete($id);
        redirectWithSuccess('/warden/notifications', 'Notice deleted successfully.');
    }
}
?>