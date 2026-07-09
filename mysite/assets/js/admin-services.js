

// ============================================
// 3. توابع مودال ویرایش
// ============================================

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
        document.getElementById('edit_date').value = fa_number(service.created_at || '');

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
            fetch(window.location.href, {
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
                        const btn = document.querySelector(`button[data-id="${id}"]`);

                        if (btn) {
                            const row = btn.closest('tr');
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
function updateRowNumbers() {
    const rows = document.querySelectorAll('.services-table tbody tr');
    rows.forEach((row, index) => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            firstCell.textContent = fa_number(index + 1);
        }
    });
}

// 7. توابع جستجو
// ============================================

function searchActivities() {

    const formData = new FormData();

    formData.append("ajax", "1");

    formData.append("name", document.getElementById("search_name").value);
    formData.append("department", document.getElementById("search_department").value);
    formData.append("brand", document.getElementById("search_brand").value);
    formData.append("receiver", document.getElementById("search_receiver").value);
    formData.append("computer_code", document.querySelector("input[name='computer_code']").value);
    formData.append("date_from", faToEn(document.getElementById("date_from").value));
    formData.append("date_to", faToEn(document.getElementById("date_to").value));

    fetch(window.location.href, {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {

            if (!data.success) {
                showToast(data.message || "خطا", "error");
                return;
            }

            document.querySelector(".services-table tbody").innerHTML = data.table;

        })
        .catch(error => {
            console.error(error);
            showToast("خطا در ارتباط با سرور", "error");
        });

}

function resetSearch() {

    document.getElementById("search_name").value = "";
    document.getElementById("search_department").value = "";
    document.getElementById("search_brand").value = "";
    document.getElementById("search_receiver").value = "";
    document.querySelector("input[name='computer_code']").value = "";
    document.getElementById("date_from").value = "";
    document.getElementById("date_to").value = "";
    document.getElementById("quick_date_select").value = "";

    searchActivities();
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

document.addEventListener("DOMContentLoaded", function () {
    initQuickDateSelect();
    document.getElementById("search_btn").addEventListener("click", searchActivities);
    document.getElementById("reset_btn").addEventListener("click", resetSearch);
});