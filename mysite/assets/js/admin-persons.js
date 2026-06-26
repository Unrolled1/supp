// ============================================
// فایل مدیریت اشخاص
// ============================================

function openEditModal(id, name) {
    if (!name) {
        const row = document.querySelector(`#person_${id}`);
        if (row) {
            const nameCell = row.querySelector('td:nth-child(2)');
            if (nameCell) {
                name = nameCell.textContent.trim();
            }
        }
    }

    document.getElementById('edit_person_id').value = id;
    document.getElementById('edit_name').value = name || '';
    document.getElementById('editModal').style.display = 'flex';
}

function savepersonEdit() {
    const person_id = document.getElementById('edit_person_id').value;
    const name = document.getElementById('edit_name').value;

    if (!name.trim()) {
        Swal.fire({
            title: 'خطا!',
            text: 'نام شخص الزامی است',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }

    const formData = new FormData();
    formData.append('edit_person', '1');
    formData.append('person_id', person_id);
    formData.append('name', name);

    fetch('admin_persons.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.querySelector(`#person_${data.id}`);
                if (row) {
                    const nameCell = row.querySelector('td:nth-child(2)');
                    if (nameCell) {
                        nameCell.textContent = data.name;
                    }
                }

                closeModal('editModal');

                Swal.fire({
                    title: 'موفق!',
                    text: 'شخص با موفقیت ویرایش شد.',
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
        text: 'شخص "' + name + '" حذف خواهد شد!',
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
            formData.append('delete_person', '1');
            formData.append('person_id', id);

            fetch('admin_persons.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`#person_${id}`);
                        if (row) {
                            row.remove();
                            updateRowNumbers();
                        }

                        Swal.fire({
                            title: 'حذف شد!',
                            text: 'شخص با موفقیت حذف شد.',
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
    const rows = document.querySelectorAll('.persons-table tbody tr');
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