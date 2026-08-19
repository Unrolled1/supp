<?php

session_start();

require_once __DIR__ . '/includes/autoload.php';

date_default_timezone_set('Asia/Tehran');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    exit('دسترسی غیرمجاز');
}

if (!isAdmin() || !canViewProducts()) {
    exit('دسترسی غیرمجاز');
}

$db = getDB();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    exit('شناسه کالا نامعتبر است');
}

$sql = "
    SELECT
        k.*,
        d.name AS department_name,
        p.name AS receiver_name,
        b.name AS brand_name,
        u.username AS creator_name
    FROM kala k

    LEFT JOIN departments d
        ON d.id = k.department_id

    LEFT JOIN persons p
        ON p.id = k.receiver_person_id

    LEFT JOIN brands b
        ON b.id = k.brand_id

    LEFT JOIN users u
        ON u.id = k.created_by

    WHERE k.id = :id
    LIMIT 1
";

$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);

$kala = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kala) {
    exit('کالا پیدا نشد');
}

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function kalaValue($value)
{
    return !empty($value) ? h($value) : '-';
}

$date = !empty($kala['created_at'])
    ? fa_number($kala['created_at'])
    : '-';

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>پرینت کالا - <?= h($kala['name']) ?></title>

<link rel="stylesheet" href="includes/report_pdf.css">
</head>

<body>

<div class="print-container">

    <!-- دکمه‌ها -->
    <div class="action-buttons no-print">

        <button class="btn-print" onclick="window.print();">
            🖨️ پرینت
        </button>

        <button class="btn-pdf" onclick="savePDF();">
            📄 ذخیره PDF
        </button>

        <button class="btn-close" onclick="window.close();">
            ✖ بستن
        </button>

    </div>


    <!-- هدر -->
    <div class="print-header">

        <h1>📦 مشخصات کالا</h1>

        <div class="date">
            <?= h($kala['name']) ?>
        </div>

    </div>


    <!-- جدول مشخصات کالا -->
<div class="table-wrapper">

    <table class="data-table">

        <thead>
            <tr>
                <th>نام کالا</th>
                <th>تعداد</th>
                <th>کد رایانه</th>
                <th>کد اموال</th>
                <th>بخش</th>
                <th>برند</th>
                <th>سریال</th>
                <th>تحویل گیرنده</th>
                <th>تاریخ ثبت</th>
                <th>ثبت کننده</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><?= kalaValue($kala['name']) ?></td>
                <td><?= fa_number($kala['quantity'] ?? 1) ?></td>
                <td><?= kalaValue($kala['computer_code']) ?></td>
                <td><?= kalaValue($kala['property_code']) ?></td>
                <td><?= kalaValue($kala['department_name']) ?></td>
                <td><?= kalaValue($kala['brand_name']) ?></td>
                <td><?= kalaValue($kala['serial_number']) ?></td>
                <td><?= kalaValue($kala['receiver_name']) ?></td>
                <td><?= $date ?></td>
                <td><?= kalaValue($kala['creator_name']) ?></td>
            </tr>
        </tbody>

    </table>

</div>
    <!-- فوتر -->
    <div class="print-footer">
        سیستم پشتیبانی بیمارستان |
        چاپ شده در <?= fa_number(now()) ?>
    </div>

</div>
<script>
function savePDF() {

    const form = document.createElement('form');

    form.method = 'POST';
    form.action = 'export_pdf.php';

    function createHidden(name, value) {

        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = name;
        input.value = value;

        return input;
    }

    // ارسال جدول
    form.appendChild(
        createHidden(
            'html',
            document.querySelector('.data-table').outerHTML
        )
    );

    // ارسال CSS
    const css = `
        ${Array.from(document.styleSheets)
            .map(sheet => {
                try {
                    return Array.from(sheet.cssRules)
                        .map(rule => rule.cssText)
                        .join('\n');
                } catch (e) {
                    return '';
                }
            })
            .join('\n')}
    `;

    form.appendChild(
        createHidden('css', css)
    );

    // ارسال عنوان
    form.appendChild(
        createHidden(
            'title',
            document.querySelector('.print-header h1').innerText
        )
    );

    document.body.appendChild(form);

    form.submit();

    form.remove();
}
</script>

</body>
</html>
