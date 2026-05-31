<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = MD5(:password)");
        $stmt->execute([':username' => $username, ':password' => $password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            // بارگذاری دسترسی‌های اختصاصی کاربر (فقط برای ادمین)
            if ($user['role'] === 'admin') {
                $stmt = $db->prepare("SELECT permission_key, permission_value FROM user_permissions WHERE user_id = :user_id");
                $stmt->execute([':user_id' => $user['id']]);
                $userPerms = $stmt->fetchAll();

                if (!empty($userPerms)) {
                    $_SESSION['limited_admin'] = true;
                    foreach ($userPerms as $perm) {
                        $_SESSION['permissions'][$perm['permission_key']] = $perm['permission_value'] == 1;
                    }
                }else {
                    // ادمین کامل - limited_admin را false قرار بده
                    $_SESSION['limited_admin'] = false;
                    }
            }

            // هدایت بر اساس نقش
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: user_dashboard.php');
            }
            exit;
        } else {
            $error = '❌ نام کاربری یا رمز عبور اشتباه است!';
        }
    } else {
        $error = '❌ لطفاً همه فیلدها را پر کنید!';
    }
}

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ورود به سیستم</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/login.css">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh;">
<div class="login-container">
    <h2>🔐 ورود به سیستم</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="نام کاربری" required>
        <input type="password" name="password" placeholder="رمز عبور" required>
        <button type="submit">ورود</button>
    </form>
</div>
</body>
</html>