// ============================================
// فایل مدیریت مدل‌ها
// ============================================

function openEditModal(id, name) {
        const row = document.querySelector(`#model_${id}`);
        if (row) {
            const nameCell = row.querySelector('td:nth-child(2)');
            const brandCell = row.querySelector('td:nth-child(3)');     // ستون برند

            document.getElementById('edit_model_id').value = id;
            document.getElementById('edit_name').value = nameCell ? nameCell.textContent.trim() : '';

            const brandName = brandCell ? brandCell.textContent.trim() : '';
            const brandSelect = document.getElementById('edit_brand_id');
            if (brandSelect && brandName && brandName !== '-') {
                let found = false;
                for (let option of brandSelect.options) {
                    if (option.text === brandName) {
                        brandSelect.value = option.value;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    brandSelect.value = '';
                }
            } else {
                brandSelect.value = '';
            }
        }

    document.getElementById('editModal').style.display = 'flex';
}
function saveEdit() {
    const model_id = document.getElementById('edit_model_id').value;
    const name = document.getElementById('edit_name').value;
    const brand_id = document.getElementById('edit_brand_id').value;

    if (!name.trim()) {
        Swal.fire({
            title: 'خطا!',
            text: 'نام مدل الزامی است',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }

    const formData = new FormData();
    formData.append('edit_model', '1');
    formData.append('model_id', model_id);
    formData.append('name', name);
    formData.append('brand_id', brand_id);

    fetch('admin_models.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.querySelector(`#model_${data.id}`);
                if (row) {
                    const nameCell = row.querySelector('td:nth-child(2)');
                    if (nameCell) {
                        nameCell.textContent = data.name;
                    }
                    // به‌روزرسانی برند
                    const brandCell = row.querySelector('td:nth-child(3)');
                    if (brandCell) {
                        const brandSelect = document.getElementById('edit_brand_id');
                        const brandName = brandSelect ? brandSelect.options[brandSelect.selectedIndex]?.text || '-' : '-';
                        brandCell.textContent = brandName;
                    }
                }
                document.getElementById('edit_name').value = data.name;
                closeModal('editModal');

                Swal.fire({
                    title: 'موفق!',
                    text: 'مدل با موفقیت ویرایش شد.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    title: 'خطا!',
                    text: data.message || 'مشکلی در ویرایش رخ داد.',
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

function confirmDelete(id, name) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'مدل "' + name + '" حذف خواهد شد!',
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
            formData.append('delete_model', '1');
            formData.append('model_id', id);

            fetch('admin_models.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`#model_${id}`);
                        if (row) {
                            row.remove();
                            updateRowNumbers();
                        }

                        Swal.fire({
                            title: 'حذف شد!',
                            text: 'مدل با موفقیت حذف شد.',
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

function updateRowNumbers() {
    const rows = document.querySelectorAll('.models-table tbody tr');
    let counter = 1;
    rows.forEach(row => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            firstCell.textContent = fa_number(counter);
            counter++;
        }
    });
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };
});