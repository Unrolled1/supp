<?php
session_start();
require_once 'config/config.php';
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
$departments = $db->query("SELECT id,name FROM departments ORDER BY name ASC")->fetchAll();
$topics = $db->query("SELECT name FROM topics ORDER BY name ASC")->fetchAll();

// ============================================
// پردازش AJAX - ثبت درخواست
// ============================================

if (isset($_POST['add_ticket_ajax'])) {
$fullname = htmlspecialchars(trim($_POST['fullname']));
$department_id = filter_var($_POST['department_id'], FILTER_VALIDATE_INT);
$subject = htmlspecialchars(trim($_POST['subject']));
$message = htmlspecialchars(trim($_POST['message']));

if ($fullname && $department_id && $subject && $message) {
$trackingCode = 'TK-' . jdate('Ymd') . '-' . rand(1000, 9999);
$stmt = $db->prepare("INSERT INTO tickets 
    (tracking_code, user_id, fullname, department_id, subject, message, status, created_at) 
VALUES (:code, :uid, :name, :did, :sub, :msg, 'درحال بررسی', :date)");

$success = $stmt->execute([
    ':code' => $trackingCode,
    ':uid' => $_SESSION['user_id'],
    ':name' => $fullname,
    ':did' => $department_id,
    ':sub' => $subject,
    ':msg' => $message,
    ':date' => now()
]);
if ($success) {
$newId = $db->lastInsertId();
// دریافت اطلاعات کامل تیکت جدید
$stmt2 = $db->prepare("
                SELECT t.*, d.name as department_name 
                FROM tickets t 
                LEFT JOIN departments d ON t.department_id = d.id 
                WHERE t.id = :id
            ");
    $stmt2->execute([':id' => $newId]);
    $newTicket = $stmt2->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'ticket' => $newTicket,
        'tracking_code' => $trackingCode,
        'message' => 'درخواست با موفقیت ثبت شد'
    ]);
    exit;
             }

}
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'خطا در ثبت درخواست']);
    exit;
}
// ============================================
// پردازش AJAX - حذف درخواست
// ============================================

if (isset($_POST['delete_ticket_ajax'])) {
    $ticket_id = filter_var($_POST['ticket_id'], FILTER_VALIDATE_INT);

    $stmt = $db->prepare("DELETE FROM tickets WHERE id = :id AND user_id = :uid");
    $success = $stmt->execute([':id' => $ticket_id, ':uid' => $_SESSION['user_id']]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'id' => $ticket_id,
        'message' => $success ? 'درخواست با موفقیت حذف شد' : 'خطا در حذف درخواست'
    ]);
    exit;
}

// گرفتن درخواست‌های کاربر
$stmt = $db->prepare("SELECT t.*, d.name as department_name FROM tickets t LEFT JOIN departments d ON t.department_id = d.id WHERE t.user_id = :uid ORDER BY t.created_at DESC");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$myTickets = $stmt->fetchAll();

// پیام از session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>داشبورد کاربر</title>
    <?php load_assets(); ?>

</head>
<body class="user-page">
<div class="container">
    <div class="header">
        <div><span>👤</span> خوش آمدید <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></div>
        <div><span class="dynamic-time" id="liveClock"><?php echo fa_number(now()); ?></span><a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a></div>
    </div>

    <div class="add-card">
        <h2>📧 ثبت تیکت جدید</h2>
        <div id="form_message"></div>
        <?php if($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
        <form method="post" id="ticketForm">
            <div class="form-row">

             <div class="form-group">
                <label>کد رایانه</label>
                <input type="text" name="fullname" id="fullname" required>
             </div>

                <div class="form-group">
                <label>بخش</label>
                <select name="department_id" id="department_id" required>
                    <option value="">انتخاب کنید</option>
                    <?php foreach($departments as $d): ?>
                        <option value="<?php echo $d['id']; ?>">🏥 <?php echo htmlspecialchars($d['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                </div>

                <div class="form-group">
                    <label>موضوع</label>
                    <select name="subject" id="subject" required>
                        <option value="">انتخاب کنید</option>
                        <?php foreach($topics as $t): ?>
                            <option value="<?php echo htmlspecialchars($t['name']); ?>">📌 <?php echo htmlspecialchars($t['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>


            <div class="form-group">
                <label>متن درخواست</label>
                <textarea name="message" id="message" rows="5"></textarea>
            </div>
            <button type="button" id="submitTicket" class="sub-btn">📨 ارسال تیکت</button>
        </form>
    </div>

    <div class="tickets-table">
        <h3>📋 تیکت‌ها (<?php echo fa_number(count($myTickets)); ?>)</h3>
        <div id="tickets_container">
        <table>
            <thead>
            <tr>
                <th>ردیف</th>
                <th>کد پیگیری</th>
                <th>بخش</th>
                <th>موضوع</th>
                <th>متن درخواست</th>
                <th>وضعیت</th>
                <th>تاریخ</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody id="ticketsBody">
            <?php $i=1; foreach($myTickets as $t): ?>
                <tr>
                    <td><?php echo fa_number($i); ?></td>
                    <td><?php echo fa_number(htmlspecialchars($t['tracking_code'])); ?></td>
                    <td><?php echo htmlspecialchars($t['department_name']??'-'); ?></td>
                    <td><?php echo htmlspecialchars($t['subject']); ?></td>
                    <td><?php echo htmlspecialchars($t['message']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $t['status']=='جدید'?'new':($t['status']=='در حال بررسی'?'review':'closed'); ?>"><?php echo $t['status']; ?></span></td>
                    <td class="date-ltr"><?php echo fa_number(htmlspecialchars($t['created_at'])); ?></td>
                    <td>
                        <button class="delete-btn-table" onclick="deleteTicket(<?php echo $t['id']; ?>)">🗑️</button>
                    </td>
                </tr>
                <?php $i++; endforeach; ?>
            </tbody>
        </table>
        </div>
</div>

<script src="assets/js/user-dashboard.js"></script>
</body>
</html>