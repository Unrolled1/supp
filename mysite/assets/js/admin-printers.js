// ============================================
// فایل مدیریت پرینترها
// ============================================

// ============================================
// رندر سلکت‌های تاریخ برای مودال ویرایش
// ============================================
function renderDateSelectsForEdit(year, month, day) {
    const currentYear = 1405;
    const y = year || currentYear;
    const m = month || 1;
    const d = day || 1;
    const maxDays = getJalaliMonthDays(parseInt(y), parseInt(m));

    let html = '<div class="date-select-group">';

    // سال
    html += '<select class="edit-date-year date-select" name="edit_year">';
    for (let i = 1390; i <= 1410; i++) {
        const selected = (i == y) ? 'selected' : '';
        html += `<option value="${i}" ${selected}>${fa_number(i)}</option>`;
    }
    html += '</select>';

    html += '<span class="date-separator">/</span>';

    // ماه
    html += '<select class="edit-date-month date-select" name="edit_month">';
    for (let i = 1; i <= 12; i++) {
        const selected = (i == m) ? 'selected' : '';
        html += `<option value="${i}" ${selected}>${getMonthName(i)}</option>`;
    }
    html += '</select>';

    html += '<span class="date-separator">/</span>';

    // روز
    html += '<select class="edit-date-day date-select" name="edit_day">';
    for (let i = 1; i <= maxDays; i++) {
        const selected = (i == d) ? 'selected' : '';
        html += `<option value="${i}" ${selected}>${fa_number(i)}</option>`;
    }
    html += '</select>';

    html += '</div>';
    return html;
}
// ============================================
// توابع جستجو
// ============================================

function initSearch() {

    const searchBtn = document.getElementById('search_btn');
    const resetBtn = document.getElementById('reset_btn');

    const urlParams = new URLSearchParams(window.location.search);
    // مقداردهی سلکت‌های تاریخ
    const dateFrom = urlParams.get('date_from') || '';
    const dateTo = urlParams.get('date_to') || '';

    renderSearchDateSelects('search_date_from_container', 'search_date_from', dateFrom);
    renderSearchDateSelects('search_date_to_container', 'search_date_to', dateTo);
    // پر کردن فیلدهای جستجو از URL
    const fields = {
        'search_computer_code': 'computer_code',
        'search_property_code': 'property_code',
        'search_activity': 'activity',
        'search_department': 'department',
        'search_brand': 'brand'
    };
    for (const [id, param] of Object.entries(fields)) {
        const el = document.getElementById(id);
        if (el && urlParams.has(param)) {
            el.value = urlParams.get(param);
        }
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            const params = new URLSearchParams();

            const computerCode = document.getElementById('search_computer_code')?.value;
            const propertyCode = document.getElementById('search_property_code')?.value;
            const activity = document.getElementById('search_activity')?.value;
            const department = document.getElementById('search_department')?.value;
            const brand = document.getElementById('search_brand')?.value;

            // دریافت مقادیر تاریخ از فیلدهای مخفی
            const dateFrom = document.getElementById('search_date_from')?.value;
            const dateTo = document.getElementById('search_date_to')?.value;

            if (computerCode) params.set('computer_code', computerCode);
            if (propertyCode) params.set('property_code', propertyCode);
            if (activity) params.set('activity', activity);
            if (department) params.set('department', department);
            if (brand) params.set('brand', brand);
            if (dateFrom) params.set('date_from', dateFrom);
            if (dateTo) params.set('date_to', dateTo);

            window.location.href = 'admin_printers.php?' + params.toString();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            window.location.href = 'admin_printers.php';
        });
    }

}
// ============================================
// توابع مودال ویرایش
// ============================================

function openEditModal(printer) {
    document.getElementById('edit_printer_id').value = printer.id;
    document.getElementById('edit_computer_code').value = printer.computer_code || '';
    document.getElementById('edit_property_code').value = printer.property_code || '';
    document.getElementById('edit_activity_id').value = printer.activity_id || '';
    document.getElementById('edit_department_id').value = printer.department_id || '';
    document.getElementById('edit_brand_id').value = printer.brand_id || '';
    document.getElementById('edit_serial_number').value = printer.serial_number || '';
    document.getElementById('edit_description').value = printer.description || '';

// تاریخ ثبت
    let year = '', month = '', day = '';
    if (printer.created_at) {
        const parts = printer.created_at.split('-');
        if (parts.length === 3) {
            year = parts[0];
            month = parseInt(parts[1]);
            day = parseInt(parts[2]);
        }
    }

    const dateContainer = document.getElementById('edit_date_container');
    if (dateContainer) {
        dateContainer.innerHTML = renderDateSelectsForEdit(year, month, day);
    }

    document.getElementById('editModal').style.display = 'flex';
}
//حذف
function confirmDelete(id, serial) {

    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'پرینتر "' + serial + '" حذف خواهد شد!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو'
    }).then((result) => {

        if (result.isConfirmed) {

            fetch('admin_printers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'delete_printer=1&printer_id=' + id
            })
                .then(response => response.json())
                .then(data => {

                    if (data.success) {

                        const button = document.querySelector(
                            `button[onclick*="confirmDelete(${id}"]`
                        );

                        const row = button ? button.closest('tr') : null;

                        if (row) {
                            row.remove();
                            updateRowNumbers();
                        }

                        Swal.fire({
                            title: 'حذف شد!',
                            text: 'پرینتر با موفقیت حذف شد.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
        }
    });
}

// ============================================
// راه‌اندازی اولیه
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const today=toJalali(new Date());
    renderDateSelects('printer_date_container',today.year,today.month,today.day);
    initSearch();
    initQuickDateSelect();
});