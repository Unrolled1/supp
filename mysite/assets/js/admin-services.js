

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
    const today=toJalali(new Date());
    renderDateSelects('service_date_container',today.year,today.month,today.day);
    initSearch();
    initQuickDateSelect();
});