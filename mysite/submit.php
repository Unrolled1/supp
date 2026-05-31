<?php
// ذخیره درخواست در فایل JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = htmlspecialchars($_POST['fullname']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    $errors = [];

    if (empty($fullname)) $errors[] = "نام و نام خانوادگی الزامی است";
    if (empty($subject)) $errors[] = "موضوع الزامی است";
    if (empty($message)) $errors[] = "متن درخواست الزامی است";

    if (empty($errors)) {
        $newRequest = [
            'id' => uniqid(),
            'time' => date('Y-m-d H:i:s'),
            'fullname' => $fullname,
            'subject' => $subject,
            'message' => $message,
            'status' => 'جدید'
        ];

        $file = 'requests.json';
        $requests = [];

        if (file_exists($file)) {
            $json = file_get_contents($file);
            $requests = json_decode($json, true) ?? [];
        }

        array_unshift($requests, $newRequest);
        file_put_contents($file, json_encode($requests, JSON_PRETTY_PRINT));

        $success = "✅ درخواست شما با موفقیت ثبت شد. کد پیگیری: " . $newRequest['id'];
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فرم پشتیبانی</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Tahoma;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
        }
        h2 { text-align: center; margin-bottom: 1.5rem; }
        label { display: block; margin-top: 1rem; font-weight: bold; }
        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            margin-top: 0.25rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: Tahoma;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            margin-top: 1.5rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background: #5a67d8; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 8px; margin-top: 1rem; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; margin-top: 1rem; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h2>📧 فرم پشتیبانی</h2>

    <?php if (isset($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post">
        <label>نام و نام خانوادگی:</label>
        <input type="text" name="fullname" required>

        <label>موضوع:</label>
        <select name="subject" required>
            <option value="">انتخاب کنید...</option>
            <option value="فنی">مشکل فنی</option>
            <option value="محصول">مشکل محصول</option>
            <option value="پیشنهاد">پیشنهاد</option>
            <option value="سایر">سایر موارد</option>
        </select>

        <label>متن درخواست:</label>
        <textarea name="message" rows="5" required placeholder="لطفاً مشکل خود را توضیح دهید..."></textarea>

        <button type="submit">📨 ارسال درخواست</button>
    </form>

    <a href="index.php" class="back-link">← بازگشت به صفحه اصلی</a>
</div>
</body>
</html>