// ============================================
// توابع Ajax برای فیلتر کردن بدون رفرش
// ============================================

/**
 * اعمال فیلترها با استفاده از Ajax
 * بدون رفرش صفحه، لیست تیکت‌ها را به‌روز میکند
 */
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
    fetch('admin_reports.php', {
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

/**
 * پاک کردن فیلترها با استفاده از Ajax
 */
function resetFiltersAjax() {
    // پیدا کردن فرم
    const form = document.getElementById("filterform");
    if (!form) {
        console.error('فرمی برای ریست کردن پیدا نشد!');
        return;
    }

    // ریست کردن فرم
    form.reset();

    // تنظیم مجدد سلکت‌ها
    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        const firstOption = select.querySelector('option:first-child');
        if (firstOption && firstOption.value === '') {
            select.value = '';
        } else {
            select.selectedIndex = 0;
        }
    });

    // خالی کردن ورودی‌های متنی
    const textInputs = form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], input[type="search"]');
    textInputs.forEach(input => {
        input.value = '';
    });

    // خالی کردن چک‌باکس‌ها و رادیوها
    const checkboxes = form.querySelectorAll('input[type="checkbox"], input[type="radio"]');
    checkboxes.forEach(input => {
        input.checked = false;
    });

    // ارسال درخواست Ajax برای بارگذاری مجدد بدون فیلتر
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('reset', '1');

    showLoading(true);

    fetch('admin_reports.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
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

/**
 * به‌روز کردن جدول تیکت‌ها
 */
function updateTable(html) {
    console.log("updateTable")
    const tableContainer = document.querySelector('.reports-table');
    console.log(tableContainer)
    if (tableContainer) {
        tableContainer.innerHTML = html;
    }
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
 * باز کردن پنجره پرینت گزارش (با فیلترهای فعلی)
 */
function openPrintWindow() {
    const department_id = document.querySelector('select[name="department_id"]')?.value || '';
    const status = document.querySelector('select[name="status"]')?.value || '';
    const from_day = document.querySelector('select[name="from_day"]')?.value || '';
    const from_month = document.querySelector('select[name="from_month"]')?.value || '';
    const from_year = document.querySelector('select[name="from_year"]')?.value || '';
    const to_day = document.querySelector('select[name="to_day"]')?.value || '';
    const to_month = document.querySelector('select[name="to_month"]')?.value || '';
    const to_year = document.querySelector('select[name="to_year"]')?.value || '';

    const params = [];
    if (department_id) params.push('department_id=' + encodeURIComponent(department_id));
    if (status) params.push('status=' + encodeURIComponent(status));
    if (from_year && from_month && from_day) {
        params.push('from_year=' + encodeURIComponent(from_year));
        params.push('from_month=' + encodeURIComponent(from_month));
        params.push('from_day=' + encodeURIComponent(from_day));
    }
    if (to_year && to_month && to_day) {
        params.push('to_year=' + encodeURIComponent(to_year));
        params.push('to_month=' + encodeURIComponent(to_month));
        params.push('to_day=' + encodeURIComponent(to_day));
    }
    params.push('print=1');

    let url = 'assets/print_report.php';
    if (params.length > 0) {
        url += '?' + params.join('&');
    }

    const printWindow = window.open(url, '_blank', 'width=1000,height=800,scrollbars=yes,menubar=yes');
    if (!printWindow) {
        showToast('لطفاً باز شدن پنجره جدید را در مرورگر خود مجاز کنید', 'warning');
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

// ============================================
// Event Listeners بعد از لود صفحه
// ============================================

document.addEventListener('DOMContentLoaded', function() {

    // دکمه اعمال فیلتر
    const btnFilter = document.querySelector(".btn-filter");
    if (btnFilter) {
        btnFilter.addEventListener("click", function (e) {
            e.preventDefault();
            applyFiltersAjax();
        });
    }

    const btnReset = document.querySelector(".btn-reset");
    if (btnReset) {
        btnReset.addEventListener("click", function (e) {
            e.preventDefault();
            resetFiltersAjax();
        });
    }

    const btnPdf = document.querySelector(".btn-pdf");
    if (btnPdf) {
        btnPdf.addEventListener("click", function (e) {
            e.preventDefault();
            openPrintWindow();
        });
    }

});