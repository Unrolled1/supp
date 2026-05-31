<?php
session_start();
require_once 'db.php';
require_once 'assets/jdf.php';
require_once 'functions.php';

date_default_timezone_set('Asia/Tehran');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDB();
$message = '';

// گرفتن بخش‌ها و موضوعات
$departments = $db->query("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$topics = $db->query("SELECT * FROM topics WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// ثبت درخواست
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_ticket'])) {
    $fullname = htmlspecialchars($_POST['fullname']);
    $department_id = $_POST['department_id'];
    $subject = htmlspecialchars($_POST['subject']);
    $requestMessage = htmlspecialchars($_POST['message']);

    if ($fullname && $department_id && $subject && $requestMessage) {
        $trackingCode = 'TK-' . jdate('Ymd') . '-' . rand(1000, 9999);
        $stmt = $db->prepare("INSERT INTO tickets (tracking_code, user_id, fullname, department_id, subject, message, status, created_at) VALUES (:code, :uid, :name, :did, :sub, :msg, 'جدید', :date)");
        $stmt->execute([':code' => $trackingCode, ':uid' => $_SESSION['user_id'], ':name' => $fullname, ':did' => $department_id, ':sub' => $subject, ':msg' => $requestMessage, ':date' => now()]);
        $message = "✅ درخواست شما ثبت شد. کد پیگیری: " . fa_number($trackingCode);
    }
}

// حذف درخواست
if (isset($_POST['delete_ticket'])) {
    $ticket_id = $_POST['ticket_id'];
    $stmt = $db->prepare("DELETE FROM tickets WHERE id = :id AND user_id = :uid");
    $stmt->execute([':id' => $ticket_id, ':uid' => $_SESSION['user_id']]);
    $message = "✅ درخواست حذف شد";
}

// گرفتن درخواست‌های کاربر
$stmt = $db->prepare("SELECT t.*, d.name as department_name FROM tickets t LEFT JOIN departments d ON t.department_id = d.id WHERE t.user_id = :uid ORDER BY t.created_at DESC");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$myTickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>داشبورد کاربر</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/user-dashboard.css">
</head>
<body class="user-page">
<div class="container">
    <div class="header">
        <div><span>👤</span> خوش آمدید، <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></div>
        <div><span class="dynamic-time" id="liveClock"><?php echo fa_number(now()); ?></span><a href="logout.php" class="logout-btn">🚪 خروج</a></div>
    </div>

    <div class="form-box">
        <h2>📧 ثبت درخواست جدید</h2>
        <?php if($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group"><label>کد رایانه</label><input type="text" name="fullname" required></div>
            <div class="form-group"><label>بخش</label><select name="department_id" required><option value="">انتخاب کنید</option><?php foreach($departments as $d): ?><option value="<?php echo $d['id']; ?>">🏥 <?php echo htmlspecialchars($d['name']); ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>موضوع</label><select name="subject" required><option value="">انتخاب کنید</option><?php foreach($topics as $t): ?><option value="<?php echo htmlspecialchars($t['name']); ?>">📌 <?php echo htmlspecialchars($t['name']); ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>متن درخواست</label><textarea name="message" rows="5" required></textarea></div>
            <button type="submit">📨 ارسال درخواست</button>
        </form>
    </div>

    <div class="tickets-table"><h3>📋 درخواست‌های قبلی (<?php echo fa_number(count($myTickets)); ?>)</h3>
        <table><thead><tr><th>#</th><th>کد پیگیری</th><th>بخش</th><th>موضوع</th><th>وضعیت</th><th>تاریخ</th><th>عملیات</th></tr></thead>
            <tbody><?php $i=1; foreach($myTickets as $t): ?>
                <tr><td><?php echo fa_number($i); ?></td><td><?php echo fa_number(htmlspecialchars($t['tracking_code'])); ?></td>
                    <td><?php echo htmlspecialchars($t['department_name']??'-'); ?></td><td><?php echo htmlspecialchars($t['subject']); ?></td>
                    <td><span class="status-badge status-<?php echo $t['status']=='جدید'?'new':($t['status']=='در حال بررسی'?'review':'closed'); ?>"><?php echo $t['status']; ?></span></td>
                    <td class="date-ltr"><?php echo fa_number(htmlspecialchars($t['created_at'])); ?></td>
                    <td><form method="post" onsubmit="return confirm('حذف؟');"><input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>"><button type="submit" name="delete_ticket" class="delete-btn-table">🗑️</button></form></td></tr>
                <?php $i++; endforeach; ?></tbody></table></div>
</div>
<script>function updateClock(){fetch('get_time.php').then(r=>r.json()).then(d=>{let c=document.getElementById('liveClock');if(c)c.innerHTML='📅 '+d.datetime;});}setInterval(updateClock,1000);updateClock();</script>
</body>
</html>