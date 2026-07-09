//3 رقم کردن مبلغ
function amountFormat(inputId) {
    const input = document.getElementById(inputId);

    if (!input) return;

    input.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '');

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
// ============================================
// جستجوی فاکتورها
// ============================================

function searchInvoices() {

    const formData = new FormData();

    formData.append("ajax", "1");

    formData.append("company_name", document.getElementById("search_company_name").value);
    formData.append("invoice_number", document.getElementById("search_invoice_number").value);
    formData.append("subject", document.getElementById("search_subject").value);
    formData.append("date_from", document.getElementById("date_from").value);
    formData.append("date_to", document.getElementById("date_to").value);

    fetch(window.location.href, {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {

            if (!data.success) {
                alert(data.message || "خطا");
                return;
            }

            document.querySelector(".invoice-table tbody").innerHTML = data.table;

        })
        .catch(error => {
            console.error(error);
        });

}

function resetInvoiceSearch() {

    document.getElementById("search_company_name").value = "";
    document.getElementById("search_invoice_number").value = "";
    document.getElementById("search_subject").value = "";
    document.getElementById("date_from").value = "";
    document.getElementById("date_to").value = "";
    document.getElementById("quick_date_select").value = "";

    searchInvoices();

}

//ویرایش
function openEditModal(invoice) {
    try {
    document.getElementById('edit_invoice_id').value = invoice.id;
    document.getElementById('edit_company_name').value = invoice.company_name;
    document.getElementById('edit_invoice_number').value = invoice.invoice_number;
    document.getElementById('edit_subject').value = invoice.subject || '';
    document.getElementById('edit_amount').value =  Number(invoice.amount).toLocaleString('en-US');
    document.getElementById('edit_description').value = invoice.description || '';
    document.getElementById('edit_date').value = fa_number(invoice.created_at || '');

    document.getElementById('editModal').style.display = 'flex';
} catch(e){
    console.error('Error in openEditModal:', e);
    alert('خطا در باز کردن فرم ویرایش: ' + e.message);
}
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
                        const row = document.getElementById("invoice_" + id);
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

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById("search_btn").addEventListener("click", searchInvoices);
    document.getElementById("reset_btn").addEventListener("click", resetInvoiceSearch);
    initQuickDateSelect();
    amountFormat('amount');
    amountFormat('edit_amount');
});