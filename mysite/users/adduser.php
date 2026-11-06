<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('HTTP/1.0 403 Forbidden');
    exit("دسترسی غیرمجاز");
}

// تنظیمات پایگاه داده
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mysite');

// ایجاد توکن CSRF اگر وجود ندارد
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}

// ================== پردازش درخواست‌های AJAX ==================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // بررسی توکن CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status'=>'error', 'message'=>'توکن امنیتی نامعتبر است']);
        exit;
    }

    // اضافه کردن کاربر
    if ($_POST['action'] == 'add') {
        try {
            $user = trim($_POST['user'] ?? '');
            $pass = trim($_POST['pass'] ?? '');

            if (!$user || !$pass) {
                echo json_encode(['status'=>'error', 'message'=>'نام کاربری و رمز عبور نمی‌توانند خالی باشند']);
                exit;
            }

            // بررسی وجود کاربر
            $stmt = $pdo->prepare("SELECT ID FROM users WHERE user = ?");
            $stmt->execute([$user]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status'=>'error', 'message'=>'این نام کاربری قبلا ثبت شده']);
                exit;
            }

            // درج کاربر جدید
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (user, pass) VALUES (?, ?)");
            $stmt->execute([$user, $hashed_pass]);

            echo json_encode([
                'status' => 'success',
                'id' => $pdo->lastInsertId(),
                'user' => htmlspecialchars($user, ENT_QUOTES, 'UTF-8')
            ]);

        } catch(PDOException $e) {
            echo json_encode(['status'=>'error', 'message'=>'خطا در ثبت کاربر: ' . $e->getMessage()]);
        }
        exit;
    }

    // حذف کاربر
    if ($_POST['action'] == 'delete') {
        try {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

            if (!$id || $id <= 0) {
                echo json_encode(['status'=>'error', 'message'=>'شناسه نامعتبر']);
                exit;
            }

            // جلوگیری از حذف خود کاربر
            $current_user = $_SESSION['username'];
            $stmt = $pdo->prepare("SELECT user FROM users WHERE ID = ?");
            $stmt->execute([$id]);
            $user_to_delete = $stmt->fetchColumn();

            if ($user_to_delete === false) {
                echo json_encode(['status'=>'error', 'message'=>'کاربر یافت نشد']);
                exit;
            }

            if ($user_to_delete === $current_user) {
                echo json_encode(['status'=>'error', 'message'=>'نمی‌توانید خودتان را حذف کنید']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE ID = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status'=>'success']);
            } else {
                echo json_encode(['status'=>'error', 'message'=>'خطا در حذف کاربر']);
            }

        } catch(PDOException $e) {
            echo json_encode(['status'=>'error', 'message'=>'خطا در حذف کاربر: ' . $e->getMessage()]);
        }
        exit;
    }
}

// ================== گرفتن همه کاربران ==================
try {
    $stmt = $pdo->query("SELECT ID, user, created_at FROM users ORDER BY ID DESC");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $users = [];
    $error = "خطا در دریافت لیست کاربران: " . $e->getMessage();
}
?>

<div class="add-user-container">
    <form id="add-user-form">
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="form-group">
            <label for="add-user-user">نام کاربری</label>
            <input type="text" id="add-user-user" name="user"
                   required minlength="3" maxlength="50">
        </div>

        <div class="form-group">
            <label for="add-user-pass">رمز عبور</label>
            <input type="password" id="add-user-pass" name="pass"
                   required minlength="6">
        </div>

        <button type="submit" id="add-user-btn">ثبت کاربر جدید</button>
    </form>

    <div id="add-user-msg" style="margin-top:10px; min-height: 20px;"></div>

    <h3>لیست کاربران</h3>

    <?php if (isset($error)): ?>
        <div style="color: red; padding: 10px; margin-bottom: 10px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <table border="1" width="100%" style="text-align:center; border-collapse: collapse;" id="users-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>نام کاربری</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($users as $u): ?>
            <tr data-id="<?php echo $u['ID']; ?>">
                <td><?php echo htmlspecialchars($u['ID']); ?></td>
                <td><?php echo htmlspecialchars($u['user']); ?></td>
                <td>
                    <?php if ($u['user'] !== $_SESSION['username']): ?>
                        <button class="delete-user-btn" data-id="<?php echo $u['ID']; ?>">حذف</button>
                    <?php else: ?>
                        <span style="color: gray;">(کاربر فعلی)</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div id="no-results" style="display: none; text-align: center; padding: 20px;">
        کاربری یافت نشد
    </div>
</div>

<script>
    // ذخیره توکن CSRF
    const csrfToken = document.getElementById('csrf_token').value;

    // =================== اضافه کردن کاربر ===================
    document.getElementById('add-user-form').addEventListener('submit', function(e){
        e.preventDefault();

        const user = document.getElementById('add-user-user').value.trim();
        const pass = document.getElementById('add-user-pass').value.trim();
        const msg = document.getElementById('add-user-msg');
        const submitBtn = document.getElementById('add-user-btn');

        if(!user || !pass){
            showMessage(msg, 'نام کاربری و رمز عبور نمی‌توانند خالی باشند', 'error');
            return;
        }

        // غیرفعال کردن دکمه
        submitBtn.disabled = true;
        submitBtn.textContent = 'در حال ثبت...';

        const formData = new URLSearchParams();
        formData.append('action', 'add');
        formData.append('user', user);
        formData.append('pass', pass);
        formData.append('csrf_token', csrfToken);

        fetch(window.location.href, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData.toString()
        })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success'){
                    showMessage(msg, 'کاربر با موفقیت اضافه شد', 'success');

                    // اضافه کردن ردیف جدید به جدول
                    const tbody = document.querySelector("#users-table tbody");
                    const tr = document.createElement("tr");
                    tr.setAttribute("data-id", data.id);
                    tr.innerHTML = `<td>${data.id}</td>
                    <td>${escapeHtml(data.user)}</td>
                    <td><button class="delete-user-btn" data-id="${data.id}">حذف</button></td>`;
                    tbody.prepend(tr);

                    // پاک کردن فرم
                    document.getElementById('add-user-form').reset();
                } else {
                    showMessage(msg, data.message, 'error');
                }
            })
            .catch(error => {
                showMessage(msg, 'خطا در ارتباط با سرور', 'error');
                console.error('Error:', error);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'ثبت کاربر جدید';
            });
    });

    // =================== حذف کاربر ===================
    document.getElementById('users-table').addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-user-btn');

        if (deleteBtn) {
            e.preventDefault();

            if (!confirm('آیا مطمئن هستید که می‌خواهید این کاربر را حذف کنید؟')) {
                return;
            }

            const id = deleteBtn.dataset.id;
            const row = deleteBtn.closest('tr');

            // ذخیره متن اصلی دکمه
            const originalText = deleteBtn.textContent;

            // غیرفعال کردن دکمه
            deleteBtn.disabled = true;
            deleteBtn.textContent = 'در حال حذف...';

            const formData = new URLSearchParams();
            formData.append('action', 'delete');
            formData.append('id', id);
            formData.append('csrf_token', csrfToken);

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('خطا در شبکه');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // حذف ردیف با انیمیشن
                        row.style.transition = 'opacity 0.5s';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();

                            // بررسی اگر جدول خالی شد
                            if (document.querySelectorAll('#users-table tbody tr').length === 0) {
                                document.getElementById('no-results').style.display = 'block';
                            }
                        }, 500);
                    } else {
                        alert(data.message);
                        // بازیابی دکمه
                        deleteBtn.disabled = false;
                        deleteBtn.textContent = originalText;
                    }
                })
                .catch(error => {
                    alert('خطا در ارتباط با سرور: ' + error.message);
                    console.error('Error:', error);
                    // بازیابی دکمه
                    deleteBtn.disabled = false;
                    deleteBtn.textContent = originalText;
                });
        }
    });

    // =================== توابع کمکی ===================
    function showMessage(element, message, type) {
        element.style.color = type === 'error' ? 'red' : 'green';
        element.textContent = message;

        // پاک کردن پیام بعد از 3 ثانیه
        setTimeout(() => {
            element.textContent = '';
        }, 3000);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // اضافه کردن استایل
    const style = document.createElement('style');
    style.textContent = `
    .add-user-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
        font-family: Tahoma, Arial, sans-serif;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    button {
        background: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    button:hover {
        background: #45a049;
    }
    button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .delete-user-btn {
        background: #f44343;
    }
    .delete-user-btn:hover {
        background: #da190b;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: white;
    }
    th {
        background: #4CAF50;
        color: white;
        padding: 12px;
    }
    td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }
    tr:hover {
        background: #f5f5f5;
    }
    #no-results {
        color: #666;
        font-style: italic;
        padding: 20px;
    }
    #add-user-msg {
        padding: 10px;
        border-radius: 4px;
    }
`;
    document.head.appendChild(style);
</script>