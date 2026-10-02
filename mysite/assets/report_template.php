
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link rel="stylesheet" href="includes/report_pdf.css?v=2">

</head>
<body>
<div class="print-container">
    <!-- دکمه‌های عملیاتی (فقط در صفحه نمایش) -->
    <div class="action-buttons no-print">
        <button class="btn-print" onclick="window.print();">
            🖨️ پرینت گزارش
        </button>
        <button class="btn-pdf" onclick="savePDF()">
    📄 ذخیره PDF
</button>
        <button class="btn-close" onclick="window.close();">
            ✖ بستن
        </button>
    </div>
    <!-- هدر گزارش -->
    <div class="print-header">
        <h1>📊 <?php echo $pageTitle; ?></h1>
        <div class="date">
            تاریخ چاپ: <?php echo fa_number(now()); ?>
        </div>
    </div>
    <!-- اطلاعات فیلترها -->
    <div class="filters-info">
        <?= $filterInfo ?? '📋 نمایش همه اطلاعات' ?>
    </div>
    <!-- آمار -->
    <?php if (!empty($stats)): ?>
<div class="stats">
    <?php foreach ($stats as $key => $item): ?>
        <?php if ($key == 'labels') continue; ?>
        <div class="stat-box">
            <div class="stat-num"><?= fa_number($item) ?></div>
            <div class="stat-title"><?= $stats['labels'][$key] ?></div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

    <div class="table-wrapper">
    <table class="data-table">
            <thead>
            <tr>
                <?php foreach ($tableHeaders as $header): ?>
                    <th><?= htmlspecialchars($header) ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($tableRows)): ?>
                <tr>
                    <td colspan="<?= count($tableHeaders) ?>" class="no-data">
                        📭 هیچ موردی یافت نشد
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($tableRows as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?= $cell ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- فوتر -->
    <div class="print-footer">
        سیستم پشتیبانی بیمارستان | چاپ شده در <?php echo fa_number(now()); ?>
    </div>
</div>

<script>
    function savePDF() {

        const tableWrapper = document.querySelector('.table-wrapper');
        const titleElement = document.querySelector('.print-header h1');
        const filterElement = document.querySelector('.filters-info');

        if (!tableWrapper) {
            alert('جدول گزارش پیدا نشد');
            return;
        }

        const form = document.createElement('form');

        form.method = 'POST';
        form.action = 'export_pdf.php';
        form.target = '_self';

        function addInput(name, value) {

            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = name;
            input.value = value ?? '';

            form.appendChild(input);
        }

        // جدول
        addInput(
            'html',
            tableWrapper.outerHTML
        );

        // عنوان
        addInput(
            'title',
            titleElement
                ? titleElement.innerText
                : 'گزارش'
        );

        // فیلتر
        addInput(
            'filter',
            filterElement
                ? filterElement.innerHTML
                : ''
        );

        document.body.appendChild(form);

        form.submit();

        setTimeout(() => {
            form.remove();
        }, 1000);
    }
</script>

</body>
</html>