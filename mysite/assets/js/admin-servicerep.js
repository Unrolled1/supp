
function searchReport() {

    const form = document.getElementById("filterform");
    const formData = new FormData(form);

    formData.append("ajax", "1");

    fetch("admin_servicerep.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {

            if (data.success) {
                document.querySelector(".reports-table").innerHTML = data.table;
            }

        })
        .catch(console.error);
}

window.reportConfig = {
    url: "admin_servicerep.php",
    printUrl: "assets/print_servicerep.php",
    table: ".reports-table",
    filterInfo: true
};