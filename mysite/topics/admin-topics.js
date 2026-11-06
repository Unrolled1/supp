function openEditModal(id, name) {
    if (!name) {
        const row = document.querySelector(`#topic_${id}`);
        if (row) {
            const nameCell = row.querySelector('td:nth-child(2)');
            if (nameCell) {
                name = nameCell.textContent.trim();
            }
        }
    }

    document.getElementById('edit_topic_id').value = id;
    document.getElementById('edit_name').value = name || '';
    document.getElementById('editModal').style.display = 'flex';
}
function savetopicsEdit() {
    const topic_id = document.getElementById('edit_topic_id').value;
    const name = document.getElementById('edit_name').value;

    if (!name.trim()) {
        Swal.fire({
            title: 'خطا!',
            text: 'نام موضوع الزامی است',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }

    const formData = new FormData();
    formData.append('edit_topic', '1');
    formData.append('topic_id', topic_id);
    formData.append('name', name);

    fetch('admin_topics.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // به‌روزرسانی نام در جدول
                const row = document.querySelector(`#topic_${data.id}`);
                if (row) {
                    const nameCell = row.querySelector('td:nth-child(2)');
                    if (nameCell) {
                        nameCell.textContent = data.name;
                    }
                }

                closeModal('editModal');

                Swal.fire({
                    title: 'موفق!',
                    text: 'موضوع با موفقیت ویرایش شد.',
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

// ============================================
// حذف فعالیت با AJAX و SweetAlert2
// ============================================

function confirmDelete(id, name) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'موضوع "' + name + '" حذف خواهد شد!',
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
            formData.append('delete_topic', '1');
            formData.append('topic_id', id);

            fetch('admin_topics.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`#topic_${data.id}`);
                        if (row) {
                            row.remove();
                            updateRowNumbers();
                        }

                        Swal.fire({
                            title: 'حذف شد!',
                            text: 'موضوع با موفقیت حذف شد.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() =>{
                            window.location.reload();
                        } );
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
// به‌روزرسانی شمارنده ردیف‌ها
// ============================================

function updateRowNumbers() {
    const rows = document.querySelectorAll('.topics-table tbody tr');
    let counter = 1;
    rows.forEach(row => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            firstCell.textContent = fa_number(counter);
            counter++;
        }
    });
}
// ============================================
// بستن مودال
// ============================================

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// ============================================
// راه‌اندازی اولیه
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // بستن مودال با کلیک روی پس‌زمینه
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };
});