// ============================================
// فایل مدیریت پرینترها
// ============================================

// ============================================
// توابع جستجو
// ============================================

function searchPrinter() {

    const formData = new FormData();

    formData.append("ajax", "1");
    formData.append("computer_code", document.getElementById("search_computer_code").value);
    formData.append("property_code", document.getElementById("search_property_code").value);
    formData.append("name", document.getElementById("search_activity").value);
    formData.append("department", document.getElementById("search_department").value);
    formData.append("brand", document.getElementById("search_brand").value);
    formData.append("date_from", faToEn(document.getElementById("date_from").value));
    formData.append("date_to", faToEn(document.getElementById("date_to").value));

    fetch("admin_printers.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelector(".printers-table tbody").innerHTML = data.table;
            }
        })
        .catch(err => console.error(err));
}

function resetPrinterSearch() {

    document.getElementById("search_activity").value = "";
    document.getElementById("search_computer_code").value = "";
    document.getElementById("search_property_code").value = "";
    document.getElementById("search_department").value = "";
    document.getElementById("search_brand").value = "";
    document.getElementById("date_from").value = "";
    document.getElementById("date_to").value = "";
    document.getElementById("quick_date_select").value = "";
    searchPrinter();
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
    document.getElementById("edit_date").value = fa_number(printer.created_at || "");
    document.getElementById('edit_description').value = printer.description || '';


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
    initQuickDateSelect();
    document.getElementById("search_btn").addEventListener("click", searchPrinter);
    document.getElementById("reset_btn").addEventListener("click", resetPrinterSearch);
});