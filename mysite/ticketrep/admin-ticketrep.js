// ============================================
// توابع Ajax برای فیلتر کردن بدون رفرش
// ============================================

// ============================================
// Event Listeners بعد از لود صفحه
// ============================================

window.reportConfig = {

    url: "admin_ticketrep.php",

    printUrl: "assets/print_report.php",

    table: ".reports-table",

    stats: true,

    filterInfo: true

};
window.addEventListener("DOMContentLoaded", function () {

    const btn = document.querySelector(".btn-pdf");

    if (!btn) return;

    btn.addEventListener("click", function () {

        const form = document.getElementById("filterform");

        form.action = window.reportConfig.printUrl + "?type=ticket";
        form.method = "POST";
        form.target = "_blank";

        form.submit();

        form.removeAttribute("action");
        form.removeAttribute("target");
    });

});