<?php
session_start();
require_once 'db.php';
require_once 'assets/jdf.php';
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
    <title>IMS</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/login.css">
</head>
<body class="login-page">
<div class="login-wrapper">
    <div class="login-box">
        <div class="login-header">
            <h1>🔐 سامانه تیکت</h1>
            <p>بیمارستان حضرت ابوالفضل میناب</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="form-group">
                <label>👤 نام کاربری</label>
                <input type="text" name="username"  required autofocus>
            </div>

            <div class="form-group">
                <label>🔑 رمز عبور</label>
                <input type="password" name="password"  required>
            </div>

            <button type="submit" class="login-btn"> ورود</button>
        </form>

        <div class="login-footer">
            <p>سیستم مدیریت یکپارچه(IMS)</p>
            <small>© <?php echo jdate('Y'); ?> - تمامی حقوق محفوظ است</small>
        </div>
    </div>
</div>
<script>
    // ============================================
    // ارسال فرم با کلید Enter
    // ============================================

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');
        const username = document.getElementById('username');
        const password = document.getElementById('password');

        // وقتی کاربر Enter بزنه
        function handleKeyPress(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.submit();
            }
        }

        // اضافه کردن event listener به هر دو فیلد
        if (username) {
            username.addEventListener('keypress', handleKeyPress);
        }

        if (password) {
            password.addEventListener('keypress', handleKeyPress);
        }

        // فوکوس خودکار روی فیلد نام کاربری
        if (username) {
            setTimeout(() => {
                username.focus();
            }, 100);
        }
    });
</script>
</body>
</html>