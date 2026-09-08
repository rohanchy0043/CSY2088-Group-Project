<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Warden.php';
require_once __DIR__ . '/../models/WardenInvitation.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/response.php';   // <-- ADD THIS LINE

class AuthController {
    public static function showLanding() {
        require __DIR__ . '/../../public/landing.php';
    }

    public static function showLogin() {
        view('auth/login');
    }

    public static function showRegister() {
        $type = $_GET['type'] ?? null;
        if (!in_array($type, ['student', 'warden'], true)) {
            $type = null;
        }
        view('auth/register', compact('type'));
    }

    public static function login() {
        self::ensureAuthSchema();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $errors = validate($_POST, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            redirectWithErrors('/login.php', $errors);
            return;
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            redirectWithError('/login.php', 'Invalid email or password');
            return;
        }

        if (($user['account_status'] ?? 'approved') !== 'approved') {
            redirectWithError('/login.php', 'This account is not active. Contact an administrator.');
            return;
        }

        loginUser($user);
        $redirect = sessionFlash('login_redirect') ?? getRedirectUrl();
        redirect($redirect);
    }

    public static function register() {
        self::ensureAuthSchema();
        $data = $_POST;
        $type = $data['account_type'] ?? '';

        if (!in_array($type, ['student', 'warden'], true)) {
            redirectWithError('/register.php', 'Please choose an account type.');
            return;
        }

        $rules = [
            'full_name' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'numeric'
        ];

        if ($type === 'student') {
            $rules['student_id'] = 'required|unique:students';
        } else {
            $rules['invitation_code'] = 'required';
        }

        $errors = validate($data, $rules);

        if ($type === 'warden' && empty($errors)) {
            $invitation = WardenInvitation::findValid($data['invitation_code'], $data['email']);
            if (!$invitation) {
                $errors['invitation_code'][] = 'The invitation code is invalid, expired, used, or does not match this email.';
            }
        }

        if (!empty($errors)) {
            redirectWithErrors('/register.php?type=' . $type, $errors);
            return;
        }

        $username = strtolower(preg_replace('/[^a-z0-9]+/i', '.', strstr($data['email'], '@', true)));
        $username = trim($username, '.') ?: $type;
        $baseUsername = $username;
        $suffix = 1;
        while (User::findByUsername($username)) {
            $username = $baseUsername . $suffix++;
        }

        db()->beginTransaction();
        try {
            $userId = User::create([
                'username' => $username,
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $type,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'] ?? '',
                'account_status' => 'approved'
            ]);

            if ($type === 'student') {
                Student::create([
                    'user_id' => $userId,
                    'student_id' => $data['student_id'],
                    'parent_contact' => '',
                    'address' => '',
                    'emergency_contact' => ''
                ]);
            } else {
                Warden::create(['user_id' => $userId]);
                WardenInvitation::markUsed($invitation['id'], $userId);
            }
            db()->commit();
        } catch (Throwable $exception) {
            db()->rollBack();
            redirectWithError('/register.php?type=' . $type, 'Unable to create the account. Please try again.');
            return;
        }

        $message = $type === 'warden'
            ? 'Warden account created. You can now login.'
            : 'Student account created. Please login.';
        redirectWithSuccess('/login.php', $message);
    }

    private static function ensureAuthSchema() {
        db()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS account_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved'");
        db()->exec("CREATE TABLE IF NOT EXISTS warden_invitations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invited_email VARCHAR(100) NOT NULL,
            code_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            used_by INT NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (used_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )");
    }

    public static function logout() {
        logoutUser();
        redirect('/login.php');
    }
}
?>