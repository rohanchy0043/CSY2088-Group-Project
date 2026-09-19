<?php
require_once __DIR__ . '/../models/Meal.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/StudentMidddleware.php';
require_once __DIR__ . '/../middleware/WardenMiddleware.php';
require_once __DIR__ . '/../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validation.php';

class MealController {
    private static function mealTypes() { return ['breakfast', 'lunch', 'dinner']; }

    public static function student() {
        StudentMiddleware::handle();
        Meal::ensureTables();
        $student = Student::findByUserId(currentUserId());
        $studentId = $student['id'] ?? 0;
        view('student/meals', [
            'menus' => Meal::menus(),
            'attendance' => Meal::attendanceForStudent($studentId),
            'feedback' => Meal::feedback($studentId),
            'complaints' => Meal::complaints($studentId)
        ]);
    }

    public static function feedbackStore() {
        StudentMiddleware::handle();
        $student = Student::findByUserId(currentUserId());
        $data = $_POST;
        $errors = validate($data, ['meal_type' => 'required|in:breakfast,lunch,dinner', 'feedback_date' => 'required|date', 'rating' => 'required|numeric', 'taste_rating' => 'required|numeric', 'quality_rating' => 'required|numeric', 'quantity_rating' => 'required|numeric', 'hygiene_rating' => 'required|numeric', 'comment' => 'max:500']);
        foreach (['rating', 'taste_rating', 'quality_rating', 'quantity_rating', 'hygiene_rating'] as $ratingField) {
            if ((int) ($data[$ratingField] ?? -1) < 0 || (int) ($data[$ratingField] ?? -1) > 5) { $errors[$ratingField][] = 'Rating must be between 0 and 5.'; }
        }
        if (!$student) { $errors['student'][] = 'Student profile not found.'; }
        if (!empty($errors)) { redirectWithErrors('/student/meals', $errors); return; }
        Meal::addFeedback($data, $student['id']);
        redirectWithSuccess('/student/meals', 'Meal feedback submitted.');
    }

    public static function complaintStore() {
        StudentMiddleware::handle();
        $student = Student::findByUserId(currentUserId());
        $data = $_POST;
        $data['subject'] = trim(($data['meal_type'] ?? '') . ' food complaint');
        $errors = validate($data, ['meal_type' => 'required|in:breakfast,lunch,dinner', 'category' => 'required|max:80', 'complaint_date' => 'required|date', 'description' => 'required']);
        if (!$student) { $errors['student'][] = 'Student profile not found.'; }
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK || $_FILES['photo']['size'] > 5 * 1024 * 1024) {
                $errors['photo'][] = 'Photo must be smaller than 5 MB.';
            } else {
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['photo']['tmp_name']);
                if (!isset($allowed[$mime])) {
                    $errors['photo'][] = 'Only JPG, PNG, or WEBP photos are allowed.';
                } else {
                    $directory = PUBLIC_PATH . '/uploads/meal-complaints';
                    if (!is_dir($directory)) { mkdir($directory, 0755, true); }
                    $filename = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $directory . '/' . $filename)) {
                        $photoPath = '/uploads/meal-complaints/' . $filename;
                    } else {
                        $errors['photo'][] = 'Unable to save the uploaded photo.';
                    }
                }
            }
        }
        if (!empty($errors)) { redirectWithErrors('/student/meals', $errors); return; }
        $data['photo_path'] = $photoPath;
        Meal::addComplaint($data, $student['id']);
        redirectWithSuccess('/student/meals', 'Food complaint submitted.');
    }

    public static function warden() {
        WardenMiddleware::handle();
        Meal::ensureTables();
        view('warden/meals', ['menus' => Meal::menus(), 'students' => Student::all(), 'attendance' => Meal::attendance(), 'feedback' => Meal::feedback(), 'complaints' => Meal::complaints()]);
    }

    public static function menuStore() {
        WardenMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['meal_type' => 'required|in:breakfast,lunch,dinner', 'menu_date' => 'required|date', 'items' => 'required']);
        if (!empty($errors)) { redirectWithErrors('/warden/meals', $errors); return; }
        Meal::saveMenu($data, currentUserId());
        redirectWithSuccess('/warden/meals', 'Meal menu saved.');
    }

    public static function attendanceStore() {
        WardenMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['student_id' => 'required|numeric', 'attendance_date' => 'required|date', 'meal_type' => 'required|in:breakfast,lunch,dinner', 'status' => 'required|in:present,absent']);
        if (empty($errors['student_id']) && !Student::find((int) $data['student_id'])) { $errors['student_id'][] = 'Selected student does not exist.'; }
        if (!empty($errors)) { redirectWithErrors('/warden/meals', $errors); return; }
        Meal::markAttendance((int) $data['student_id'], $data['attendance_date'], $data['meal_type'], $data['status'], currentUserId());
        redirectWithSuccess('/warden/meals', 'Meal attendance saved.');
    }

    public static function complaintUpdate() {
        WardenMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['id' => 'required|numeric', 'status' => 'required|in:pending,in-progress,resolved', 'resolution_notes' => 'max:500']);
        if (!empty($errors)) { redirectWithErrors('/warden/meals', $errors); return; }
        Meal::updateComplaint((int) $data['id'], $data['status'], $data['resolution_notes'] ?? '', currentUserId());
        redirectWithSuccess('/warden/meals', 'Food complaint updated.');
    }

    public static function admin() {
        AdminMiddleware::handle();
        Meal::ensureTables();
        view('admin/meals', ['menus' => Meal::menus(), 'attendance' => Meal::attendance(), 'feedback' => Meal::feedback(), 'complaints' => Meal::complaints()]);
    }

    public static function adminMenuStore() {
        AdminMiddleware::handle();
        $data = $_POST;
        $errors = validate($data, ['meal_type' => 'required|in:breakfast,lunch,dinner', 'menu_date' => 'required|date', 'items' => 'required']);
        if (!empty($errors)) { redirectWithErrors('/admin/meals', $errors); return; }
        Meal::saveMenu($data, currentUserId());
        redirectWithSuccess('/admin/meals', 'Meal menu saved.');
    }

    public static function adminAttendance() {
        AdminMiddleware::handle();
        view('admin/meal-attendance', ['attendance' => Meal::attendance()]);
    }

}
