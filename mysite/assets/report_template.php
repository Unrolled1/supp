
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
<style>
        @font-face {
    font-family: 'Vazir';
    src: url('styles/Fonts/Vazir.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
 }
 @page{
    size: A4 landscape;
    margin:5mm;
 }
        * {
            font-family: 'Vazir',  sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
    font-family: 'Vazir', 'Tahoma', sans-serif;
    padding: 2px;
    background: #1f2937;
    direction: rtl;
}

.print-container {
    max-width: 1200px;
    margin: 0 auto;
    background: #111827;
    padding: 10px;
    border-radius: 12px;
    box-shadow: 0 2px 20px rgba(0,0,0,0.4);
}

.print-header {
    text-align:center;
    margin-bottom:10px;
    padding-bottom:10px;
    border-bottom:3px solid #d4af37;
}

.print-header h1 {
    color:#d4af37;
    font-size:26px;
}

        .print-header .date {
    color:#cbd5e1;
}


.filters-info {
    background:#1e293b;
    padding:15px 20px;
    border-radius:10px;
    margin-bottom:10px;
    font-size:14px;
    color:#e5e7eb;
    border-right:4px solid #d4af37;
}

.filters-info span {
    color:#d4af37;
}


.stat-box {
    border:1px solid #374151;
    border-radius:12px;
    padding:18px 20px;
    text-align:center;
    background:#1e293b;
}

        .stat-num {
    color:#d4af37;
}

.stat-title {
    color:#9ca3af;
}


.table-wrapper {
    border-radius:10px;
    border:1px solid #374151;
}


table {
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

        th,td {
    border:1px solid #374151;
    padding:10px 12px;
    text-align:center;
}


th {
    background:#334155;
    color:#f9fafb;
    font-size:13px;
}


td {
    color:#e5e7eb;
}


tr:nth-child(even) {
    background:#172033;
}


tr:hover {
    background:#263449;
}


.print-footer {
    color:#94a3b8;
    border-top:1px solid #374151;
}

        /* دکمه‌های پرینت و بستن (فقط در صفحه نمایش) */
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .action-buttons button {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Vazir', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background: linear-gradient(135deg, #667eea, #5a67d8);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
        }
.btn-pdf {
    background: #48bb78;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Vazir', sans-serif;
}
.btn-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
        }
        .btn-close {
            background: #f0f0f0;
            color: #555;
        }

        .btn-close:hover {
            transform: translateY(-2px);
            background: #e0e0e0;
        }

        /* تذکر پرینت */
        .print-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 10px;
            text-align: center;
            font-size: 14px;
        }

        .print-notice strong {
            color: #d39e00;
        }
        @media print {

    body{
        background:#969799;
        padding:0;
        margin:0;
    }

    .no-print,
    .action-buttons,
    .print-notice{
        display:none !important;
    }

    .print-container{
        max-width:100%;
        width:100%;
        margin:0;
        padding:0;
        border:none;
        border-radius:0;
        box-shadow:none;
    }

    .table-wrapper{
        overflow:visible;
        border:none;
    }

    table{
        width:100%;
        table-layout:fixed;
        border-collapse:collapse;
    }

    th,
    td{
        font-size:10px;
        padding:4px;
        white-space:normal;
        word-break:break-word;
        overflow-wrap:anywhere;
        vertical-align:top;
    }

    th{
        white-space:normal;
    }

 }
</style>
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
    <!-- جدول تیکت‌ها -->
    <div class="table-wrapper">
        <table>
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
function savePDF(){

    const content = document.querySelector('.table-wrapper').innerHTML;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'export_pdf.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'html';
    input.value = content;

    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();

    form.remove();
}
</script>

</body>
</html>