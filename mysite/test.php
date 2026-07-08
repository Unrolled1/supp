
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ی</title>
    <link rel="stylesheet" href="assets/styles/persian-datepicker.min.css">

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/persian-date.min.js"></script>
    <script src="assets/js/persian-datepicker.min.js"></script>
</head>
<body>
<div class="filter-group">
    <label>از تاریخ</label>
    <input
            type="text"
            id="date_from"
            name="date_from"
            class="form-control">
</div>

<div class="filter-group">
    <label>تا تاریخ</label>
    <input
            type="text"
            id="date_to"
            name="date_to"
            class="form-control">
</div>

<script>
    $(function () {

        $("#date_from").persianDatepicker({
            format: "YYYY/MM/DD",
            autoClose: true,
            initialValue: false
        });

        $("#date_to").persianDatepicker({
            format: "YYYY/MM/DD",
            autoClose: true,
            initialValue: false
        });

    });
</script>
</body>