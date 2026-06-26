<?php
require_once 'config/config.php';
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
    <?php load_assets(); ?>
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