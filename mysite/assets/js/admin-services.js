// ============================================
// فایل مدیریت خدمات - تمام توابع جاوااسکریپت
// ============================================

// ============================================
// 1. توابع کمکی
// ============================================

// تبدیل اعداد به فارسی
function fa_number(num) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return num.toString().replace(/\d/g, x => persianDigits[parseInt(x)]);
}

// فرار از کاراکترهای خاص (امنیت)
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// به روز رسانی شماره ردیف‌های جدول
function updateRowNumbers() {
    const rows = document.querySelectorAll('.services-table tbody tr');
    rows.forEach((row, index) => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            firstCell.textContent = fa_number(index + 1);
        }
    });
}

// دریافت شماره ردیف بعدی
function getNextRowNumber() {
    const rows = document.querySelectorAll('.services-table tbody tr');
    const emptyMessage = document.querySelector('.services-table tbody td[colspan="12"]');
    if (emptyMessage) return 1;
    return rows.length + 1;
}

// ریست کردن سلکت‌های تاریخ در فرم افزودن
function resetDateSelects() {
    const yearSelect = document.querySelector('#addServiceForm select[name="year"]');
    const monthSelect = document.querySelector('#addServiceForm select[name="month"]');
    const daySelect = document.querySelector('#addServiceForm select[name="day"]');
    if (yearSelect) yearSelect.value = '';
    if (monthSelect) monthSelect.value = '';
    if (daySelect) daySelect.value = '';
}

// ============================================
// 2. توابع رندر کردن سلکت‌های تاریخ
// ============================================

// رندر سلکت‌های تاریخ برای مودال ویرایش
function renderDateSelectsForEdit(year, month, day) {
    let html = '<div class="date-select-group">';

    // سال (چپ‌ترین)
    html += '<select name="year" class="date-select">';
    html += '<option value="">سال</option>';
    for (let i = 1390; i <= 1420; i++) {
        const selected = (year == i) ? 'selected' : '';
        html += `<option value="${i}" ${selected}>${fa_number(i)}</option>`;
    }
    html += '</select>';

    html += '<span>/</span>';

// ماه (وسط)
    html += '<select name="month" class="date-select">';
    html += '<option value="">ماه</option>';
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    for (let i = 1; i <= 12; i++) {
        const selected = (month == i) ? 'selected' : '';
        html += `<option value="${i}" ${selected}>${months[i-1]}</option>`;
    }
    html += '</select>';

    html += '<span>/</span>';

// روز (راست‌ترین)
    html += '<select name="day" class="date-select">';
    html += '<option value="">روز</option>';
    for (let i = 1; i <= 31; i++) {
        const selected = (day == i) ? 'selected' : '';
        html += `<option value="${i}" ${selected}>${fa_number(i)}</option>`;
    }
    html += '</select>';

    html += '</div>';
    return html;
}

// رندر سلکت‌های تاریخ برای بخش جستجو
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
    html += '<select class="date-select">';
    html += '<option value="">سال</option>';
    for (let i = 1404; i <= 1410; i++) {
        html += `<option value="${i}" ${defaultYear == i ? 'selected' : ''}>${fa_number(i)}</option>`;
    }
    html += '</select>';




    html += '<span>/</span>';

    // ماه
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    html += '<select class="date-select">';
    html += '<option value="">ماه</option>';
    for (let i = 1; i <= 12; i++) {
        html += `<option value="${i}" ${defaultMonth == i ? 'selected' : ''}>${months[i-1]}</option>`;
    }
    html += '</select>';
    html += '<span>/</span>';

    // روز
    html += '<select class="date-select">';
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
        const day = daySelect ? daySelect.value : '';
        const month = monthSelect ? monthSelect.value : '';
        const year = yearSelect ? yearSelect.value : '';
        const hiddenInput = document.getElementById(inputId);
        if (hiddenInput && day && month && year) {
            hiddenInput.value = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
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
// 3. توابع مودال ویرایش
// ============================================

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function openEditModal(service) {
    try {
        document.getElementById('edit_service_id').value = service.id;
        document.getElementById('edit_service_name').value = service.service_name;
        document.getElementById('edit_department_id').value = service.department_id || '';
        document.getElementById('edit_brand_id').value = service.brand_id || '';
        document.getElementById('edit_receiver_person_id').value = service.receiver_person_id || '';
        document.getElementById('edit_serial_number').value = service.serial_number || '';
        document.getElementById('edit_computer_code').value = service.computer_code || '';
        document.getElementById('edit_description').value = service.description || '';

        const year = service.service_date_year || '';
        const month = service.service_date_month || '';
        const day = service.service_date_day || '';

        const dateContainer = document.getElementById('edit_date_container');
        if (dateContainer) {
            dateContainer.innerHTML = renderDateSelectsForEdit(year, month, day);
        }

        document.getElementById('editModal').style.display = 'flex';
    } catch(e) {
        console.error('Error in openEditModal:', e);
        alert('خطا در باز کردن فرم ویرایش: ' + e.message);
    }
}

// ============================================
// 4. تابع حذف سرویس
// ============================================

function confirmDelete(id, name) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'سرویس "' + name + '" حذف خواهد شد!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('admin_services.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'delete_service=1&service_id=' + id
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
                            text: 'سرویس با موفقیت حذف شد.',
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'خطا!',
                        text: 'مشکلی در حذف سرویس رخ داد.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                });
        }
    });
}

// ============================================
// 5. توابع جدول (افزودن ردیف)
// ============================================

function addRowToTable(service) {
    const tbody = document.querySelector('.services-table tbody');
    if (!tbody) return;

    const emptyMessage = tbody.querySelector('td[colspan="12"]');
    if (emptyMessage) {
        tbody.innerHTML = '';
    }

    const row = document.createElement('tr');
    const serviceDate = service.service_date_jalali || service.service_date || '-';
    const createdDate = service.created_at ? service.created_at.split(' ')[0] : '';

    row.innerHTML = `
        <td style="padding: 10px; border: 1px solid #dee2e6;">${fa_number(getNextRowNumber())}</td>
        <td style="padding: 10px; border: 1px solid #dee2e6;">${escapeHtml(service.service_name)}</td>
        <td style="padding: 10px; border: 1px solid #dee2e6;">${escapeHtml(service.department_name || '-')}</td>
        <td style="padding: 10px; border: 1px solid #dee2e6;">${escapeHtml(service.brand_name || '-')}</td>
        <td style="padding: 10px; border: 1px solid #dee2e6;">${escapeHtml(service.receiver_name || '-')}</td>
        <td style="padding: 10px; border: 1px solid #dee2e6;">${escapeHtml(service.serial_number || '-')}</td>
        <td class="date" style="padding: 10px; border: 1px solid #dee2e6;">${fa_number(serviceDate)}</td>
        <td style="padding: 10px; border: 1px solid #dee2e6;">${escapeHtml(service.computer_code || '-')}</td>
        <td class="description-cell" style="padding: 10px; border: 1px solid #dee2e6;">${escapeHtml(service.description || '-')}</td>
        <td style="padding: 10px; border: 1px solid #dee2e6;">
            <span class="status-badge status-pending">⏳ در انتظار</span>
        </td>
        <td style="padding: 10px; border: 1px solid #dee2e6;">${fa_number(createdDate)}</td>
        <td class="action-buttons" style="padding: 10px; border: 1px solid #dee2e6;">
            <button class="edit-btn" onclick='openEditModal(${JSON.stringify(service)})'>✏️ ویرایش</button>
            <button class="delete-btn" onclick="confirmDelete(${service.id}, '${escapeHtml(service.service_name)}')">🗑️ حذف</button>
        </td>
    `;

    if (tbody.firstChild) {
        tbody.insertBefore(row, tbody.firstChild);
    } else {
        tbody.appendChild(row);
    }

    updateRowNumbers();
}
// ============================================
// توابع تاریخ برای انتخاب سریع
// ============================================

function getJalaliDate(date) {
    const persianDate = new Intl.DateTimeFormat('fa-IR-u-nu-latn', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric'
    }).format(date);
    const parts = persianDate.split('/');
    return {
        year: parseInt(parts[0]),
        month: parseInt(parts[1]),
        day: parseInt(parts[2])
    };
}
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
            quickSelect.value = '';
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
            quickSelect.value = '';
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
            quickSelect.value = '';
            return;
        }

        // ========== هفته جاری (شنبه تا جمعه) ==========
        if (selectedValue === 'this_week') {
            // پیدا کردن شنبه این هفته
            // روز هفته: 0=یکشنبه, 1=دوشنبه, ..., 5=جمعه, 6=شنبه
            const dayOfWeek = now.getDay();

            // محاسبه فاصله تا شنبه
            let daysToSaturday;
            if (dayOfWeek === 6) { // امروز شنبه
                daysToSaturday = 0;
            } else if (dayOfWeek === 0) { // امروز یکشنبه
                daysToSaturday = -6; // 6 روز به عقب
            } else {
                daysToSaturday = -(dayOfWeek); // به عقب
            }

            const saturday = new Date(now);
            saturday.setDate(now.getDate() + daysToSaturday);

            // جمعه = شنبه + 6 روز
            const friday = new Date(saturday);
            friday.setDate(saturday.getDate() + 6);

            const startOfWeekJalali = toJalali(saturday);
            const endOfWeekJalali = toJalali(friday);

            setDateRange(startOfWeekJalali, endOfWeekJalali);

            return;
        }
    });
}

// 7. توابع جستجو
// ============================================

function initSearchDates() {
    const urlParams = new URLSearchParams(window.location.search);
    const dateFrom = urlParams.get('date_from') || '';
    const dateTo = urlParams.get('date_to') || '';

    renderSearchDateSelects('search_date_from_container', 'search_date_from', dateFrom);
    renderSearchDateSelects('search_date_to_container', 'search_date_to', dateTo);
}

function initSearch() {
    initSearchDates();

    const searchBtn = document.getElementById('search_btn');
    const resetBtn = document.getElementById('reset_btn');

    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const name = document.getElementById('search_name')?.value || '';
            const department = document.getElementById('search_department')?.value || '';
            const brand = document.getElementById('search_brand')?.value || '';
            const status = document.getElementById('search_status')?.value || '';
            const dateFrom = document.getElementById('search_date_from')?.value || '';
            const dateTo = document.getElementById('search_date_to')?.value || '';

            const url = 'admin_services.php?';
            const params = [];

            if (name) params.push('name=' + encodeURIComponent(name));
            if (department) params.push('department=' + department);
            if (brand) params.push('brand=' + brand);
            if (status) params.push('status=' + status);
            if (dateFrom) params.push('date_from=' + encodeURIComponent(dateFrom));
            if (dateTo) params.push('date_to=' + encodeURIComponent(dateTo));

            window.location.href = url + params.join('&');
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            window.location.href = 'admin_services.php';
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const searchName = document.getElementById('search_name');
    const searchDept = document.getElementById('search_department');
    const searchBrand = document.getElementById('search_brand');
    const searchStatus = document.getElementById('search_status');

    if (searchName && urlParams.has('name')) searchName.value = urlParams.get('name');
    if (searchDept && urlParams.has('department')) searchDept.value = urlParams.get('department');
    if (searchBrand && urlParams.has('brand')) searchBrand.value = urlParams.get('brand');
    if (searchStatus && urlParams.has('status')) searchStatus.value = urlParams.get('status');
}

// ============================================
// 8. ساعت زنده
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
// 9. رویدادهای گلوبال
// ============================================

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
};

// ============================================
// 10. راه‌اندازی اولیه
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    initSearch();
    initQuickDateSelect();
    setInterval(updateClock, 1000);
    updateClock();
});