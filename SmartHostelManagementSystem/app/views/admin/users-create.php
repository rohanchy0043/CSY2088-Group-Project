<?php $errors = sessionErrors(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - DormSync</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; margin: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e7ebf2; padding: 18px 6%; }
        .topbar a { color: #2563eb; text-decoration: none; }
        main { margin: auto; max-width: 680px; padding: 38px 24px; }
        .panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 10px; padding: 28px; }
        h1 { font-size: 28px; margin: 0 0 8px; }
        .intro { color: #64748b; font-size: 14px; margin: 0 0 26px; }
        h2 { border-bottom: 1px solid #e7ebf2; color: #172b4d; font-size: 17px; margin: 26px 0 18px; padding-bottom: 12px; }
        h2:first-of-type { margin-top: 0; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 14px 0 6px; }
        input, select, button { border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; padding: 11px; width: 100%; }
        input:focus, select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); outline: 0; }
        button { background: #2563eb; border-color: #2563eb; color: #fff; cursor: pointer; font-weight: 600; margin-top: 24px; }
        .cancel { background: #fff; border-color: #cbd5e1; color: #334155; display: block; margin-top: 10px; text-align: center; }
        .error { color: #991b1b; font-size: 13px; margin: 8px 0; }
        [hidden] { display: none !important; }
    </style>
</head>
<body>
<header class="topbar"><a href="/admin/users">&larr; Back to Users</a></header>
<main>
    <section class="panel">
        <h1>Add User</h1>
        <p class="intro">Create a new user account directly from the administration panel.</p>
        <?php foreach ($errors as $fieldErrors): foreach ((array) $fieldErrors as $error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endforeach; endforeach; ?>
        <form method="post" action="/admin/user-store">
            <h2>User Information</h2>
            <label for="full_name">Full Name</label>
            <input id="full_name" name="full_name" placeholder="Enter full name" required>
            <label for="email">Email Address</label>
            <input id="email" name="email" type="email" placeholder="Enter email address" required>
            <label for="phone">Phone Number</label>
            <input id="phone" name="phone" placeholder="Enter phone number">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="student">Student</option>
                <option value="warden">Warden</option>
                <option value="admin">Admin</option>
            </select>
            <section id="studentInformation">
                <h2>Student Information</h2>
                <label for="student_id">Student ID</label>
                <input id="student_id" name="student_id" placeholder="Enter student ID">
            </section>
            <h2>Password</h2>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter password" required>
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm password" required>
            <button type="submit">Create User</button>
            <a class="cancel" href="/admin/users">Cancel</a>
        </form>
    </section>
</main>
<script>
const role = document.getElementById('role');
const studentInformation = document.getElementById('studentInformation');
const studentId = document.getElementById('student_id');
function updateRoleFields() {
    const isStudent = role.value === 'student';
    studentInformation.hidden = !isStudent;
    studentId.required = isStudent;
}
role.addEventListener('change', updateRoleFields);
updateRoleFields();
</script>
</body>
</html>
