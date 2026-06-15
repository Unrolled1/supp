

//جستجو
function initSearch() {
    const searchBtn = document.getElementById('search_btn');
    const resetBtn = document.getElementById('reset_btn');

    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const params = new URLSearchParams();
            const name = document.getElementById('search_name')?.value;
            const computerCode = document.getElementById('search_computer_code')?.value;
            const propertyCode = document.getElementById('search_property_code')?.value;
            const department = document.getElementById('search_department')?.value;
            const brand = document.getElementById('search_brand')?.value;

            if (name) params.set('name', name);
            if (computerCode) params.set('computer_code', computerCode);
            if (propertyCode) params.set('property_code', propertyCode);
            if (department) params.set('department', department);
            if (brand) params.set('brand', brand);

            window.location.href = 'admin_kala.php?' + params.toString();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            window.location.href = 'admin_kala.php';
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const searchName = document.getElementById('search_name');
    const searchComputerCode = document.getElementById('search_computer_code');
    const searchPropertyCode = document.getElementById('search_property_code');
    const searchDept = document.getElementById('search_department');
    const searchBrand = document.getElementById('search_brand');

    if (searchName && urlParams.has('name')) searchName.value = urlParams.get('name');
    if (searchComputerCode && urlParams.has('computer_code')) searchComputerCode.value = urlParams.get('computer_code');
    if (searchPropertyCode && urlParams.has('property_code')) searchPropertyCode.value = urlParams.get('property_code');
    if (searchDept && urlParams.has('department')) searchDept.value = urlParams.get('department');
    if (searchBrand && urlParams.has('brand')) searchBrand.value = urlParams.get('brand');
}

//ویرایش
function openEditModal(kala) {
    document.getElementById('edit_kala_id').value = kala.id;
    document.getElementById('edit_name').value = kala.name;
    document.getElementById('edit_computer_code').value = kala.computer_code || '';
    document.getElementById('edit_property_code').value = kala.property_code || '';
    document.getElementById('edit_department_id').value = kala.department_id || '';
    document.getElementById('edit_brand_id').value = kala.brand_id || '';
    document.getElementById('edit_quantity').value = kala.quantity || 1;
    document.getElementById('edit_serial_number').value = kala.serial_number || '';
    document.getElementById('edit_receiver_person_id').value = kala.receiver_person_id || '';

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
            fetch('admin_kala.php', {
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
