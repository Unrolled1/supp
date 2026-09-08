// ============================================
// مدیریت گزارش فعالیت‌ها
// ============================================

document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // 1. دکمه اعمال فیلتر
    // ============================================
    const filterBtn = document.querySelector('.btn-filter');
    if (filterBtn) {
        filterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            searchReport();
        });
    }

    // ============================================
    // 2. دکمه پرینت گزارش
    // ============================================
    const printBtn = document.querySelector('.btn-pdf');
    if (printBtn) {
        printBtn.addEventListener('click', function(e) {
            e.preventDefault();
            printReport();
        });
    }

    // ============================================
    // 3. دکمه Reset (اگر وجود دارد)
    // ============================================
    const resetBtn = document.querySelector('.btn-reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            resetFilters();
        });
    }

    // ============================================
    // 4. پرینت خودکار با Ctrl+P
    // ============================================
    document.addEventListener('keydown', function(e) {
        // Ctrl+P یا Cmd+P
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            // اگر در صفحه گزارش هستیم
            if (window.location.pathname.includes('admin_servicerep.php')) {
                e.preventDefault();
                printReport();
            }
        }
    });

    // ============================================
    // 5. تغییر خودکار در تاریخ‌ها (اختیاری)
    // ============================================
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');

    if (dateFrom && dateTo) {
        // وقتی تاریخ از تغییر کرد
        dateFrom.addEventListener('change', function() {
            // اگر تاریخ تا خالی است، آن را پر کن
            if (!dateTo.value && this.value) {
                dateTo.value = this.value;
            }
        });
    }
});

// ============================================
// تابع جستجو و اعمال فیلتر
// ============================================
function searchReport() {
    const form = document.getElementById("filterform");
    if (!form) {
        console.error('فرم filterform پیدا نشد');
        return;
    }

    const formData = new FormData(form);
    formData.append("ajax", "1");

    // نمایش وضعیت بارگذاری
    const tableContainer = document.querySelector(".reports-table");
    if (tableContainer) {
        tableContainer.innerHTML = '<div class="loading">⏳ در حال بارگذاری...</div>';
    }

    fetch("admin_servicerep.php", {
        method: "POST",
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('خطا در ارتباط با سرور');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // به‌روزرسانی جدول
                if (tableContainer) {
                    tableContainer.innerHTML = data.table;
                }

                // به‌روزرسانی اطلاعات فیلترها
                if (data.filterInfo) {
                    updateFilterInfo(data.filterInfo);
                }

                // اسکرول به جدول
                if (tableContainer) {
                    tableContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            } else {
                showError('خطا در دریافت داده‌ها');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('خطا در ارتباط با سرور: ' + error.message);
        });
}

// ============================================
// تابع پرینت گزارش
// ============================================
function printReport() {
    const form = document.getElementById("filterform");
    if (!form) {
        console.error('فرم filterform پیدا نشد');
        return;
    }

    const formData = new FormData(form);

    // اطلاعات چاپ
    formData.append("print", "1");
    formData.append("type", window.reportConfig?.type || "service");

    const printUrl = window.reportConfig?.printUrl || "assets/print_servicerep.php";

    showLoading('در حال آماده‌سازی گزارش برای چاپ...');

    fetch(printUrl, {
        method: "POST",
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('خطا در دریافت گزارش');
            }
            return response.text();
        })
        .then(html => {
            const win = window.open('', '_blank', 'width=1000,height=800,scrollbars=yes');

            if (!win) {
                showError('لطفاً پنجره‌های بازشو را فعال کنید');
                return;
            }

            win.document.write(html);
            win.document.close();

            win.onload = function() {
                setTimeout(function() {
                    win.focus();
                    win.print();
                }, 500);
            };
        })
        .catch(error => {
            console.error('Error:', error);
            showError('خطا در چاپ گزارش: ' + error.message);
        });
}

// ============================================
// تابع بازنشانی فیلترها
// ============================================
function resetFilters() {
    const form = document.getElementById("filterform");
    if (!form) return;

    // بازنشانی همه فیلدها
    form.reset();

    // پاک کردن تاریخ‌ها
    document.getElementById('date_from').value = '';
    document.getElementById('date_to').value = '';

    // اعمال مجدد جستجو بدون فیلتر
    searchReport();

    // نمایش پیام
    showMessage('✅ فیلترها بازنشانی شدند');
}

// ============================================
// توابع کمکی
// ============================================

// به‌روزرسانی اطلاعات فیلترها
function updateFilterInfo(filterInfo) {
    // حذف اطلاعات قبلی
    const oldInfo = document.querySelector('.filter-info');
    if (oldInfo) {
        oldInfo.remove();
    }

    // ایجاد اطلاعات جدید
    const filterCard = document.querySelector('.filter-card');
    if (filterCard && filterInfo) {
        const infoDiv = document.createElement('div');
        infoDiv.className = 'filter-info';
        infoDiv.innerHTML = filterInfo;
        filterCard.insertAdjacentElement('afterend', infoDiv);
    }
}

// نمایش پیام بارگذاری
function showLoading(message) {
    const existing = document.querySelector('.loading-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="loading-content">
            <div class="spinner"></div>
            <p>${message}</p>
        </div>
    `;
    document.body.appendChild(overlay);

    // حذف خودکار بعد از 5 ثانیه
    setTimeout(() => {
        if (overlay.parentNode) {
            overlay.remove();
        }
    }, 5000);
}

// نمایش خطا
function showError(message) {
    const existing = document.querySelector('.error-message');
    if (existing) existing.remove();

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.innerHTML = `
        <span class="error-icon">❌</span>
        <span class="error-text">${message}</span>
        <button class="error-close" onclick="this.parentElement.remove()">✖</button>
    `;

    const container = document.querySelector('.main-content');
    if (container) {
        container.insertBefore(errorDiv, container.firstChild);
    }

    // حذف خودکار بعد از 5 ثانیه
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.remove();
        }
    }, 5000);
}

// نمایش پیام موفقیت
function showMessage(message) {
    const existing = document.querySelector('.success-message');
    if (existing) existing.remove();

    const msgDiv = document.createElement('div');
    msgDiv.className = 'success-message';
    msgDiv.innerHTML = `
        <span class="success-icon">✅</span>
        <span class="success-text">${message}</span>
    `;

    const container = document.querySelector('.main-content');
    if (container) {
        container.insertBefore(msgDiv, container.firstChild);
    }

    setTimeout(() => {
        if (msgDiv.parentNode) {
            msgDiv.remove();
        }
    }, 3000);
}