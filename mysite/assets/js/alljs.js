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
// توابع گزارشات
// ============================================
function openPrintWindow(config) {

    const form = document.getElementById("filterform");
    if (!form) return;

    const params = new URLSearchParams(new FormData(form));

    params.append("print", "1");

    window.open(
        config.printUrl + "?" + params.toString(),
        "_blank",
        "width=1000,height=800,scrollbars=yes"
    );

}
function applyReportFilters(config) {

    const form = document.getElementById("filterform");
    if (!form) return;

    const formData = new FormData(form);
    formData.append("ajax", "1");

    showLoading(true);

    fetch(config.url, {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {

            showLoading(false);

            if (!data.success) {
                showToast(data.message || "خطا", "error");
                return;
            }

            updateTable(config.table, data.table);

            if (config.stats && data.stats)
                updateStats(data.stats);

            if (config.filterInfo && data.filterInfo)
                updateFilterInfo(data.filterInfo);

        })
        .catch(err => {
            showLoading(false);
            console.error(err);
            showToast("خطا در ارتباط با سرور", "error");
        });

}
/**
 * اعمال فیلترها با استفاده از Ajax
 * بدون رفرش صفحه، لیست تیکت‌ها را به‌روز میکند
 */
function applyReportFilters(config) {

    const form = document.getElementById("filterform");
    if (!form) return;

    const formData = new FormData(form);
    formData.append("ajax", "1");

    showLoading(true);

    fetch(config.url, {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {

            showLoading(false);

            if (!data.success) {
                showToast(data.message || "خطا", "error");
                return;
            }

            updateTable(config.table, data.table);

            if (config.stats)
                updateStats(data.stats);

            if (config.filterInfo)
                updateFilterInfo(data.filterInfo);

        })
        .catch(err => {
            showLoading(false);
            console.error(err);
            showToast("خطا در ارتباط با سرور", "error");
        });

}


function resetReportFilters(config) {

    const form = document.getElementById("filterform");
    if (!form) return;

    form.reset();

    applyReportFilters(config);

}

function applyFiltersAjax() {
    // گرفتن مقادیر فیلترها
    const department_id = document.querySelector('select[name="department_id"]')?.value || '';
    const status = document.querySelector('select[name="status"]')?.value || '';
    const from_day = document.querySelector('select[name="from_day"]')?.value || '';
    const from_month = document.querySelector('select[name="from_month"]')?.value || '';
    const from_year = document.querySelector('select[name="from_year"]')?.value || '';
    const to_day = document.querySelector('select[name="to_day"]')?.value || '';
    const to_month = document.querySelector('select[name="to_month"]')?.value || '';
    const to_year = document.querySelector('select[name="to_year"]')?.value || '';

    // اعتبارسنجی تاریخ
    if (from_year && from_month && from_day && to_year && to_month && to_day) {
        const fromDate = new Date(from_year, from_month - 1, from_day);
        const toDate = new Date(to_year, to_month - 1, to_day);
        if (fromDate > toDate) {
            showToast('تاریخ "از" نمیتواند بزرگتر از تاریخ "تا" باشد!', 'error');
            return;
        }
    }

    // ساخت داده برای ارسال
    const formData = new FormData();
    if (department_id) formData.append('department_id', department_id);
    if (status) formData.append('status', status);
    if (from_year && from_month && from_day) {
        formData.append('from_year', from_year);
        formData.append('from_month', from_month);
        formData.append('from_day', from_day);
    }
    if (to_year && to_month && to_day) {
        formData.append('to_year', to_year);
        formData.append('to_month', to_month);
        formData.append('to_day', to_day);
    }
    formData.append('ajax', '1'); // مشخص کردن درخواست Ajax

    // نمایش وضعیت بارگذاری
    showLoading(true);

    // ارسال درخواست Ajax
    fetch('admin_ticketrep.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            showLoading(false);
            if(!data.success){
                showToast(data.message || "خطا", "error");
                return;
            }

            updateTable(data.table);
            updateStats(data.stats);
            updateFilterInfo(data.filterInfo);

        })
        .catch(error => {
            showLoading(false);
            console.error('Error:', error);
            showToast('خطا در ارتباط با سرور', 'error');
        });
}

function updateTable(selector, html) {

    const table = document.querySelector(selector);

    if (table)
        table.innerHTML = html;

}
/**
 * به‌روز کردن آمار
 */
function updateStats(stats) {
    if (!stats) return;

    const statBoxes = document.querySelectorAll('.stat-box .stat-num');
    if (statBoxes.length >= 4) {
        statBoxes[0].textContent = stats.total || '0';
        statBoxes[1].textContent = stats.review || '0';
        statBoxes[2].textContent = stats.answered || '0';
        statBoxes[3].textContent = stats.closed || '0';
    }
}

/**
 * به‌روز کردن اطلاعات فیلترها
 */
function updateFilterInfo(html) {
    const filterInfo = document.querySelector('.filters-info');
    if (filterInfo) {
        filterInfo.innerHTML = html;
    }
}

/**
 * نمایش/مخفی کردن وضعیت بارگذاری
 */
function showLoading(show) {
    const loadingElement = document.getElementById('loading-overlay');
    if (loadingElement) {
        loadingElement.style.display = show ? 'flex' : 'none';
    }
}

/**
 * نمایش پیام toast
 */
function showToast(message, type = 'info') {
    if (typeof toastr !== 'undefined') {
        switch(type) {
            case 'success': toastr.success(message); break;
            case 'error': toastr.error(message); break;
            case 'warning': toastr.warning(message); break;
            default: toastr.info(message);
        }
        return;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            text: message,
            icon: type,
            timer: 3000,
            showConfirmButton: false
        });
        return;
    }

    alert(message);
}


document.addEventListener("DOMContentLoaded", () => {

    if (!window.reportConfig)
        return;

    document.querySelector(".btn-filter")
        ?.addEventListener("click", () => applyReportFilters(reportConfig));

    document.querySelector(".btn-reset")
        ?.addEventListener("click", () => resetReportFilters(reportConfig));

    document.querySelector(".btn-pdf")
        ?.addEventListener("click", openPrintWindow);

});
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
document.addEventListener("DOMContentLoaded", () => {

    // انتقال کرسر به آخر متن
    const inputs = document.querySelectorAll('input[type="text"], input[type="number"]');

    inputs.forEach(input => {

        input.addEventListener("focus", function () {
            setCursorToEnd(this);
        });

        input.addEventListener("click", function () {
            setCursorToEnd(this);
        });

    });

    // ساخت انتخابگرهای تاریخ (اگر وجود داشته باشند)
    if (document.getElementById("search_date_from_container"))
        renderSearchDateSelects("search_date_from_container", "search_date_from");

    if (document.getElementById("search_date_to_container"))
        renderSearchDateSelects("search_date_to_container", "search_date_to");


    // گزارش‌ها
    if (window.reportConfig) {

        document.querySelector(".btn-filter")
            ?.addEventListener("click", () => applyReportFilters(reportConfig));

        document.querySelector(".btn-reset")
            ?.addEventListener("click", () => resetReportFilters(reportConfig));

        document.querySelector(".btn-pdf")
            ?.addEventListener("click", () => openPrintWindow(reportConfig));

    }

});