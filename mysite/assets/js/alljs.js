// ============================================
// فایل کلی جاوا (توابع عمومی)
// ============================================

// تبدیل اعداد به فارسی
function fa_number(num) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return num.toString().replace(/\d/g, x => persianDigits[parseInt(x)]);
}
function faToEn(str) {
    return str.replace(/[۰-۹]/g, d => "۰۱۲۳۴۵۶۷۸۹".indexOf(d));
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
// توابع تاریخ عمومی
// ============================================

function getJalaliMonthDays(year, month) {
    if (month <= 6) return 31;
    if (month <= 11) return 30;
    // اسفند
    const isLeap = (year % 33 === 1 || year % 33 === 5 || year % 33 === 9 ||
        year % 33 === 13 || year % 33 === 17 || year % 33 === 22 ||
        year % 33 === 26 || year % 33 === 30);
    return isLeap ? 30 : 29;
}
function getMonthName(month) {
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    return months[month - 1] || month;
}
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

// ============================================
// رندر سلکت‌های تاریخ برای فرم ثبت (عمومی)
// ============================================

function renderDateSelects(containerId, defaultYear = '', defaultMonth = '', defaultDay = '') {

    let html = '<div class="date-select-group">';
// سال
    html += '<select name="year" class="date-select">';
    html += '<option value="">سال</option>';
    for (let i = 1405; i <= 1420; i++) {
        html += `<option value="${i}" ${defaultYear == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';

    // اسلش بین سال و ماه
    html += '<span class="date-separator">/</span>';

    // ماه
    html += '<select name="month" class="date-select">';
    html += '<option value="">ماه</option>';
    for (let i = 1; i <= 12; i++) {
        html += `<option value="${i}" ${defaultMonth == i ? 'selected' : ''}>${getMonthName(i)}</option>`;
    }
    html += '</select>';

    // اسلش بین ماه و روز
    html += '<span class="date-separator">/</span>';

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

    const container = document.getElementById(containerId);
    if (!container) return;

    let defaultYear = '', defaultMonth = '', defaultDay = '';
    if (defaultDate) {
        const parts = defaultDate.split('-');
        if (parts.length === 3) {
            defaultYear = parts[0];
            defaultMonth = parts[1];
            defaultDay = parts[2];
        }
    }
    const currentYear = 1405;
    const year = defaultYear || currentYear;
    const month = parseInt(defaultMonth) || 1;
    const day = parseInt(defaultDay) || 1;
    const maxDays = getJalaliMonthDays(parseInt(year), month);

    let html = '<div class="date-select-group">';

    // سال
    html += '<select class="search-date-year date-select">';
    html += '<option value="">سال</option>';
    for (let i = 1405; i <= 1420; i++) {
        html += `<option value="${i}" ${defaultYear == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';

    // اسلش بین روز و ماه
    html += '<span class="date-separator">/</span>';

    // ماه
    html += '<select class="search-date-month date-select">';
    html += '<option value="">ماه</option>';
    for (let i = 1; i <= 12; i++) {
        const selected = (i == month) ? 'selected' : '';
        html += `<option value="${i}" ${selected}>${getMonthName(i)}</option>`;    }
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

    container.innerHTML = html;

// Event listener برای به‌روزرسانی فیلد مخفی
    container.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', function() {
            updateSearchDateHidden(containerId, inputId);
        });
    });

    // به‌روزرسانی اولیه
    updateSearchDateHidden(containerId, inputId);
}

function updateSearchDateHidden(containerId, hiddenId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const yearSelect = container.querySelector('.search-date-year');
    const monthSelect = container.querySelector('.search-date-month');
    const daySelect = container.querySelector('.search-date-day');
    const hidden = document.getElementById(hiddenId);

    if (!yearSelect || !monthSelect || !daySelect || !hidden) return;

    if (yearSelect.value && monthSelect.value && daySelect.value) {
        const year = yearSelect.value;
        const month = String(monthSelect.value).padStart(2, '0');
        const day = String(daySelect.value).padStart(2, '0');
        hidden.value = `${year}-${month}-${day}`;
    } else {
        hidden.value = '';
    }
}

$(function () {

    if ($("#date_from").length) {
        $("#date_from").persianDatepicker({
            format: "YYYY/MM/DD",
            autoClose: true,
            initialValue: false
        });
    }

    if ($("#date_to").length) {
        $("#date_to").persianDatepicker({
            format: "YYYY/MM/DD",
            autoClose: true,
            initialValue: false
        });
    }

    if ($("#add_date").length) {
        $("#add_date").persianDatepicker({
            format: "YYYY/MM/DD",
            autoClose: true,
            initialValue: true   // فقط این یکی تاریخ امروز
        });
    }

    if ($("#edit_date").length) {
        $("#edit_date").persianDatepicker({
            format: "YYYY/MM/DD",
            autoClose: true,
            initialValue: false
        });
    }

    if ($("#quick_date_select").length) {

        $("#quick_date_select").on("change", function () {

            const now = new persianDate();

            switch ($(this).val()) {

                case "today":
                    $("#date_from").val(now.format("YYYY/MM/DD"));
                    $("#date_to").val(now.format("YYYY/MM/DD"));
                    break;

                case "week":
                    $("#date_from").val(now.clone().startOf("week").format("YYYY/MM/DD"));
                    $("#date_to").val(now.clone().endOf("week").format("YYYY/MM/DD"));
                    break;

                case "month":
                    $("#date_from").val(now.clone().startOf("month").format("YYYY/MM/DD"));
                    $("#date_to").val(now.clone().endOf("month").format("YYYY/MM/DD"));
                    break;

                case "year":
                    $("#date_from").val(now.clone().startOf("year").format("YYYY/MM/DD"));
                    $("#date_to").val(now.clone().endOf("year").format("YYYY/MM/DD"));
                    break;
            }

        });

    }

});
// ============================================
//انتخاب سریع و پرکردن فیلد تاریخ
// ============================================

function setDateRange(fromDate, toDate) {

    document.getElementById("date_from").value =
        fromDate.year + "/" +
        String(fromDate.month).padStart(2, "0") + "/" +
        String(fromDate.day).padStart(2, "0");

    document.getElementById("date_to").value =
        toDate.year + "/" +
        String(toDate.month).padStart(2, "0") + "/" +
        String(toDate.day).padStart(2, "0");

}

function initQuickDateSelect() {
    const quickSelect = document.getElementById('quick_date_select');
    if (!quickSelect) return;

    quickSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        if (!selectedValue) return;

        const now = new Date();
        const today = toJalali(now);
        let fromDate = null, toDate = null;

        switch (selectedValue) {
            case 'today':
                fromDate = toDate = today;
                break;

            case 'week': {
                const dayOfWeek = now.getDay();
                const daysBack = (dayOfWeek === 6) ? 0 : dayOfWeek + 1;

                const saturday = new Date(now);
                saturday.setDate(now.getDate() - daysBack);

                const friday = new Date(saturday);
                friday.setDate(saturday.getDate() + 6);

                fromDate = toJalali(saturday);
                toDate = toJalali(friday);
                break;
            }

            case 'month': {
                const maxDays = getJalaliMonthDays(today.year, today.month);
                fromDate = { year: today.year, month: today.month, day: 1 };
                toDate   = { year: today.year, month: today.month, day: maxDays };
                break;
            }

            case 'year': {
                const maxDays = getJalaliMonthDays(today.year, 12);
                fromDate = { year: today.year, month: 1, day: 1 };
                toDate   = { year: today.year, month: 12, day: maxDays };
                break;
            }
        }
        if (fromDate && toDate) {
            setDateRange(fromDate, toDate);
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



// ============================================
// بستن مودال با کلیک روی پس‌زمینه
// ============================================

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
};

// ============================================
// کنترل مکان‌نما در فیلدهای راست‌چین
// ============================================

function setCursorToEnd(input) {
    // مکان‌نما رو به انتهای متن ببر
    if (input.setSelectionRange) {
        setTimeout(function() {
            input.setSelectionRange(input.value.length, input.value.length);
        }, 0);
    }
}

// وقتی کاربر روی فیلد کلیک میکنه
document.addEventListener('DOMContentLoaded', function() {
    // همه فیلدهای ورودی
    const inputs = document.querySelectorAll('input[type="text"], input[type="number"]');

    inputs.forEach(function(input) {
        // وقتی کاربر کلیک میکنه
        input.addEventListener('focus', function() {
            setCursorToEnd(this);
        });

        // وقتی کاربر با Tab میاد
        input.addEventListener('click', function() {
            setCursorToEnd(this);
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    renderSearchDateSelects('search_date_from_container', 'search_date_from');
    renderSearchDateSelects('search_date_to_container', 'search_date_to');
});