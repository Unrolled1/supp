// ============================================
// فایل مدیریت فاکتورها
// ============================================

// تبدیل اعداد به فارسی
function fa_number(num) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return num.toString().replace(/\d/g, x => persianDigits[parseInt(x)]);
}

// ============================================
// رندر سلکت‌های تاریخ برای فرم ثبت فاکتور
// ============================================

function renderDateSelects(containerId, defaultYear = '', defaultMonth = '', defaultDay = '') {
    let html = '<div class="date-select-group" style="display: ' +
        'flex; gap: 10px; flex-wrap: wrap; align-items: center; direction: rtl;">';
// سال
    html += '<select name="year" class="date-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">';
    html += '<option value="">سال</option>';
    for (let i = 1404; i <= 1420; i++) {
        html += `<option value="${i}" ${defaultYear == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';


    // اسلش بین سال و ماه
    html += '<span class="date-separator" style="color: black">/</span>';

    // ماه
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    html += '<select name="month" class="date-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">';
    html += '<option value="">ماه</option>';
    for (let i = 1; i <= 12; i++) {
        html += `<option value="${i}" ${defaultMonth == i ? 'selected' : ''}>${months[i-1]}</option>`;
    }
    html += '</select>';

    // اسلش بین ماه و روز
    html += '<span class="date-separator" style="color: black">/</span>';
// روز
    html += '<select name="day" class="date-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">';
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
    let html = '<div class="date-select-group" style="display: ' +
        'flex; gap: 10px; flex-wrap: wrap; align-items: center; direction: rtl;">';
// سال
    html += '<select name="year" class="date-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">';
    html += '<option value="">سال</option>';
    for (let i = 1390; i <= 1420; i++) {
        html += `<option value="${i}" ${year == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';


    // اسلش بین سال و ماه
    html += '<span class="date-separator">/</span>';

    // ماه
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    html += '<select name="month" class="date-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">';
    html += '<option value="">ماه</option>';
    for (let i = 1; i <= 12; i++) {
        html += `<option value="${i}" ${month == i ? 'selected' : ''}>${months[i-1]}</option>`;
    }
    html += '</select>';

    // اسلش بین ماه و روز
    html += '<span class="date-separator">/</span>';

// روز
    html += '<select name="day" class="date-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">';
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

    let html = '<div class="search-date-group" style="display: ' +
        'flex; gap: 10px; flex-wrap: wrap; align-items: center; direction: rtl;">';

    // سال
    html += '<select class="search-date-year date-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">';
    html += '<option value="">سال</option>';
    for (let i = 1404; i <= 1420; i++) {
        html += `<option value="${i}" ${defaultYear == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';


    // اسلش بین روز و ماه
    html += '<span class="date-separator">/</span>';

    // ماه
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    html += '<select class="search-date-month date-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">';
    html += '<option value="">ماه</option>';
    for (let i = 1; i <= 12; i++) {
        html += `<option value="${i}" ${defaultMonth == i ? 'selected' : ''}>${months[i-1]}</option>`;
    }
    html += '</select>';

    // اسلش بین ماه و سال
    html += '<span class="date-separator">/</span>';

    // روز
    html += '<select class="search-date-day date-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">';
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
// توابع مودال ویرایش
// ============================================

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function openEditModal(invoice) {
    document.getElementById('edit_invoice_id').value = invoice.id;
    document.getElementById('edit_company_name').value = invoice.company_name;
    document.getElementById('edit_invoice_number').value = invoice.invoice_number;
    document.getElementById('edit_subject').value = invoice.subject || '';
    document.getElementById('edit_amount').value = invoice.amount;
    document.getElementById('edit_description').value = invoice.description || '';

    const year = invoice.invoice_date_year || '';
    const month = invoice.invoice_date_month || '';
    const day = invoice.invoice_date_day || '';

    const dateContainer = document.getElementById('edit_date_container');
    if (dateContainer) {
        dateContainer.innerHTML = renderDateSelectsForEdit(year, month, day);
    }

    document.getElementById('editModal').style.display = 'flex';
}

// ============================================
// تابع حذف فاکتور
// ============================================

function confirmDelete(id, name) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'فاکتور "' + name + '" حذف خواهد شد!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('admin_invoices.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'delete_invoice=1&invoice_id=' + id
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`tr:has(button[onclick*="confirmDelete(${id}, "])`);
                        if (row) {
                            row.remove();
                            updateRowNumbers();
                        }
                        Swal.fire({
                            title: 'حذف شد!',
                            text: 'فاکتور با موفقیت حذف شد.',
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'خطا!',
                        text: 'مشکلی در حذف فاکتور رخ داد.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                });
        }
    });
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
// توابع جستجو
// ============================================

function initSearch() {
    const urlParams = new URLSearchParams(window.location.search);

    // مقداردهی فیلدهای متنی
    const searchCompany = document.getElementById('search_company_name');
    const searchInvoice = document.getElementById('search_invoice_number');
    const searchSubject = document.getElementById('search_subject');

    if (searchCompany && urlParams.has('company_name')) searchCompany.value = urlParams.get('company_name');
    if (searchInvoice && urlParams.has('invoice_number')) searchInvoice.value = urlParams.get('invoice_number');
    if (searchSubject && urlParams.has('subject')) searchSubject.value = urlParams.get('subject');

    // مقداردهی سلکت‌های تاریخ
    renderSearchDateSelects('search_date_from_container', 'search_date_from', urlParams.get('date_from') || '');
    renderSearchDateSelects('search_date_to_container', 'search_date_to', urlParams.get('date_to') || '');

    // دکمه جستجو
    const searchBtn = document.getElementById('search_btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            const params = new URLSearchParams();
            const companyName = document.getElementById('search_company_name')?.value;
            const invoiceNumber = document.getElementById('search_invoice_number')?.value;
            const subject = document.getElementById('search_subject')?.value;
            const dateFrom = document.getElementById('search_date_from')?.value;
            const dateTo = document.getElementById('search_date_to')?.value;

            if (companyName) params.set('company_name', companyName);
            if (invoiceNumber) params.set('invoice_number', invoiceNumber);
            if (subject) params.set('subject', subject);
            if (dateFrom) params.set('date_from', dateFrom);
            if (dateTo) params.set('date_to', dateTo);

            window.location.href = 'admin_invoices.php?' + params.toString();
        });
    }

    // دکمه reset
    const resetBtn = document.getElementById('reset_btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            window.location.href = 'admin_invoices.php';
        });
    }
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
    initAddForm();
    setInterval(updateClock, 1000);
    updateClock();
});