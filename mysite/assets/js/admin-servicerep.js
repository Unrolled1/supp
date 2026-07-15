
function searchReport() {

    const formData = new FormData();

    formData.append("ajax", "1");

    formData.append("department_id",
        document.querySelector("[name='department_id']").value);

    formData.append("date_from",
        document.getElementById("search_date_from").value);

    formData.append("date_to",
        document.getElementById("search_date_to").value);

    fetch("admin-servicerep.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {

            if (data.success) {

                document.querySelector(".reports-table").innerHTML = data.table;

                document.querySelector(".stats .stat-box:nth-child(1) .stat-num").innerHTML = data.stats.total;
                document.querySelector(".stats .review .stat-num").innerHTML = data.stats.review;
                document.querySelector(".stats .answered .stat-num").innerHTML = data.stats.answered;
                document.querySelector(".stats .closed .stat-num").innerHTML = data.stats.closed;

            }

        })
        .catch(console.error);

}
window.reportConfig = {

    url: "admin_servicerep.php",

    printUrl: "assets/print_servicerep.php",

    table: ".reports-table",

    stats: true,

    filterInfo: true

};