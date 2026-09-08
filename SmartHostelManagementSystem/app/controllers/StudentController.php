<?php
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Fee.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Visitor.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Notice.php';
require_once __DIR__ . '/../middleware/StudentMidddleware.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/response.php';

class StudentController {
	public static function dashboard() {
		StudentMiddleware::handle();
		$student = Student::findByUserId(currentUserId());
			$studentId = $student['id'] ?? 0;
		$stats = [
				'unpaid_fees' => $student ? Student::getUnpaidFees($studentId) : 0,
				'pending_complaints' => $student ? Student::getPendingComplaints($studentId) : 0,
				'total_fees_due' => $student ? Student::getTotalFeesDue($studentId) : 0,
				'visitors_this_month' => $student ? db()->prepare("SELECT COUNT(*) FROM visitors WHERE student_id = ? AND check_in >= DATE_FORMAT(CURDATE(), '%Y-%m-01')") : 0,
			'notifications' => Notification::unreadCount(currentUserId())
		];
			if ($student) {
				$visitorCount = $stats['visitors_this_month'];
				$visitorCount->execute([$studentId]);
				$stats['visitors_this_month'] = (int) $visitorCount->fetchColumn();
			}
			$recentFees = $student ? Fee::forStudent($studentId) : [];
			$recentFees = array_slice($recentFees, 0, 3);
			$recentComplaints = $student ? array_slice(Complaint::forStudent($studentId), 0, 3) : [];
			$recentNotices = array_slice(Notice::forStudent($student['block'] ?? null), 0, 3);
			view('student/dashboard', compact('student', 'stats', 'recentFees', 'recentComplaints', 'recentNotices'));
	}

		public static function section($section) {
			StudentMiddleware::handle();
			$allowedSections = ['room', 'fees', 'complaints', 'visitors', 'notifications', 'profile'];
			if (!in_array($section, $allowedSections, true)) {
				abort(404, 'Student page not found');
			}

			$student = Student::findByUserId(currentUserId());
			$items = [];
			if ($student && $section === 'room' && $student['room_id']) {
				$stmt = db()->prepare("SELECT u.full_name, s.student_id FROM students s JOIN users u ON s.user_id = u.id WHERE s.room_id = ? AND s.id <> ? ORDER BY u.full_name");
				$stmt->execute([$student['room_id'], $student['id']]);
				$items = $stmt->fetchAll();
			}
			if ($student && $section === 'fees') {
				$items = Fee::forStudent($student['id']);
			} elseif ($student && $section === 'complaints') {
				$items = Complaint::forStudent($student['id']);
			} elseif ($student && $section === 'visitors') {
				$items = Visitor::forStudent($student['id']);
			} elseif ($section === 'notifications') {
				$items = array_merge(Notification::forUser(currentUserId()), Notice::forStudent($student['block'] ?? null));
			}

			view('student/section', compact('section', 'student', 'items'));
		}

		public static function createComplaint() {
			StudentMiddleware::handle();
			$student = Student::findByUserId(currentUserId());
			$data = $_POST;
			$errors = validate($data, [
				'category' => 'required',
				'subject' => 'required|max:200',
				'description' => 'required',
				'priority' => 'required|in:low,medium,high'
			]);
			if (!$student) {
				$errors['student'][] = 'Student profile not found.';
			}
			if (!empty($errors)) {
				redirectWithErrors('/student/complaints', $errors);
				return;
			}
			Complaint::create([
				'student_id' => $student['id'],
				'category' => $data['category'],
				'subject' => $data['subject'],
				'description' => $data['description'],
				'priority' => $data['priority']
			]);
			redirectWithSuccess('/student/complaints', 'Complaint submitted successfully.');
		}

		public static function createVisitor() {
			StudentMiddleware::handle();
			$student = Student::findByUserId(currentUserId());
			$data = $_POST;
			$errors = validate($data, [
				'visitor_name' => 'required|max:100',
				'contact' => 'numeric',
				'purpose' => 'required'
			]);
			if (!$student) {
				$errors['student'][] = 'Student profile not found.';
			}
			if (!empty($errors)) {
				redirectWithErrors('/student/visitors', $errors);
				return;
			}
			Visitor::create([
				'student_id' => $student['id'],
				'visitor_name' => $data['visitor_name'],
				'contact' => $data['contact'] ?? '',
				'purpose' => $data['purpose']
			]);
			redirectWithSuccess('/student/visitors', 'Visitor registered successfully.');
		}

		public static function updateProfile() {
			StudentMiddleware::handle();
			$student = Student::findByUserId(currentUserId());
			$data = $_POST;
			$errors = validate($data, [
				'full_name' => 'required|min:2',
				'phone' => 'numeric',
				'parent_contact' => 'numeric',
				'emergency_contact' => 'numeric'
			]);
			if (!$student) {
				$errors['student'][] = 'Student profile not found.';
			}
			if (!empty($errors)) {
				redirectWithErrors('/student/profile', $errors);
				return;
			}
			User::update(currentUserId(), [
				'full_name' => $data['full_name'],
				'phone' => $data['phone'] ?? ''
			]);
			Student::update($student['id'], [
				'parent_contact' => $data['parent_contact'] ?? '',
				'address' => $data['address'] ?? '',
				'emergency_contact' => $data['emergency_contact'] ?? ''
			]);
			$_SESSION['full_name'] = $data['full_name'];
			redirectWithSuccess('/student/profile', 'Profile updated successfully.');
		}

		public static function receipt($feeId) {
			StudentMiddleware::handle();
			$student = Student::findByUserId(currentUserId());
			$stmt = db()->prepare('SELECT f.* FROM fees f WHERE f.id = ? AND f.student_id = ? AND f.status = ?');
			$stmt->execute([$feeId, $student['id'] ?? 0, FEE_PAID]);
			$fee = $stmt->fetch();
			if (!$fee) {
				abort(404, 'Paid fee receipt not found');
			}
			header('Content-Type: text/html; charset=UTF-8');
			header('Content-Disposition: attachment; filename="fee-receipt-' . (int) $feeId . '.html"');
			echo '<!doctype html><html><head><meta charset="UTF-8"><title>Fee Receipt</title></head><body><h1>DormSync Fee Receipt</h1><p>Student: ' . htmlspecialchars($student['full_name']) . '</p><p>Amount: ₹' . number_format((float) $fee['amount'], 2) . '</p><p>Payment date: ' . htmlspecialchars($fee['paid_at'] ?? '-') . '</p><p>Payment method: ' . htmlspecialchars($fee['payment_method'] ?? '-') . '</p><p>Transaction ID: ' . htmlspecialchars($fee['transaction_id'] ?? '-') . '</p></body></html>';
			exit;
		}
}
