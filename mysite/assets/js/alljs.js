// ============================================
// فایل کلی جاوا
// ============================================

// تبدیل اعداد به فارسی
function fa_number(num) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return num.toString().replace(/\d/g, x => persianDigits[parseInt(x)]);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// ============================================
// رندر سلکت‌های تاریخ برای فرم ثبت فاکتور
// ============================================

function renderDateSelects(containerId, defaultYear = '', defaultMonth = '', defaultDay = '') {
    let html = '<div class="date-select-group">';
// سال
    html += '<select name="year" class="date-select">';
    html += '<option value="">سال</option>';
    for (let i = 1404; i <= 1420; i++) {
        html += `<option value="${i}" ${defaultYear == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';


    // اسلش بین سال و ماه
    html += '<span class="date-separator" style="color: black">/</span>';

    // ماه
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    html += '<select name="month" class="date-select">';
    html += '<option value="">ماه</option>';
    for (let i = 1; i <= 12; i++) {
        html += `<option value="${i}" ${defaultMonth == i ? 'selected' : ''}>${months[i-1]}</option>`;
    }
    html += '</select>';

    // اسلش بین ماه و روز
    html += '<span class="date-separator" style="color: black">/</span>';
// روز
    html += '<select name="day" class="date-select">';
    html += '<option value="">روز</option>';
    for (let i = 1; i <= 31; i++) {
        html += `<option value="${i}" ${defaultDay == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';


    html += '</div>';

    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = html;
    }
}

// ============================================
// رندر سلکت‌های تاریخ برای مودال ویرایش
// ============================================

function renderDateSelectsForEdit(year, month, day) {
    let html = '<div class="date-select-group">';
// سال
    html += '<select name="year" class="date-select">';
    html += '<option value="">سال</option>';
    for (let i = 1390; i <= 1420; i++) {
        html += `<option value="${i}" ${year == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';


    // اسلش بین سال و ماه
    html += '<span class="date-separator">/</span>';

    // ماه
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    html += '<select name="month" class="date-select">';
    html += '<option value="">ماه</option>';
    for (let i = 1; i <= 12; i++) {
        html += `<option value="${i}" ${month == i ? 'selected' : ''}>${months[i-1]}</option>`;
    }
    html += '</select>';

    // اسلش بین ماه و روز
    html += '<span class="date-separator">/</span>';

// روز
    html += '<select name="day" class="date-select">';
    html += '<option value="">روز</option>';
    for (let i = 1; i <= 31; i++) {
        html += `<option value="${i}" ${day == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';


    html += '</div>';
    return html;
}

// ============================================
// رندر سلکت‌های تاریخ برای جستجو
// ============================================

function renderSearchDateSelects(containerId, inputId, defaultDate = '') {
    let defaultYear = '', defaultMonth = '', defaultDay = '';
    if (defaultDate) {
        const parts = defaultDate.split('-');
        if (parts.length === 3) {
            defaultYear = parts[0];
            defaultMonth = parts[1];
            defaultDay = parts[2];
        }
    }

    let html = '<div class="date-select-group">';

    // سال
    html += '<select class="search-date-year date-select">';
    html += '<option value="">سال</option>';
    for (let i = 1404; i <= 1420; i++) {
        html += `<option value="${i}" ${defaultYear == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';

    // اسلش بین روز و ماه
    html += '<span class="date-separator">/</span>';

    // ماه
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    html += '<select class="search-date-month date-select">';
    html += '<option value="">ماه</option>';
    for (let i = 1; i <= 12; i++) {
        html += `<option value="${i}" ${defaultMonth == i ? 'selected' : ''}>${months[i-1]}</option>`;
    }
    html += '</select>';

    // اسلش بین ماه و سال
    html += '<span class="date-separator">/</span>';

    // روز
    html += '<select class="search-date-day date-select">';
    html += '<option value="">روز</option>';
    for (let i = 1; i <= 31; i++) {
        html += `<option value="${i}" ${defaultDay == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';

    html += '</div>';

    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = html;
    }

    const daySelect = document.querySelector(`#${containerId} .search-date-day`);
    const monthSelect = document.querySelector(`#${containerId} .search-date-month`);
    const yearSelect = document.querySelector(`#${containerId} .search-date-year`);

    function updateHidden() {
        const hiddenInput = document.getElementById(inputId);
        if (hiddenInput && daySelect && daySelect.value && monthSelect && monthSelect.value && yearSelect && yearSelect.value) {
            hiddenInput.value = `${yearSelect.value}-${String(monthSelect.value).padStart(2, '0')}-${String(daySelect.value).padStart(2, '0')}`;
        } else if (hiddenInput) {
            hiddenInput.value = '';
        }
    }

    if (daySelect) daySelect.addEventListener('change', updateHidden);
    if (monthSelect) monthSelect.addEventListener('change', updateHidden);
    if (yearSelect) yearSelect.addEventListener('change', updateHidden);
    updateHidden();
}

// ============================================
//انتخاب سریع و پرکردن فیلد تاریخ
// ============================================

function setDateRange(fromDate, toDate) {
    console.log('setDateRange called with:', fromDate, toDate);

    // تنظیم "از تاریخ"
    const fromContainer = document.getElementById('search_date_from_container');
    console.log('fromContainer:', fromContainer);

    if (fromContainer) {
        const fromYearSelect = fromContainer.querySelector('.search-date-year');
        const fromMonthSelect = fromContainer.querySelector('.search-date-month');
        const fromDaySelect = fromContainer.querySelector('.search-date-day');

        console.log('from selects:', fromYearSelect, fromMonthSelect, fromDaySelect);

        if (fromYearSelect && fromMonthSelect && fromDaySelect) {
            fromYearSelect.value = fromDate.year.toString();
            fromMonthSelect.value = fromDate.month.toString();
            fromDaySelect.value = fromDate.day.toString();

            console.log('after set - from values:', fromYearSelect.value, fromMonthSelect.value, fromDaySelect.value);

            const fromHidden = document.getElementById('search_date_from');
            if (fromHidden) {
                fromHidden.value = `${fromDate.year}-${String(fromDate.month).padStart(2, '0')}-${String(fromDate.day).padStart(2, '0')}`;
            }
        }
    }

    // تنظیم "تا تاریخ" (همینطور)
    const toContainer = document.getElementById('search_date_to_container');
    if (toContainer) {
        const toYearSelect = toContainer.querySelector('.search-date-year');
        const toMonthSelect = toContainer.querySelector('.search-date-month');
        const toDaySelect = toContainer.querySelector('.search-date-day');

        if (toYearSelect && toMonthSelect && toDaySelect) {
            toYearSelect.value = toDate.year.toString();
            toMonthSelect.value = toDate.month.toString();
            toDaySelect.value = toDate.day.toString();

            const toHidden = document.getElementById('search_date_to');
            if (toHidden) {
                toHidden.value = `${toDate.year}-${String(toDate.month).padStart(2, '0')}-${String(toDate.day).padStart(2, '0')}`;
            }
        }
    }
}

function initQuickDateSelect() {
    const quickSelect = document.getElementById('quick_date_select');
    if (!quickSelect) return;

    quickSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        if (!selectedValue) return;

        const now = new Date();

        // تبدیل تاریخ میلادی به شمسی
        function toJalali(date) {
            const jd = date.toLocaleDateString('fa-IR-u-nu-latn', {
                year: 'numeric',
                month: 'numeric',
                day: 'numeric'
            }).split('/');
            return {
                year: parseInt(jd[0]),
                month: parseInt(jd[1]),
                day: parseInt(jd[2])
            };
        }

        // تبدیل تاریخ شمسی به میلادی (برای ساخت Date object)
        function toGregorian(year, month, day) {
            // این تابع ساده - برای محاسبات هفته استفاده میشه
            const jalaliDate = new Date(`${year}/${month}/${day}`);
            return new Date(year, month - 1, day);
        }

        const todayJalali = toJalali(now);

        // ========== روز جاری ==========
        if (selectedValue === 'today') {
            setDateRange(todayJalali, todayJalali);
            return;
        }

        // ========== ماه جاری ==========
        if (selectedValue === 'this_month') {
            // اول ماه
            const startOfMonth = { year: todayJalali.year, month: todayJalali.month, day: 1 };

            // آخر ماه (تعداد روزهای ماه شمسی)
            let lastDay;
            if (todayJalali.month <= 6) {
                lastDay = 31;
            } else if (todayJalali.month <= 11) {
                lastDay = 30;
            } else {
                const isLeap = (todayJalali.year % 33 === 1 || todayJalali.year % 33 === 5 ||
                    todayJalali.year % 33 === 9 || todayJalali.year % 33 === 13 ||
                    todayJalali.year % 33 === 17 || todayJalali.year % 33 === 22 ||
                    todayJalali.year % 33 === 26 || todayJalali.year % 33 === 30);
                lastDay = isLeap ? 30 : 29;
            }
            const endOfMonth = { year: todayJalali.year, month: todayJalali.month, day: lastDay };

            setDateRange(startOfMonth, endOfMonth);

            return;
        }

        // ========== سال جاری ==========
        if (selectedValue === 'this_year') {
            const startOfYear = { year: todayJalali.year, month: 1, day: 1 };

            const isLeapYear = (todayJalali.year % 33 === 1 || todayJalali.year % 33 === 5 ||
                todayJalali.year % 33 === 9 || todayJalali.year % 33 === 13 ||
                todayJalali.year % 33 === 17 || todayJalali.year % 33 === 22 ||
                todayJalali.year % 33 === 26 || todayJalali.year % 33 === 30);
            const lastDayOfYear = isLeapYear ? 30 : 29;
            const endOfYear = { year: todayJalali.year, month: 12, day: lastDayOfYear };

            setDateRange(startOfYear, endOfYear);

            return;
        }

        // ========== هفته جاری (شنبه تا جمعه) ==========
        if (selectedValue === 'this_week') {
            // پیدا کردن شنبه این هفته

            const dayOfWeek = now.getDay();

            // 0=یکشنبه ... 6=شنبه
            const daysBack = (dayOfWeek === 6) ? 0 : dayOfWeek + 1;

            const saturday = new Date(now);
            saturday.setDate(now.getDate() - daysBack);

            const friday = new Date(saturday);
            friday.setDate(saturday.getDate() + 6);

            const startOfWeekJalali = toJalali(saturday);
            const endOfWeekJalali = toJalali(friday);

            setDateRange(startOfWeekJalali, endOfWeekJalali);

            return;
        }
    });
}

// ============================================
// توابع مودال ویرایش
// ============================================

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('.invoices-table tbody tr');
    rows.forEach((row, index) => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            firstCell.textContent = fa_number(index + 1);
        }
    });
}

// ============================================
// مقداردهی اولیه فرم ثبت
// ============================================

function initAddForm() {
    const dateContainer = document.getElementById('invoice_date_container');
    if (dateContainer) {
        renderDateSelects('invoice_date_container');
    }
}

// ============================================
// ساعت زنده
// ============================================

function updateClock() {
    fetch('get_time.php')
        .then(response => response.json())
        .then(data => {
            const clock = document.getElementById('liveClock');
            if (clock) clock.innerHTML = '📅 ' + data.datetime;
        })
        .catch(error => console.log('Clock error:', error));
}

// ============================================
// بستن مودال با کلیک روی پس‌زمینه
// ============================================

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
};

// ============================================
// راه‌اندازی اولیه
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Invoices JS loaded');
    initSearch();
    initQuickDateSelect();
    initAddForm();
    setInterval(updateClock, 1000);
    updateClock();
});