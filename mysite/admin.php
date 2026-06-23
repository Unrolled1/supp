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

// فقط ادمین می‌تواند وارد این صفحه شود
if (!isAdmin()) {
    header('Location: user_dashboard.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';


$stmt = $db->query("
    SELECT t.*, u.username, u.fullname as user_fullname, d.name as department_name
    FROM tickets t 
    LEFT JOIN users u ON t.user_id = u.id 
    LEFT JOIN departments d ON t.department_id = d.id
    ORDER BY t.created_at DESC
");
$tickets = $stmt->fetchAll();

$totalCount = count($tickets);
$newCount = 0;
foreach ($tickets as $t) {
    if ($t['status'] == 'جدید') $newCount++;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status']) && canEditTickets()) {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['new_status'];

    $updateStmt = $db->prepare("UPDATE tickets SET status = :status WHERE id = :id");
    if ($updateStmt->execute([':status' => $new_status, ':id' => $ticket_id])) {
        $successMessage = "✅ وضعیت تیکت با موفقیت تغییر کرد";
    } else {
        $errorMessage = "❌ خطا در تغییر وضعیت";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket']) && canDeleteTickets()) {
    $ticket_id = $_POST['ticket_id'];

    $deleteStmt = $db->prepare("DELETE FROM tickets WHERE id = :id");
    if ($deleteStmt->execute([':id' => $ticket_id])) {
        $successMessage = "✅ تیکت با موفقیت حذف شد";
    } else {
        $errorMessage = "❌ خطا در حذف تیکت";
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پنل مدیریت درخواست‌ها</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/admin.css">
</head>
<body>
<div class="admin-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="main-header">
            <div class="user-info">
                <span>👨‍💼</span>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
            </div>
            <div>
                <span class="clock-display" id="liveClock">📅<?php echo fa_number(now()); ?></span>
                <a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a>
            </div>
        </div>

        <div class="main-title">
            <h1>📋 پنل مدیریت درخواست‌ها</h1>
        </div>

        <div class="stats">
            <div class="stat-box">
                <div class="stat-number"><?php echo fa_number($totalCount); ?></div>
                <div class="stat-label">📊 کل درخواست‌ها</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo fa_number($newCount); ?></div>
                <div class="stat-label">🆕 درخواست‌های جدید</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo fa_number($totalCount - $newCount); ?></div>
                <div class="stat-label">✅ بررسی شده</div>
            </div>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <div class="tickets-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>کد پیگیری</th>
                    <th>بخش</th>
                    <th>نام کاربر</th>
                    <th>موضوع</th>
                    <th>متن درخواست</th>
                    <th>وضعیت</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;">📭 هیچ درخواستی ثبت نشده است</td></tr>
                <?php else: ?>
                    <?php $row_num = 1; ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <?php $statusClass = match($ticket['status']) {
                            'جدید' => 'status-new',
                            'در حال بررسی' => 'status-review',
                            'پاسخ داده شده' => 'status-answered',
                            default => 'status-closed'
                        }; ?>
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo fa_number(htmlspecialchars($ticket['tracking_code'])); ?></td>
                            <td>🏥 <?php echo htmlspecialchars($ticket['department_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($ticket['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                            <td class="message-cell"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></td>
                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $ticket['status']; ?></span></td>
                            <td class="date-ltr"><?php echo fa_number(htmlspecialchars($ticket['created_at'])); ?></td>

                            <td class="action-buttons">
                                <?php if (canEditTickets()): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <select name="new_status">
                                            <option value="در حال بررسی">در حال بررسی</option>
                                            <option value="پاسخ داده شده">پاسخ داده شده</option>
                                            <option value="بسته شده">بسته شده</option>
                                        </select>
                                        <button type="submit" name="change_status" class="status-btn-table">✏️ تغییر</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (canDeleteTickets()): ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('آیا از حذف این درخواست مطمئن هستید؟');">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <button type="submit" name="delete_ticket" class="delete-btn-table">🗑️ حذف</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $row_num++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>