```php
<?php

date_default_timezone_set('Asia/Tehran');

require_once __DIR__ . '/tcpdf/tcpdf.php';

class MyPDF extends TCPDF
{
    public $reportTitle = '';
    public $filterInfo = '';
    public $generatedDate = '';

    public function Header()
    {
        $this->SetFont('dejavusans', '', 12);
        $this->Cell(0, 10, $this->reportTitle, 0, 1, 'C');

        if (!empty($this->filterInfo)) {
            $this->SetFont('dejavusans', '', 8);
            $this->Cell(0, 5, $this->filterInfo, 0, 1, 'C');
        }

        $this->Line(10, 25, 200, 25);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('dejavusans', '', 8);

        $footerText =
            'صفحه ' . $this->getAliasNumPage() .
            ' از ' . $this->getAliasNbPages() .
            ' | تاریخ تولید: ' . $this->generatedDate;

        $this->Cell(0, 10, $footerText, 0, 0, 'C');
    }
}

$pdf = new MyPDF(
    'L',
    'mm',
    'A4',
    true,
    'UTF-8',
    false
);

$pdf->reportTitle = $_POST['title'] ?? 'گزارش';
$pdf->filterInfo = $_POST['filter'] ?? '';
$pdf->generatedDate = date('Y/m/d');

$pdf->SetCreator('Support System');
$pdf->SetAuthor('Support System');
$pdf->SetTitle($pdf->reportTitle);

$pdf->SetMargins(3, 30, 3);
$pdf->SetHeaderMargin(8);
$pdf->SetFooterMargin(3);

$pdf->SetAutoPageBreak(true, 18);

$pdf->AddPage();

$pdf->setRTL(true);

$pdf->SetFont('dejavusans', '', 9);

$css = <<<CSS
<style>

body {
    direction: rtl;
    text-align: center;
    font-family: dejavusans;
    font-size: 9px;
}

table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
}

thead tr {
    background-color: #2c3e50;
    color: #ffffff;
}

th {
    background-color: #2c3e50;
    color: #ffffff;
    border: 1px solid #666666;
    text-align: center;
    font-weight: bold;
    padding: 6px;
    font-size: 9px;
}

td {
    border: 1px solid #bcbcbc;
    text-align: center;
    padding: 5px;
    font-size: 8px;
    vertical-align: middle;
}

tr:nth-child(even) {
    background-color: #f5f5f5;
}

</style>
CSS;

$title = $_POST['title'] ?? 'گزارش';
$filter = $_POST['filter'] ?? '';
$table = $_POST['html'] ?? '';

$html = $css;

$html .= "
<h2 style=\"text-align:center;\">"
    . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
    . "</h2>
";

if (!empty($filter)) {
    $html .= "
    <div style=\"border:1px solid #cccccc; padding:8px; margin-bottom:10px;\">
        {$filter}
    </div>
    ";
}

$html .= $table;

$pdf->writeHTML(
    $html,
    true,
    false,
    true,
    false,
    ''
);

$filename = 'system_report_' . date('Y-m-d_H-i-s') . '.pdf';

$pdf->Output($filename, 'D');
```
