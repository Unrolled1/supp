//3 رقم کردن مبلغ
function amountFormat(inputId) {
    const input = document.getElementById(inputId);

    if (!input) return;

    input.addEventListener('input', function () {
        let value = this.value.replace(/,/g, '');

        if (value === '') {
            this.value = '';
            return;
        }

        if (!isNaN(value)) {
            this.value = Number(value).toLocaleString('en-US');
        }
    });
}

//جستجو
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

//ویرایش
function openEditModal(invoice) {
    document.getElementById('edit_invoice_id').value = invoice.id;
    document.getElementById('edit_company_name').value = invoice.company_name;
    document.getElementById('edit_invoice_number').value = invoice.invoice_number;
    document.getElementById('edit_subject').value = invoice.subject || '';
    document.getElementById('edit_amount').value =  Number(invoice.amount).toLocaleString('en-US');
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

//حذف
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

document.addEventListener('DOMContentLoaded', function () {
    amountFormat('amount');
    amountFormat('edit_amount');
});