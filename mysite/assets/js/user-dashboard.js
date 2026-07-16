// ============================================
// فایل مدیریت داشبورد کاربر
// ============================================

// ============================================
// ثبت درخواست با AJAX
// ============================================



// ============================================
// حذف درخواست با AJAX
// ============================================

function deleteTicket(ticketId) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'این درخواست حذف خواهد شد!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('delete_ticket_ajax', '1');
            formData.append('ticket_id', ticketId);

            fetch('user_dashboard.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // حذف ردیف از جدول
                        const row = document.getElementById('ticket_' + ticketId);
                        if (row) {
                            row.remove();
                            updateRowNumbers();
                            updateTicketCount();
                        }

                        Swal.fire({
                            title: 'حذف شد!',
                            text: data.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            title: 'خطا!',
                            text: data.message || 'مشکلی در حذف رخ داد.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'خطا!',
                        text: 'مشکلی در ارتباط با سرور رخ داد.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    console.error('Error:', error);
                });
        }
    });
}

// ============================================
// اضافه کردن تیکت جدید به جدول
// ============================================

function addTicketToTable(ticket) {
    const tbody = document.getElementById('ticketsBody');
    const rowCount = tbody.querySelectorAll('tr').length;

    const tr = document.createElement('tr');
    tr.id = 'ticket_' + ticket.id;

    // تعیین کلاس وضعیت
    let statusClass = 'new';
    if (ticket.status === 'در حال بررسی') statusClass = 'review';
    else if (ticket.status === 'بسته شده') statusClass = 'closed';

    tr.innerHTML = `
        <td>${fa_number(rowCount + 1)}</td>
        <td>${fa_number(ticket.tracking_code)}</td>
        <td>${escapeHtml(ticket.department_name || '-')}</td>
        <td>${escapeHtml(ticket.subject)}</td>
        <td class="message-cell">${escapeHtml(ticket.message||'')}</td>
        <td><span class="status-badge status-${statusClass}">${escapeHtml(ticket.status)}</span></td>
        <td class="date-ltr">${fa_number(ticket.created_at)}</td>
        <td><button class="delete-btn-table" onclick="deleteTicket(${ticket.id})">🗑️</button></td>
    `;

    tbody.prepend(tr);
    updateRowNumbers();
    updateTicketCount();
}

// ============================================
// به‌روزرسانی شمارنده ردیف‌ها
// ============================================

function updateRowNumbers() {
    const rows = document.querySelectorAll('#ticketsBody tr');
    rows.forEach((row, index) => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            firstCell.textContent = fa_number(index + 1);
        }
    });
}

// ============================================
// به‌روزرسانی تعداد تیکت‌ها
// ============================================

function updateTicketCount() {
    const count = document.querySelectorAll('#ticketsBody tr').length;
    const title = document.querySelector('.tickets-table h3');
    if (title) {
        title.textContent = '📋 درخواست‌های قبلی (' + fa_number(count) + ')';
    }
}

// ============================================
// نمایش پیام در فرم
// ============================================

function showFormMessage(text, type) {
    const container = document.getElementById('form_message');
    if (container) {
        container.innerHTML = `<div class="alert alert-${type === 'success' ? 'success' : 'error'}">${text}</div>`;
        // حذف پیام بعد از 5 ثانیه
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }
}

// ============================================
// توابع کمکی
// ============================================

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

document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitTicket');
    const form = document.getElementById('ticketForm');

    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            // جمع‌آوری داده‌ها
            const fullname = document.getElementById('fullname').value;
            const department_id = document.getElementById('department_id').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;

            // اعتبارسنجی ساده
            if (!fullname || !department_id || !subject || !message) {
                showFormMessage('❌ لطفاً همه فیلدها را پر کنید.', 'error');
                return;
            }

            // غیرفعال کردن دکمه
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ در حال ارسال...';

            // ارسال درخواست AJAX
            const formData = new FormData();
            formData.append('add_ticket_ajax', '1');
            formData.append('fullname', fullname);
            formData.append('department_id', department_id);
            formData.append('subject', subject);
            formData.append('message', message);

            fetch('user_dashboard.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {

                        // اضافه کردن تیکت جدید به جدول
                        addTicketToTable(data.ticket);

                        // ریست فرم
                        form.reset();

                        Swal.fire({
                            title: 'موفق!',
                            text: 'درخواست شما با موفقیت ثبت شد. کد پیگیری: ' + fa_number(data.tracking_code),
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            timer: 2000
                        });
                    } else {
                        showFormMessage('❌ ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showFormMessage('❌ خطا در ارتباط با سرور', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '📨 ارسال درخواست';
                });
        });
    }
});