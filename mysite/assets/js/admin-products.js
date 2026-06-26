// ============================================
// مدیریت کالاها (Products)
// ============================================

function openEditModal(id) {
    // پیدا کردن ردیف کالا در جدول
    const row = document.querySelector(`#product_${id}`);
    // دریافت اطلاعات از سلول‌های جدول
    const nameCell = row.querySelector('td:nth-child(2)');
    const brandCell = row.querySelector('td:nth-child(3)');

    // قرار دادن اطلاعات در مودال
    document.getElementById('edit_product_id').value = id;
    document.getElementById('edit_name').value = nameCell ? nameCell.textContent.trim() : '';

    // تنظیم برند در سلکت باکس
    const brandSelect = document.getElementById('edit_brand_id');
    const brandName = brandCell ? brandCell.textContent.trim() : '';

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

    // نمایش مودال
    document.getElementById('editModal').style.display = 'flex';
}

/**
 * ذخیره ویرایش کالا با Ajax
 */
function saveEdit() {
    const product_id = document.getElementById('edit_product_id').value;
    const name = document.getElementById('edit_name').value;
    const brand_id = document.getElementById('edit_brand_id').value;

    // اعتبارسنجی
    if (!name.trim()) {
        Swal.fire({
            title: 'خطا!',
            text: 'نام کالا الزامی است',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }

    if (!product_id || isNaN(product_id) || product_id <= 0) {
        Swal.fire({
            title: 'خطا!',
            text: 'شناسه کالا نامعتبر است',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }

    // آماده‌سازی داده برای ارسال
    const formData = new FormData();
    formData.append('edit_product', '1');
    formData.append('product_id', product_id);
    formData.append('name', name);
    formData.append('brand_id', brand_id);

    // ارسال درخواست Ajax
    fetch('admin_products.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // به‌روزرسانی ردیف جدول
                const row = document.querySelector(`#product_${data.id}`);
                if (row) {
                    const nameCell = row.querySelector('td:nth-child(2)');
                    if (nameCell) {
                        nameCell.textContent = data.name;
                    }

                    const brandCell = row.querySelector('td:nth-child(3)');
                    if (brandCell) {
                        const brandSelect = document.getElementById('edit_brand_id');
                        const brandName = brandSelect ? brandSelect.options[brandSelect.selectedIndex]?.text || '-' : '-';
                        brandCell.textContent = brandName;
                    }
                }

                // بستن مودال
                closeModal('editModal');

                // نمایش پیام موفقیت
                Swal.fire({
                    title: 'موفق!',
                    text: 'کالا با موفقیت ویرایش شد',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    title: 'خطا!',
                    text: data.message || 'مشکلی در ویرایش رخ داد',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'خطا!',
                text: 'مشکلی در ارتباط با سرور رخ داد',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
            console.error('Error:', error);

        });
}

function confirmDelete(id, name) {

    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: `کالا "${name}" حذف خواهد شد!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // آماده‌سازی داده برای ارسال
            const formData = new FormData();
            formData.append('delete_product', '1');
            formData.append('product_id', id);

            // نمایش لودینگ
            Swal.fire({
                title: 'در حال حذف...',
                text: 'لطفاً صبر کنید',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // ارسال درخواست Ajax
            fetch('admin_products.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // حذف ردیف از جدول
                        const row = document.querySelector(`#product_${id}`);
                        if (row) {
                            row.remove();
                            updateRowNumbers();
                        }

                        Swal.fire({
                            title: 'حذف شد!',
                            text: 'کالا با موفقیت حذف شد',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            title: 'خطا!',
                            text: data.message || 'مشکلی در حذف رخ داد',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'خطا!',
                        text: 'مشکلی در ارتباط با سرور رخ داد',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    console.error('Error:', error);
                });
        }
    });
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('.products-table tbody tr');
    let counter = 1;
    rows.forEach(row => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            // تابع fa_number باید در alljs.js تعریف شده باشد
            if (typeof fa_number === 'function') {
                firstCell.textContent = fa_number(counter);
            } else {
                firstCell.textContent = counter;
            }
            counter++;
        }
    });
}

// ============================================
// رویدادها
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // بستن مودال با کلیک بیرون
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };

    // بستن مودال با کلید Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                }
            });
        }
    });
});