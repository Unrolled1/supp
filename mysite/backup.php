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

if (!isAdmin()) {
    header('Location: user_dashboard.php');
    exit;
}

$db = getDB();

// ============================================
// تنظیمات پشتیبان‌گیری
// ============================================

$backupDir = __DIR__ . '/backups/';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// ============================================
// لیست فایل‌های پشتیبان
// ============================================

$backupFiles = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {

            $fileTime = filemtime($backupDir . $file);
            $jalaliDate = jdate('Y-m-d H:i', $fileTime);
            $backupFiles[] = [
                'name' => $file,
                'size' => filesize($backupDir . $file),
                'date' => $jalaliDate
            ];
        }
    }
    usort($backupFiles, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// ============================================
// پردازش - پشتیبان‌گیری
// ============================================

if (isset($_POST['create_backup'])) {
    try {
        $tables = [];
        $stmt = $db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $backupName = 'backup_' . jdate('Y-m-d_H-i-s') . '.sql';
        $backupPath = $backupDir . $backupName;

        $sql = "-- ============================================\n";
        $sql .= "-- پشتیبان‌گیری از دیتابیس\n";
        $sql .= "-- تاریخ: " . jdate('Y-m-d H:i:s') . "\n";
        $sql .= "-- ============================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $stmt = $db->query("SHOW CREATE TABLE `$table`");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $row['Create Table'] . ";\n\n";

            $stmt = $db->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $columns = array_keys($rows[0]);
                $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";

                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = "(" . implode(', ', $rowValues) . ")";
                }
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        file_put_contents($backupPath, $sql);


        $_SESSION['backup_success'] = "✅ پشتیبان‌گیری با موفقیت انجام شد. فایل: " . htmlspecialchars($backupName);

        header('Location: backup.php#backup-list');
        exit;

    } catch (Exception $e) {
        $_SESSION['backup_error'] = "❌ خطا در پشتیبان‌گیری: " . $e->getMessage();

        header('Location: backup.php#backup-list');
        exit;
    }
}

// ============================================
// پردازش - دانلود پشتیبان
// ============================================

if (isset($_GET['download'])) {
    $fileName = basename($_GET['download']);
    $filePath = $backupDir . $fileName;

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        $_SESSION['backup_error'] = "❌ فایل مورد نظر وجود ندارد";

        header('Location: backup.php#backup-list');
        exit;
    }
}

// ============================================
// پردازش - حذف پشتیبان
// ============================================

if (isset($_POST['delete_backup'])) {
    $fileName = basename($_POST['file_name']);
    $filePath = $backupDir . $fileName;

    if (file_exists($filePath)) {
        if (@unlink($filePath)) {
            if (file_exists($filePath . '.gz')) {
                @unlink($filePath . '.gz');
            }
            $_SESSION['backup_success'] = "✅ فایل پشتیبان با موفقیت حذف شد";
        } else {
            $_SESSION['backup_error'] = "❌ خطا در حذف فایل";
        }
    } else {
        $_SESSION['backup_error'] = "❌ فایل مورد نظر وجود ندارد";
    }

    header('Location: backup.php#backup-list');
    exit;
}

// ============================================
// پردازش - بازیابی (Restore)
// ============================================

if (isset($_POST['restore_backup']) && isset($_FILES['restore_file']) && $_FILES['restore_file']['error'] === UPLOAD_ERR_OK) {
    $uploadedFile = $_FILES['restore_file'];
    $fileExtension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);

    if ($fileExtension !== 'sql') {
        $_SESSION['backup_error'] = "❌ فقط فایل‌های با پسوند .sql  مجاز هستند";
        header('Location: backup.php#backup-list');
        exit;
    }

    try {
        $content = file_get_contents($uploadedFile['tmp_name']);

        $db->beginTransaction();

        $queries = explode(";\n", $content);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query) && $query !== '') {
                $db->exec($query);
            }
        }

        $db->commit();
        $_SESSION['backup_success'] = "✅ دیتابیس با موفقیت بازیابی شد";

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['backup_error'] = "❌ خطا در بازیابی: " . $e->getMessage();
    }

    header('Location: backup.php#backup-list');
    exit;
}

// ============================================
// گرفتن پیام‌ها از Session
// ============================================

$successMessage = '';
$errorMessage = '';

if (isset($_SESSION['backup_success'])) {
    $successMessage = $_SESSION['backup_success'];
    unset($_SESSION['backup_success']);
}

if (isset($_SESSION['backup_error'])) {
    $errorMessage = $_SESSION['backup_error'];
    unset($_SESSION['backup_error']);
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پشتیبان‌گیری و بازیابی</title>
    <?php load_assets(); ?>
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
                <span class="clock-display" id="liveClock"> <?php echo fa_number(now()); ?></span>
                <a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a>
            </div>
        </div>

        <div class="main-title">
            <h1>💾 پشتیبان‌گیری و بازیابی</h1>
        </div>

        <!-- ============================================ -->
        <!-- بخش پشتیبان‌گیری -->
        <!-- ============================================ -->
        <div class="backup-card">
            <h2>🔄 ایجاد پشتیبان جدید</h2>
            <p class="backup-desc">با کلیک روی دکمه زیر، یک فایل پشتیبان کامل از دیتابیس ایجاد می‌شود.</p>
            <form method="post">
                <button type="submit" name="create_backup" class="btn-backup">
                    💾 ایجاد پشتیبان
                </button>
            </form>
        </div>

        <!-- ============================================ -->
        <!-- بخش بازیابی -->
        <!-- ============================================ -->
        <div class="restore-card">
            <h2>🔄 بازیابی دیتابیس</h2>
            <p class="restore-desc">فایل پشتیبان (.sql ) را انتخاب کنید تا دیتابیس بازیابی شود.</p>
            <p class="restore-warning">⚠️ توجه: بازیابی اطلاعات فعلی را بازنویسی می‌کند!</p>
            <form method="post" enctype="multipart/form-data" id="restoreForm">
                <div class="file-upload-wrapper">
                    <input type="file" name="restore_file" id="restore_file" accept=".sql" required>
                    <label for="restore_file" class="file-label">📁 انتخاب فایل پشتیبان</label>
                </div>
                <button type="submit" name="restore_backup" class="btn-restore" onclick="return confirmRestore()">
                    🔄 بازیابی
                </button>
            </form>
        </div>

        <!-- ============================================ -->
        <!-- لیست پشتیبان‌ها -->
        <!-- ============================================ -->
        <div class="backup-list-card" id="backup-list">
            <h2>📋 لیست پشتیبان‌ها</h2>

            <?php if (empty($backupFiles)): ?>
                <p class="empty-list">💾 هیچ فایل پشتیبان‌گیری وجود ندارد</p>
            <?php else: ?>
                <table class="backup-table data-table">
                    <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>نام فایل</th>
                        <th>حجم</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; foreach ($backupFiles as $file): ?>
                        <tr>
                            <td><?php echo fa_number($i); ?></td>
                            <td><?php echo htmlspecialchars($file['name']); ?></td>
                            <td><?php echo fa_number(number_format($file['size'] / 1024, 1)) . ' KB'; ?></td>
                            <td class="date-ltr"><?php echo fa_number($file['date']); ?></td>
                            <td class="action-buttons">
                                <a href="backup.php?download=<?php echo urlencode($file['name']); ?>" class="btn-download" title="دانلود">⬇️</a>

                                <form method="post" style="display: inline;" onsubmit="return confirmDeleteForm('<?php echo htmlspecialchars($file['name']); ?>')">
                                    <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($file['name']); ?>">
                                    <button type="submit" name="delete_backup" class="btn-delete" title="حذف">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function confirmDelete(fileName, buttonElement) {
        event.preventDefault(); // جلوگیری از رفتار پیش‌فرض
        Swal.fire({
            title: 'آیا مطمئن هستید؟',
            text: 'فایل "' + fileName + '" حذف خواهد شد!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'لغو'
        }).then((result) => {
            if (result.isConfirmed) {
                // پیدا کردن فرم و ارسال
                const form = buttonElement.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }

    function confirmRestore() {
        const fileInput = document.getElementById('restore_file');
        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire({
                title: 'خطا!',
                text: 'لطفاً یک فایل پشتیبان انتخاب کنید.',
                icon: 'warning',
                confirmButtonColor: '#ffc107'
            });
            return false;
        }

        Swal.fire({
            title: '⚠️ هشدار!',
            text: 'بازیابی دیتابیس اطلاعات فعلی را بازنویسی می‌کند. آیا مطمئن هستید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'بله، بازیابی شود',
            cancelButtonText: 'لغو'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('restoreForm').submit();
            }
        });
        return false;
    }

</script>

<!-- ============================================ -->
<!-- پیام‌های موفقیت/خطا با SweetAlert -->
<!-- ============================================ -->

<?php if ($successMessage): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '✅ موفق!',
                text: '<?php echo addslashes($successMessage); ?>',
                icon: 'success',
                timer: 3000,
                showConfirmButton: true,
                confirmButtonColor: '#48bb78'
            });
        });
    </script>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '❌ خطا!',
                text: '<?php echo addslashes($errorMessage); ?>',
                icon: 'error',
                timer: 4000,
                showConfirmButton: true,
                confirmButtonColor: '#e53e3e'
            });
        });
    </script>
<?php endif; ?>

</body>
</html>