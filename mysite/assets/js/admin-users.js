// ============================================
// مدیریت کاربران - نسخه کامل
// ============================================

// ============================================
// متغیرهای سراسری
// ============================================

var allPermissions = [];
var permissionLabels = {};
var existingPermissions = {};
var permissionTree = [];

// ============================================
// توابع مدیریت درخت دسترسی‌ها
// ============================================

function buildPermissionTree(containerId, existingPerms) {
    var container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';

    // ساختار دو ستونی
    var gridHtml = '<div class="permission-grid">';

    permissionTree.forEach(function(node) {
        gridHtml += '<div class="permission-col">';
        gridHtml += buildTreeNode(node, existingPerms);
        gridHtml += '</div>';
    });

    gridHtml += '</div>';

    container.innerHTML = gridHtml;

    // باز کردن همه شاخه‌ها
    document.querySelectorAll('#' + containerId + ' .tree-toggle').forEach(function(el) {
        var childrenDiv = el.parentElement.nextElementSibling;
        if (childrenDiv) {
            childrenDiv.classList.add('show');
            el.textContent = '▼';
        }
    });
}

// ============================================
// ساخت نود درخت - با کلیک روی کل ردیف
// ============================================

function buildTreeNode(node, existingPerms, level = 0) {

    let html = '<div class="tree-node level-' + level + '">';

    if (node.children && node.children.length > 0) {

        html += `
            <div class="tree-node-main">

                <span class="tree-toggle"
                    onclick="toggleTree(this)">▼</span>

                <input type="checkbox"
                    onchange="toggleChildren(this)">

                <label>${node.name}</label>

            </div>

            <div class="tree-children show">
        `;

        node.children.forEach(child => {
            html += buildTreeNode(child, existingPerms, level + 1);
        });

        html += '</div>';

    } else {

        let checked = existingPerms[node.key] == 1 ? 'checked' : '';

        html += `
            <div class="tree-child">

                <input
                    type="checkbox"
                    name="perm_${node.key}"
                    id="perm_${node.key}"
                    value="1"
                    ${checked}
                    onchange="updateParents(this)">

                <label for="perm_${node.key}">
                    ${node.name}
                </label>

            </div>
        `;
    }

    html += '</div>';

    return html;
}


// باز و بسته کردن درخت - یک تابع برای همه
// ============================================

function toggleTree(el){

    const children = el.parentElement.nextElementSibling;

    if(!children) return;

    children.classList.toggle("show");

    el.textContent =
        children.classList.contains("show")
            ? "▼"
            : "▶";

}

// ============================================
// کلیک روی کل ردیف (همان toggleTree صدا زده می‌شود)
// ============================================

function toggleTreeByRow(event, element) {
    // اگر روی چک‌باکس کلیک شده، فقط تیک بخورد
    if (event.target.type === 'checkbox') return;

    // پیدا کردن دکمه toggle در همان ردیف
    var toggleSpan = element.querySelector('.tree-toggle');
    if (toggleSpan) {
        toggleTree(toggleSpan);
    }
}

// ============================================
// توابع مدیریت چک‌باکس‌ها
// ============================================

function toggleChildren(parent){

    const node = parent.closest(".tree-node");

    const checkboxes =
        node.querySelectorAll(".tree-children input[type=checkbox]");

    checkboxes.forEach(cb=>{

        cb.checked = parent.checked;
        cb.indeterminate = false;

    });

}

function updateParents(child){

    let parentNode =
        child.closest(".tree-children")
            ?.closest(".tree-node");

    while(parentNode){

        const parentCheckbox =
            parentNode.querySelector(":scope > .tree-node-main input");

        const children =
            parentNode.querySelectorAll(":scope > .tree-children input");

        let checked = 0;

        children.forEach(c=>{
            if(c.checked) checked++;
        });

        if(parentCheckbox){

            if(checked==0){

                parentCheckbox.checked=false;
                parentCheckbox.indeterminate=false;

            }
            else if(checked==children.length){

                parentCheckbox.checked=true;
                parentCheckbox.indeterminate=false;

            }
            else{

                parentCheckbox.checked=false;
                parentCheckbox.indeterminate=true;

            }

        }

        parentNode =
            parentNode.parentElement
                ?.closest(".tree-node");
    }

}

// ============================================
// توابع باز کردن مودال‌ها
// ============================================

function openEditModal(id, username, fullname, role) {
    var currentUserId = parseInt(document.getElementById('current_user_id').value || 0);

    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_username').value = username || '';
    document.getElementById('edit_fullname').value = fullname || '';

    var roleSelect = document.getElementById('edit_role');
    roleSelect.value = role || 'user';

    // غیرفعال کردن تغییر نقش برای کاربر فعلی
    if (id == currentUserId) {
        roleSelect.disabled = true;
        roleSelect.style.opacity = '0.6';
        roleSelect.style.cursor = 'not-allowed';
    } else {
        roleSelect.disabled = false;
        roleSelect.style.opacity = '1';
        roleSelect.style.cursor = 'pointer';
    }

    // نمایش یا مخفی کردن بخش دسترسی‌ها
    var accessSection = document.getElementById('edit_access_section');
    if (role === 'admin') {
        accessSection.style.display = 'block';
        var userPerms = existingPermissions[id] || {};
        buildPermissionTree('edit_permissions_grid', userPerms);

        // اگر کاربر خودش باشد، همه چک‌باکس‌ها غیرفعال می‌شوند
        if (id == currentUserId) {
            var allCheckboxes = accessSection.querySelectorAll('input[type="checkbox"]');
            for (var i = 0; i < allCheckboxes.length; i++) {
                allCheckboxes[i].disabled = true;
                allCheckboxes[i].checked = true;
            }
        }
    } else {
        accessSection.style.display = 'none';
    }

    document.getElementById('editModal').style.display = 'flex';
}

function openPasswordModal(id) {
    document.getElementById('password_user_id').value = id;
    document.getElementById('passwordModal').style.display = 'flex';
}


// ============================================
// تابع ذخیره ویرایش کاربر (Ajax)
// ============================================

function saveusersEdit() {
    var user_id = document.getElementById('edit_user_id').value;
    var username = document.getElementById('edit_username').value;
    var fullname = document.getElementById('edit_fullname').value;
    var role = document.getElementById('edit_role').value;

    if (!username.trim() || !fullname.trim()) {
        Swal.fire({
            title: 'خطا!',
            text: 'نام کاربری و نام کامل الزامی است',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }

    // جمع‌آوری دسترسی‌ها
    var permissions = {};
    var checkboxes = document.querySelectorAll('#edit_permissions_grid input[type="checkbox"]');
    checkboxes.forEach(function(cb) {
        var name = cb.name.replace('perm_', '');
        permissions[name] = cb.checked ? 1 : 0;
    });

    var formData = new FormData();
    formData.append('edit_user', '1');
    formData.append('user_id', user_id);
    formData.append('username', username);
    formData.append('fullname', fullname);
    formData.append('role', role);
    formData.append('permissions', JSON.stringify(permissions));

    var saveBtn = document.querySelector('.btn-add');
    var originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = '⏳ در حال ذخیره...';

    fetch('admin_users.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var row = document.querySelector('#user_' + data.id);
                if (row) {
                    var cells = row.querySelectorAll('td');
                    if (cells.length >= 5) {
                        cells[1].textContent = data.username;
                        cells[2].textContent = data.fullname;
                        var roleBadge = cells[3].querySelector('.role-badge');
                        if (roleBadge) {
                            roleBadge.textContent = data.role === 'admin' ? 'ادمین' : 'کاربر عادی';
                            roleBadge.className = 'role-badge ' + (data.role === 'admin' ? 'role-admin' : 'role-user');
                        }
                    }
                }

                closeModal('editModal');

                Swal.fire({
                    title: 'موفق!',
                    text: 'کاربر با موفقیت ویرایش شد',
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
        .catch(function(error) {
            Swal.fire({
                title: 'خطا!',
                text: 'مشکلی در ارتباط با سرور رخ داد',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
            console.error('Error:', error);
        })
        .finally(function() {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        });
}

// ============================================
// تابع حذف کاربر (Ajax)
// ============================================

// ============================================
// تابع حذف کاربر (Ajax) - با دیباگ
// ============================================

// ============================================
// تابع حذف کاربر (Ajax) - بدون لودینگ
// ============================================

// ============================================
// تابع حذف کاربر (Ajax) - با پیدا کردن ردیف
// ============================================

function confirmDelete(id, username) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'کاربر "' + username + '" حذف خواهد شد!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو',
        reverseButtons: true
    }).then(function(result) {
        if (result.isConfirmed) {
            var formData = new FormData();
            formData.append('delete_user', '1');
            formData.append('user_id', id);

            fetch('admin_users.php', {
                method: 'POST',
                body: formData
            })
                .then(function(response) {
                    return response.text();
                })
                .then(function(text) {
                    try {
                        var data = JSON.parse(text);
                        if (data.success) {
                            // ✅ روش 1: با id
                            var row = document.querySelector('#user_' + id);
                            if (row) {
                                row.remove();
                            } else {
                                // ✅ روش 2: پیدا کردن با دکمه حذف
                                var allRows = document.querySelectorAll('.users-table tbody tr');
                                for (var i = 0; i < allRows.length; i++) {
                                    var deleteBtn = allRows[i].querySelector('.delete-btn');
                                    if (deleteBtn) {
                                        var onclickAttr = deleteBtn.getAttribute('onclick');
                                        if (onclickAttr && onclickAttr.includes('confirmDelete(' + id + ',')) {
                                            allRows[i].remove();
                                            break;
                                        }
                                    }
                                }
                            }

                            // بروزرسانی شماره ردیف‌ها
                            updateRowNumbers();

                            Swal.fire({
                                title: 'حذف شد!',
                                text: 'کاربر با موفقیت حذف شد',
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
                    } catch(e) {
                        console.error('JSON Parse Error:', e);
                        Swal.fire({
                            title: 'خطا!',
                            text: 'پاسخ سرور نامعتبر است',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(function(error) {
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
// ============================================
// توابع کمکی
// ============================================

function updateRowNumbers() {
    var rows = document.querySelectorAll('.users-table tbody tr');
    var counter = 1;
    rows.forEach(function(row) {
        var firstCell = row.querySelector('td:first-child');
        if (firstCell) {
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
    // دریافت داده‌ها از window.userData
    if (window.userData) {
        allPermissions = window.userData.allPermissions || [];
        permissionLabels = window.userData.permissionLabels || {};
        existingPermissions = window.userData.existingPermissions || {};
        permissionTree = window.userData.permissionTree || [];
    } else {
        console.warn('userData not found!');
    }

    // بستن مودال با کلیک بیرون
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };

    // بستن مودال با کلید Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(function(modal) {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                }
            });
        }
    });

    // رویداد تغییر نقش
    var editRole = document.getElementById('edit_role');
    if (editRole) {
        editRole.addEventListener('change', function() {
            var accessSection = document.getElementById('edit_access_section');
            var userId = document.getElementById('edit_user_id').value;
            var currentUserId = window.userData?.currentUserId || 0;

            if (this.value === 'admin') {
                accessSection.style.display = 'block';
                var userPerms = existingPermissions[userId] || {};
                buildPermissionTree('edit_permissions_grid', userPerms);

                if (userId == currentUserId) {
                    var allCheckboxes = accessSection.querySelectorAll('input[type="checkbox"]');
                    for (var i = 0; i < allCheckboxes.length; i++) {
                        allCheckboxes[i].disabled = true;
                        allCheckboxes[i].checked = true;
                    }
                }
            } else {
                accessSection.style.display = 'none';
            }
        });
    }
});