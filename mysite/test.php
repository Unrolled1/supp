<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تاریخ شمسی ساده</title>
    <style>
        body {
            font-family: 'Vazir', Tahoma, sans-serif;
            padding: 50px;
            background: #f0f0f0;
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .date-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Vazir', Tahoma, sans-serif;
            font-size: 14px;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
        }
        .result {
            margin-top: 20px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📅 انتخاب تاریخ شمسی</h2>

    <label>تاریخ را انتخاب کنید:</label>
    <div class="date-group">
        <select id="year">
            <option value="">سال</option>
        </select>
        <select id="month">
            <option value="">ماه</option>
        </select>
        <select id="day">
            <option value="">روز</option>
        </select>
    </div>

    <button onclick="showDate()">نمایش تاریخ</button>
    <div class="result" id="result"></div>
</div>

<script>
    // پر کردن سال‌ها
    const yearSelect = document.getElementById('year');
    for (let i = 1390; i <= 1420; i++) {
        yearSelect.innerHTML += `<option value="${i}">${i}</option>`;
    }

    // پر کردن ماه‌ها
    const monthSelect = document.getElementById('month');
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    for (let i = 1; i <= 12; i++) {
        monthSelect.innerHTML += `<option value="${i}">${months[i-1]}</option>`;
    }

    // پر کردن روزها
    const daySelect = document.getElementById('day');
    for (let i = 1; i <= 31; i++) {
        daySelect.innerHTML += `<option value="${i}">${i}</option>`;
    }

    function showDate() {
        const year = yearSelect.value;
        const month = monthSelect.value;
        const day = daySelect.value;

        if (year && month && day) {
            document.getElementById('result').innerHTML = `تاریخ انتخاب شده: ${year}/${month}/${day}`;
        } else {
            document.getElementById('result').innerHTML = 'لطفاً تاریخ کامل را انتخاب کنید';
        }
    }
</script>
</body>
</html>