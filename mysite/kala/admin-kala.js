// ============================================
// کدهای اختصاصی مدیریت کالاها
// ============================================

// ============================================
// جستجوی کالا
// ============================================

function searchKala() {

    const formData = new FormData();

    formData.append("ajax", "1");

    formData.append("name", document.getElementById("search_name").value);
    formData.append("computer_code", document.getElementById("search_computer_code").value);
    formData.append("property_code", document.getElementById("search_property_code").value);
    formData.append("department", document.getElementById("search_department").value);
    formData.append("brand", document.getElementById("search_brand").value);
    formData.append("date_from", faToEn(document.getElementById("date_from").value));
    formData.append("date_to", faToEn(document.getElementById("date_to").value));

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

            document.querySelector(".kala-table tbody").innerHTML = data.table;

        })
        .catch(error => {
            console.error(error);
        });

}


function resetKalaSearch() {

    document.getElementById("search_name").value = "";
    document.getElementById("search_computer_code").value = "";
    document.getElementById("search_property_code").value = "";
    document.getElementById("search_department").value = "";
    document.getElementById("search_brand").value = "";
    document.getElementById("date_from").value = "";
    document.getElementById("date_to").value = "";
    document.getElementById("quick_date_select").value = "";

    searchKala();

}
// ============================================
// ویرایش
// ============================================
function openEditModal(item) {

    document.getElementById("edit_id").value = item.id;
    document.getElementById("edit_computer_code").value = item.computer_code || "";
    document.getElementById("edit_property_code").value = item.property_code || "";
    document.getElementById("edit_name").value = item.name || "";
    document.getElementById("edit_department_id").value = String(item.department_id || "");
    document.getElementById("edit_brand_id").value = String(item.brand_id || "");
    document.getElementById("edit_receiver_person_id").value = String(item.receiver_person_id || "");
    document.getElementById("edit_quantity").value = item.quantity || "";
    document.getElementById("edit_serial_number").value = item.serial_number || "";
    document.getElementById("edit_date").value = fa_number(item.created_at || "");
    document.getElementById("edit_description").value = item.description || "";

    document.getElementById("editModal").style.display = "flex";
}

// ============================================
// حذف
// ============================================
function confirmDelete(id, name) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟', text: 'کالا "' + name + '" حذف خواهد شد!', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d', confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو', reverseButtons: true}).then((result) => {
        if (result.isConfirmed) {fetch('admin_kala.php', {method: 'POST', headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'delete_kala=1&kala_id=' + id
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const button = document.querySelector(`button[onclick*="confirmDelete(${id}"]`);
                        const row = button ? button.closest('tr') : null;
                        if (row) {
                            row.remove();
                            updateRowNumbers();
                        }
                        Swal.fire({
                            title: 'حذف شد!',
                            text: 'کالا با موفقیت حذف شد.',
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
                        text: 'مشکلی در حذف کالا رخ داد.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                });
        }
    });
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('.kala-table tbody tr');
    rows.forEach((row, index) => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            firstCell.textContent = fa_number(index + 1);
        }
    });
}
// ============================================
// مقداردهی اولیه (اختصاصی)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    initQuickDateSelect();
    document.getElementById("search_btn").addEventListener("click", searchKala);
    document.getElementById("reset_btn").addEventListener("click", resetKalaSearch);

});