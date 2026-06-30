// ============================================
// فایل مدیریت سیستم‌ها
// ============================================

// ============================================
// جستجو
// ============================================

function initSearch() {
    const searchBtn = document.getElementById('search_btn');
    const resetBtn = document.getElementById('reset_btn');

    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const params = new URLSearchParams();

            const computerCode = document.getElementById('search_computer_code')?.value;
            const name = document.getElementById('search_name')?.value;
            const department = document.getElementById('search_department')?.value;

            if (computerCode) params.set('computer_code', computerCode);
            if (name) params.set('name', name);
            if (department) params.set('department', department);

            window.location.href = 'admin_systems.php?' + params.toString();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            window.location.href = 'admin_systems.php';
        });
    }
}

// ============================================
// تابع عمومی دریافت داده از سرور
// ============================================

function getSystemData(systemId, type, callback) {
    fetch(`get_system_data.php?system_id=${systemId}&type=${type}`)
        .then(response => response.json())
        .then(data => {
            if (callback) callback(data);
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'خطا!',
                text: 'مشکلی در دریافت اطلاعات رخ داد.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        });
}

function getSelectOptions(selector) {
    const select = document.querySelector(selector);

    if (!select) return '';

    return [...select.options]
        .filter(opt => opt.value !== '')
        .map(opt => `<option value="${opt.value}">${opt.text}</option>`)
        .join('');
}

function createSelectRow(config) {

    const container = document.getElementById(config.containerId);

    const rowCount =
        container.querySelectorAll("." + config.rowClass).length;

    const firstSelect =
        document.querySelector(config.firstSelect);

    let options = "";

    if (firstSelect) {
        options = firstSelect.innerHTML;
    }

    const row = document.createElement("div");

    row.className = config.rowClass;

    row.dataset.row = rowCount;

    row.innerHTML = `

        <label class="section-label">
            ${config.label}
        </label>

        <div class="select-wrapper">

            <select
                name="${config.name}_${rowCount}"
                class="${config.selectClass}">
                ${options}
            </select>

            ${config.quickButton || ""}

            <button
                type="button"
                class="${config.removeClass}"
                onclick="${config.removeFunction}(this)"
                ${rowCount===0?"hidden":""}>
                🗑️
            </button>

        </div>

    `;

    container.appendChild(row);

}

function removeDynamicRow(button, rowClass, containerId, itemName) {

    const row = button.closest("." + rowClass);
    const container = document.getElementById(containerId);

    if (container.querySelectorAll("." + rowClass).length <= 1) {
        Swal.fire({
            title: "خطا!",
            text: `حداقل یک ${itemName} باید وجود داشته باشد.`,
            icon: "warning",
            confirmButtonColor: "#ffc107"
        });
        return;
    }

    Swal.fire({
        title: "آیا مطمئن هستید؟",
        text: `این ${itemName} حذف خواهد شد!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "بله",
        cancelButtonText: "لغو"
    }).then(result => {

        if (result.isConfirmed) {
            row.remove();

            updateRowNumbers(rowClass);
        }

    });

}

function createHtmlRow(containerId, rowClass, html) {

    const container = document.getElementById(containerId);

    const row = document.createElement("div");

    row.className = rowClass;

    row.dataset.row = container.querySelectorAll("." + rowClass).length;

    row.innerHTML = html;

    container.appendChild(row);

}

// ============================================
// مدیریت رم‌ها (افزودن در فرم ثبت)
// ============================================

function addRamRow(){

    createSelectRow({

        containerId:"rams_container",

        rowClass:"ram-row",

        firstSelect:'select[name="ram_id_0"]',

        name:"ram_id",

        selectClass:"ram-select",

        removeClass:"btn-remove-ram",

        removeFunction:"removeRamRow",

        label:"رم",

    });

}

function removeRamRow(button) {
    removeDynamicRow(button, "ram-row", "rams_container", "رم");
}
// ============================================
// مدیریت هاردها (افزودن در فرم ثبت)
// ============================================

function addStorageRow(){

    createSelectRow({

        containerId:"storages_container",

        rowClass:"storage-row",

        firstSelect:'select[name="storage_id_0"]',

        name:"storage_id",

        selectClass:"storage-select",

        removeClass:"btn-remove-storage",

        removeFunction:"removeStorageRow",

        label:"هارد",


    });

}

function removeStorageRow(button) {
    removeDynamicRow(button, "storage-row", "storages_container", "هارد");
}

// ============================================
// مدیریت IPها (افزودن در فرم ثبت)
// ============================================

function addIpRow() {

    createHtmlRow(
        "ips_container",
        "ip-row",
        `
        <label class="section-label">IP</label>

        <div class="ip-inline">

            <input type="text" name="ip_address_${document.querySelectorAll('#ips_container .ip-row').length}">

            <select name="ip_network_${document.querySelectorAll('#ips_container .ip-row').length}">
                <option value="LAN">LAN</option>
                <option value="WAN">WAN</option>
                <option value="VPN">VPN</option>
                <option value="WiFi">WiFi</option>
                <option value="Other">سایر</option>
            </select>

            <button
                type="button"
                class="btn-remove-ip"
                onclick="removeIpRow(this)">
                🗑️
            </button>

        </div>
        `
    );

}

function removeIpRow(button) {
    removeDynamicRow(button, "ip-row", "ips_container", "آدرس IP");
}
// ============================================
// مدیریت تجهیزات جانبی (افزودن در فرم ثبت)
// ============================================

function addPeripheralRow() {

    const row = document.querySelectorAll("#peripherals_container .peripheral-row").length;

    const options =
        document.querySelector('select[name="peripheral_id_0"]').innerHTML;

    createHtmlRow(
        "peripherals_container",
        "peripheral-row",
        `
        <label class="section-label">تجهیزات جانبی</label>

        <div class="select-wrapper">

            <select
                name="peripheral_id_${row}"
                class="peripheral-select">

                ${options}

            </select>

            <button
                type="button"
                class="btn-remove-peripheral"
                onclick="removePeripheralRow(this)">
                🗑️
            </button>

        </div>
        `
    );

}

function removePeripheralRow(button) {
    removeDynamicRow(button, "peripheral-row", "peripherals_container", "تجهیز جانبی");
}
// ============================================
// به‌روزرسانی شمارنده ردیف‌ها
// ============================================

function updateRowNumbers(className) {
    const rows = document.querySelectorAll(`.${className}`);
    rows.forEach((row, index) => {
        const inputs = row.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.name;
            if (name) {
                input.name = name.replace(/_\d+$/, `_${index}`);
            }
        });
    });
    rows.forEach((row, index) => {

        const removeBtn = row.querySelector(
            ".btn-remove-ram, .btn-remove-storage, .btn-remove-ip, .btn-remove-peripheral"
        );

        if (!removeBtn) return;

        if (index === 0) {
            removeBtn.hidden = true;
        } else {
            removeBtn.hidden = false;
        }

    });
}

// ============================================
// بارگذاری داده‌ها در مودال ویرایش
// ============================================

function loadEditData(systemId) {
    // دریافت همه داده‌ها با یک درخواست
    getSystemData(systemId, 'all', function(data) {
        // بارگذاری رم‌ها
        loadEditRams(data.rams || []);
        // بارگذاری هاردها
        loadEditStorages(data.storages || []);
        // بارگذاری IPها
        loadEditIps(data.ips || []);
        // بارگذاری تجهیزات جانبی
        loadEditPeripherals(data.peripherals || []);
    });
}

// ============================================
// بارگذاری رم‌ها در مودال ویرایش
// ============================================

function loadEditRams(rams) {
    const container = document.getElementById('edit_rams_container');
    container.innerHTML = '';

    if (!rams || rams.length === 0) {
        addEditRamRow();
        return;
    }

    rams.forEach((ram, index) => {
        const row = document.createElement('div');
        row.className = 'ram-row form-row';
        row.dataset.row = index;
        row.innerHTML = `
            <div class="form-group">
                <label>رم</label>
                <select name="edit_ram_id_${index}" class="ram-select">
                    <option value="">-- انتخاب --</option>
                    ${getRamOptions(ram.ram_id)}
                </select>
            </div>
            <div class="form-group">
                
                <button type="button" class="btn-remove-ram" onclick="removeEditRamRow(this)">🗑️</button>
            </div>
        `;
        container.appendChild(row);
    });
}

function getRamOptions(selectedId) {
    // این تابع باید در PHP ساخته شود و در JS به صورت داده ارسال شود
    // برای سادگی، از داده‌های موجود در صفحه استفاده می‌کنیم
    const select = document.querySelector('select[name="ram_id_0"]');
    if (!select) return '';
    return select.innerHTML;
}

// ============================================
// بارگذاری هاردها در مودال ویرایش
// ============================================

function loadEditStorages(storages) {
    const container = document.getElementById('edit_storages_container');
    container.innerHTML = '';

    if (!storages || storages.length === 0) {
        addEditStorageRow();
        return;
    }

    storages.forEach((storage, index) => {
        const row = document.createElement('div');
        row.className = 'storage-row form-row';
        row.dataset.row = index;
        row.innerHTML = `
            <div class="form-group">
                <label>هارد</label>
                <select name="edit_storage_id_${index}" class="storage-select">
                    <option value="">-- انتخاب --</option>
                    ${getStorageOptions(storage.storage_id)}
                </select>
            </div>
            <div class="form-group">
                
                <button type="button" class="btn-remove-storage" onclick="removeEditStorageRow(this)">🗑️</button>
            </div>
        `;
        container.appendChild(row);
    });
}

// ============================================
// بارگذاری IPها در مودال ویرایش
// ============================================

function loadEditIps(ips) {
    const container = document.getElementById('edit_ips_container');
    container.innerHTML = '';

    if (!ips || ips.length === 0) {
        addEditIpRow();
        return;
    }

    ips.forEach((ip, index) => {
        const row = document.createElement('div');
        row.className = 'ip-row form-row';
        row.dataset.row = index;
        row.innerHTML = `
            <div class="form-group">
                <label>آدرس IP</label>
                <input type="text" name="edit_ip_address_${index}" value="${ip.ip_address}" placeholder="مثلاً: 192.168.1.100">
            </div>
            <div class="form-group">
                <label>شبکه</label>
                <select name="edit_ip_network_${index}">
                    <option value="LAN" ${ip.network_type === 'LAN' ? 'selected' : ''}>LAN</option>
                    <option value="WAN" ${ip.network_type === 'WAN' ? 'selected' : ''}>WAN</option>
                    <option value="VPN" ${ip.network_type === 'VPN' ? 'selected' : ''}>VPN</option>
                    <option value="WiFi" ${ip.network_type === 'WiFi' ? 'selected' : ''}>WiFi</option>
                    <option value="Other" ${ip.network_type === 'Other' ? 'selected' : ''}>سایر</option>
                </select>
            </div>
            <div class="form-group">
               
                <button type="button" class="btn-remove-ip" onclick="removeEditIpRow(this)">🗑️</button>
            </div>
        `;
        container.appendChild(row);
    });
}

// ============================================
// بارگذاری تجهیزات جانبی در مودال ویرایش
// ============================================

function loadEditPeripherals(peripherals) {
    const container = document.getElementById('edit_peripherals_container');
    container.innerHTML = '';

    if (!peripherals || peripherals.length === 0) {
        addEditPeripheralRow();
        return;
    }

    peripherals.forEach((periph, index) => {
        const row = document.createElement('div');
        row.className = 'peripheral-row form-row';
        row.dataset.row = index;
        row.innerHTML = `
            <div class="form-group">
                <label>تجهیز</label>
                <select name="edit_peripheral_id_${index}" class="peripheral-select">
                    <option value="">-- انتخاب --</option>
                    ${getPeripheralOptions(periph.peripheral_id)}
                </select>
            </div>
            <div class="form-group" >
               
                <button type="button" class="btn-remove-peripheral" onclick="removeEditPeripheralRow(this)">🗑️</button>
            </div>
        `;
        container.appendChild(row);
    });
}

// ============================================
// توابع افزودن ردیف در مودال ویرایش
// ============================================

function addEditRamRow() {
    const container = document.getElementById('edit_rams_container');
    const rowCount = container.querySelectorAll('.ram-row').length;
    addEditRow(container, 'ram', rowCount, 'ram-row', 'edit_ram_id_', 'edit_ram_primary_');
}

function addEditStorageRow() {
    const container = document.getElementById('edit_storages_container');
    const rowCount = container.querySelectorAll('.storage-row').length;
    addEditRow(container, 'storage', rowCount, 'storage-row', 'edit_storage_id_', 'edit_storage_primary_');
}

function addEditIpRow() {
    const container = document.getElementById('edit_ips_container');
    const rowCount = container.querySelectorAll('.ip-row').length;
    addEditRow(container, 'ip', rowCount, 'ip-row', 'edit_ip_address_', 'edit_ip_primary_');
}

function addEditPeripheralRow() {
    const container = document.getElementById('edit_peripherals_container');
    const rowCount = container.querySelectorAll('.peripheral-row').length;
    addEditRow(container, 'peripheral', rowCount, 'peripheral-row', 'edit_peripheral_id_', 'edit_peripheral_default_');
}

function addEditRow(container, type, rowCount, className, namePrefix, checkPrefix) {
    const firstRow = container.querySelector(`.${className}`);
    if (!firstRow) {
        // اگر ردیفی وجود ندارد، یک ردیف جدید بساز
        const newRow = document.createElement('div');
        newRow.className = className + ' form-row';
        newRow.dataset.row = rowCount;
        newRow.innerHTML = getDefaultRowHtml(type, rowCount);
        container.appendChild(newRow);
        return;
    }

    const newRow = document.createElement('div');
    newRow.className = className + ' form-row';
    newRow.dataset.row = rowCount;

    let html = firstRow.innerHTML;
    html = html.replace(new RegExp(namePrefix + '\\d+', 'g'), namePrefix + rowCount);
    html = html.replace(new RegExp(checkPrefix + '\\d+', 'g'), checkPrefix + rowCount);

    newRow.innerHTML = html;
    container.appendChild(newRow);

    const removeBtn = newRow.querySelector('.btn-remove-' + type);
    if (removeBtn) {
        removeBtn.style.display = 'inline-block';
    }
}

function getDefaultRowHtml(type, index) {
    switch(type) {
        case 'ram':
            return `
                <div class="form-group">
                    <label>رم</label>
                    <select name="edit_ram_id_${index}" class="ram-select">
                        <option value="">-- انتخاب --</option>
                        ${getRamOptions()}
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" class="btn-remove-ram" onclick="removeEditRamRow(this)">🗑️</button>
                </div>
            `;
        case 'storage':
            return `
                <div class="form-group">
                    <label>هارد</label>
                    <select name="edit_storage_id_${index}" class="storage-select">
                        <option value="">-- انتخاب --</option>
                        ${getStorageOptions()}
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" class="btn-remove-storage" onclick="removeEditStorageRow(this)">🗑️</button>
                </div>
            `;
        case 'ip':
            return `
                <div class="form-group">
                    <label>آدرس IP</label>
                    <input type="text" name="edit_ip_address_${index}" placeholder="مثلاً: 192.168.1.100">
                </div>
                <div class="form-group">
                    <label>شبکه</label>
                    <select name="edit_ip_network_${index}">
                        <option value="LAN">LAN</option>
                        <option value="WAN">WAN</option>
                        <option value="VPN">VPN</option>
                        <option value="WiFi">WiFi</option>
                        <option value="Other">سایر</option>
                    </select>
                </div>
                <div class="form-group" >
                  
                    <button type="button" class="btn-remove-ip" onclick="removeEditIpRow(this)" >🗑️</button>
                </div>
            `;
        case 'peripheral':
            return `
                <div class="form-group">
                    <label>تجهیز</label>
                    <select name="edit_peripheral_id_${index}" class="peripheral-select">
                        <option value="">-- انتخاب --</option>
                        ${getPeripheralOptions()}
                    </select>
                </div>
                <div class="form-group" >
                    <button type="button" class="btn-remove-peripheral" onclick="removeEditPeripheralRow(this)" >🗑️</button>
                </div>
            `;
        default:
            return '';
    }
}

// ============================================
// توابع حذف ردیف در مودال ویرایش
// ============================================

function removeEditRamRow(btn) {
    const row = btn.closest('.ram-row');
    const container = document.getElementById('edit_rams_container');
    if (container.querySelectorAll('.ram-row').length > 1) {
        row.remove();
    } else {
        Swal.fire({
            title: 'خطا!',
            text: 'حداقل یک رم باید وجود داشته باشد.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
    }
}

function removeEditStorageRow(btn) {
    const row = btn.closest('.storage-row');
    const container = document.getElementById('edit_storages_container');
    if (container.querySelectorAll('.storage-row').length > 1) {
        row.remove();
    } else {
        Swal.fire({
            title: 'خطا!',
            text: 'حداقل یک هارد باید وجود داشته باشد.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
    }
}

function removeEditIpRow(btn) {
    const row = btn.closest('.ip-row');
    const container = document.getElementById('edit_ips_container');
    if (container.querySelectorAll('.ip-row').length > 1) {
        row.remove();
    } else {
        Swal.fire({
            title: 'خطا!',
            text: 'حداقل یک IP باید وجود داشته باشد.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
    }
}

function removeEditPeripheralRow(btn) {
    const row = btn.closest('.peripheral-row');
    const container = document.getElementById('edit_peripherals_container');
    if (container.querySelectorAll('.peripheral-row').length > 1) {
        row.remove();
    } else {
        Swal.fire({
            title: 'خطا!',
            text: 'حداقل یک تجهیز جانبی باید وجود داشته باشد.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
    }
}

// ============================================
// توابع کمکی (با داده‌های واقعی)
// ============================================

function getRamOptions(selectedId) {
    // این تابع باید در PHP ساخته شود
    // برای سادگی، از داده‌های موجود در صفحه استفاده می‌کنیم
    const select = document.querySelector('select[name="ram_id_0"]');
    if (!select) return '';
    return select.innerHTML;
}

function getStorageOptions(selectedId) {
    const select = document.querySelector('select[name="storage_id_0"]');
    if (!select) return '';
    return select.innerHTML;
}

function getPeripheralOptions(selectedId) {
    const select = document.querySelector('select[name="peripheral_id_0"]');
    if (!select) return '';
    return select.innerHTML;
}
// ============================================
// مدیریت افزودن سریع قطعات
// ============================================

function openComponentModal(type) {
    const modal = document.getElementById('componentModal');
    const form = document.getElementById('componentForm');
    const title = document.getElementById('componentModalTitle');
    // ریست فرم
    form.reset();

    // تنظیم عنوان و نوع
    const titles = {
        'cpu': '➕ افزودن پردازنده جدید',
        'motherboard': '➕ افزودن مادربرد جدید',
        'ram': '➕ افزودن رم جدید',
        'storage': '➕ افزودن هارد جدید',
        'power': '➕ افزودن پاور جدید',
        'monitor': '➕ افزودن مانیتور جدید'
    };

    title.textContent = titles[type] || '➕ افزودن قطعه جدید';
    document.getElementById('component_type').value = type;

    document.getElementById("componentListTitle").innerText =
        "لیست " + componentTitles[type] + " های ثبت شده";

    loadComponentList(type);

    // نمایش فیلدهای اختصاصی
    document.getElementById('ram_fields').style.display = type === 'ram' ? 'block' : 'none';
    document.getElementById('storage_fields').style.display = type === 'storage' ? 'block' : 'none';
    document.getElementById('monitor_fields').style.display = type === 'monitor' ? 'block' : 'none';

    modal.style.display = 'flex';
}

const componentTitles = {
    cpu: "CPU",
    motherboard: "مادربرد",
    ram: "رم",
    storage: "هارد",
    power: "پاور",
    gpu: "کارت گرافیک",
    monitor: "مانیتور"
};
function loadComponentList(type) {

    fetch("assets/ajax/get_components.php?type=" + type)

        .then(response => response.json())

        .then(rows => {
            const head = document.getElementById("componentTableHead");

            if (type === "peripheral") {

                head.innerHTML = `
        <tr>
            <th>نوع تجهیز</th>
            <th>برند</th>
            <th>مدل</th>
            <th>کد اموال</th>
            <th>عملیات</th>
        </tr>
    `;

            }
            else if (type === "ram" || type === "storage") {

                head.innerHTML = `
        <tr>
            <th>برند</th>
            <th>مدل</th>
            <th>ظرفیت</th>
            <th>نوع</th>
            <th>عملیات</th>
        </tr>
    `;

            } else {

                head.innerHTML = `
        <tr>
            <th>برند</th>
            <th>مدل</th>
            <th>عملیات</th>
        </tr>
    `;

            }
            let html = "";
            if (rows.length === 0) {

                const colspan = type === "peripheral" ? 5 :
                    (type === "ram" || type === "storage") ? 5 : 3;


                html = `
        <tr>
            <td colspan="${colspan}" style="text-align:center;color:#888">
                موردی ثبت نشده است
            </td>
        </tr>
    `;
            }else {
                rows.forEach(row => {

                    if (type === "peripheral") {

                        html += `
        <tr>
            <td>${row.type_name}</td>
            <td>${row.brand_name}</td>
            <td>${row.model_name}</td>
            <td>${row.property_code || "-"}</td>
            <td>
                <button
                    class="btn-remove"
                    onclick="deleteComponent('${type}', ${row.id})">
                    🗑️
                </button>
            </td>
        </tr>
    `;

                    }

                    else if (type === "ram" || type === "storage") {

                        html += `
            <tr>
                <td>${row.brand_name}</td>
                <td>${row.model_name}</td>
                <td>${row.capacity || "-"}</td>
                <td>${row.type || "-"}</td>
                <td>
                    <button class="btn-remove"
                        onclick="deleteComponent('${type}', ${row.id})">
                        🗑️
                    </button>
                </td>
            </tr>
        `;

                    } else {

                        html += `
            <tr>
                <td>${row.brand_name}</td>
                <td>${row.model_name}</td>
                <td>
                    <button class="btn-remove"
                        onclick="deleteComponent('${type}', ${row.id})">
                        🗑️
                    </button>
                </td>
            </tr>
        `;

                    }

                });
            }
            document.getElementById("componentTableBody").innerHTML = html;

        })

        .catch(err => console.error(err));
}



function deleteComponent(type,id){

    Swal.fire({

        title:"حذف شود؟",
        text:"این قطعه حذف خواهد شد.",
        icon:"warning",

        showCancelButton:true,

        confirmButtonText:"بله",
        cancelButtonText:"لغو",

        confirmButtonColor:"#dc3545"

    }).then(result=>{

        if(!result.isConfirmed) return;

        fetch("assets/ajax/delete_component.php",{

            method:"POST",

            headers:{
                "Content-Type":"application/x-www-form-urlencoded"
            },

            body:
                "type="+encodeURIComponent(type)+
                "&id="+id

        })

            .then(r=>r.json())

            .then(data=>{

                if(data.success){
                    loadComponentList(type);
                    refreshComponentSelect(type);

                    Swal.fire({

                        icon:"success",
                        title:"حذف شد",
                        timer:1200,
                        showConfirmButton:false

                    });


                }else{

                    Swal.fire("خطا","حذف انجام نشد","error");

                }

            });

    });

}
function refreshComponentSelect(type) {

    fetch("assets/ajax/get_components.php?type=" + type)

        .then(r => r.json())

        .then(rows => {

            let select;

            switch (type) {

                case "ram":
                    select = document.querySelector(".ram-select");
                    break;

                case "storage":
                    select = document.querySelector(".storage-select");
                    break;

                case "peripheral":
                    select = document.querySelector(".peripheral-select");
                    break;

                default:
                    select = document.getElementById(type + "_id");
                    break;
            }

            if (!select) return;

            let html = '<option value="">-- انتخاب --</option>';

            rows.forEach(row => {

                let text = row.brand_name + " " + row.model_name;

                if (row.type) {
                    text += ` (${row.capacity} ${row.type})`;
                }

                html += `<option value="${row.id}">${text}</option>`;

            });

            select.innerHTML = html;

        });

}
function saveComponent() {
    const form = document.getElementById('componentForm');
    const formData = new FormData(form);
    formData.append('add_component', '1');

    fetch('admin_systems.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const type = document.getElementById('component_type').value;
                console.log(type);
                // پیدا کردن سلکت مربوطه
                let selectId = type + '_id';
                if (type === 'motherboard') selectId = 'motherboard_id';
                if (type === 'storage') selectId = 'storage_id';
                if (type === 'peripheral') selectId = 'peripheral_id';

                const select = document.getElementById(selectId);
                if (select) {
                    const option = document.createElement('option');
                    option.value = data.id;
                    option.textContent = data.display_name;
                    select.appendChild(option);
                    select.value = data.id;
                }

                closeModal('componentModal');

                Swal.fire({
                    title: 'موفق!',
                    text: 'قطعه با موفقیت ثبت شد.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                refreshComponentSelect(type);
                loadComponentList(type);
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'خطا!',
                text: 'مشکلی در ثبت قطعه رخ داد.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        });
}
// ============================================
// مودال افزودن تجهیز جانبی
// ============================================

function openPeripheralModal() {
    const modal = document.getElementById('peripheralModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function savePeripheral() {
    const form = document.getElementById('peripheralForm');
    const formData = new FormData(form);

    fetch('admin_systems.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // پیدا کردن آخرین سلکت تجهیزات جانبی
                const selects = document.querySelectorAll('.peripheral-select');
                const select = selects[selects.length - 1];
                if (select) {
                    const option = document.createElement('option');
                    option.value = data.id;
                    option.textContent = data.display_name;
                    select.appendChild(option);
                    select.value = data.id;
                }

                closeModal('peripheralModal');

                Swal.fire({
                    title: 'موفق!',
                    text: 'تجهیز جانبی با موفقیت ثبت شد.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'خطا!',
                text: 'مشکلی در ثبت تجهیز جانبی رخ داد.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        });
}
// ============================================
// ویرایش سیستم
// ============================================

function openEditModal(system) {
    document.getElementById('edit_system_id').value = system.id;
    document.getElementById('edit_computer_code').value = system.computer_code || '';
    document.getElementById('edit_property_code').value = system.property_code || '';
    document.getElementById('edit_name').value = system.name || '';
    document.getElementById('edit_department_id').value = system.department_id || '';
    document.getElementById('edit_cpu_id').value = system.cpu_id || '';
    document.getElementById('edit_motherboard_id').value = system.motherboard_id || '';
    document.getElementById('edit_power_id').value = system.power_id || '';
    document.getElementById('edit_monitor_id').value = system.monitor_id || '';

    // بارگذاری همه داده‌ها
    loadEditData(system.id);

    document.getElementById('editModal').style.display = 'flex';
}

// ============================================
// حذف فاکتور
// ============================================

function confirmDelete(id, name) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'سیستم "' + name + '" حذف خواهد شد!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('admin_systems.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'delete_system=1&system_id=' + id
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`tr:has(button[onclick*="confirmDelete(${id}, "])`);
                        if (row) {
                            row.remove();
                            updateTableRowNumbers();
                        }
                        Swal.fire({
                            title: 'حذف شد!',
                            text: 'سیستم با موفقیت حذف شد.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'خطا!',
                        text: 'مشکلی در حذف سیستم رخ داد.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                });
        }
    });
}

function updateTableRowNumbers() {
    const rows = document.querySelectorAll('.systems-table tbody tr:not(:first-child)');
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
    document.getElementById(modalId).style.display = 'none';
}

// ============================================
// راه‌اندازی اولیه
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    initSearch();

    // بستن مودال با کلیک روی پس‌زمینه
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };
});