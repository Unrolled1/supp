<?php

date_default_timezone_set('Asia/Tehran');

require_once __DIR__ . '/tcpdf/tcpdf.php';

function removeEmoji($text)
{
    return preg_replace(
        '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u',
        '',
        $text
    );
}

class MyPDF extends TCPDF
{
    public $reportTitle = '';
    public $filterInfo = '';
    public $generatedDate = '';

    public function Header()
    {
        // بدون هدر
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Vazir', '', 9);

        $this->Cell(
            0,
            10,
            'صفحه ' . $this->getAliasNumPage() .
            ' از ' . $this->getAliasNbPages() .
            ' | تاریخ تولید: ' . $this->generatedDate,
            0,
            0,
            'C'
        );
    }
}
function gregorianToJalali($gy, $gm, $gd)
{
    $g_d_m = [
        0,31,59,90,120,151,181,212,243,273,304,334
    ];

    if ($gy > 1600) {
        $jy = 979;
        $gy -= 1600;
    } else {
        $jy = 0;
        $gy -= 621;
    }

    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

    $days = (365 * $gy)
        + floor(($gy2 + 3) / 4)
        - floor(($gy2 + 99) / 100)
        + floor(($gy2 + 399) / 400)
        - 80
        + $gd
        + $g_d_m[$gm - 1];

    $jy += 33 * floor($days / 12053);
    $days %= 12053;

    $jy += 4 * floor($days / 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += floor(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + floor($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + floor(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return [$jy, $jm, $jd];
}
function htmlTableToArray($html)
{
    $result = [];

    if (empty($html)) {
        return $result;
    }

    preg_match('/<table.*?>(.*?)<\/table>/is', $html, $tableMatch);

    if (empty($tableMatch)) {
        return $result;
    }

    $tableContent = $tableMatch[1];

    preg_match_all('/<tr.*?>(.*?)<\/tr>/is', $tableContent, $rowMatches);

    foreach ($rowMatches[1] as $row) {

        $rowData = [];

        preg_match_all('/<t[dh].*?>(.*?)<\/t[dh]>/is', $row, $cellMatches);

        foreach ($cellMatches[1] as $cell) {

            $cell = strip_tags($cell);
            $cell = html_entity_decode(
                $cell,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

            $cell = trim(
                preg_replace('/\s+/u', ' ', $cell)
            );

            $cell = removeEmoji($cell);

            $rowData[] = $cell;
        }

        if (!empty($rowData)) {
            $result[] = $rowData;
        }
    }

    return $result;
}

$title = isset($_POST['title'])
    ? removeEmoji(trim(strip_tags($_POST['title'])))
    : 'گزارش';

$filter = isset($_POST['filter'])
    ? removeEmoji(trim(strip_tags($_POST['filter'])))
    : '';

$tableHtml = isset($_POST['html'])
    ? $_POST['html']
    : '';

$css = isset($_POST['css'])
    ? $_POST['css']
    : '';

$tableData = htmlTableToArray($tableHtml);

$fontPath = __DIR__ . '/../styles/Fonts/Vazir.ttf';

if (!file_exists($fontPath)) {
    die('Vazir.ttf پیدا نشد');
}

$fontname = TCPDF_FONTS::addTTFfont(
    $fontPath,
    'TrueTypeUnicode',
    '',
    32
);

if ($fontname === false) {
    die('خطا در اضافه کردن فونت Vazir');
}

$pdf = new MyPDF(
    'L',
    'mm',
    'A4',
    true,
    'UTF-8',
    false
);

$pdf->SetCreator('Support System');
$pdf->SetAuthor('Support System');
$pdf->SetTitle($title);

$pdf->reportTitle = $title;
$pdf->filterInfo = $filter;
[$jy, $jm, $jd] = gregorianToJalali(
    (int)date('Y'),
    (int)date('n'),
    (int)date('j')
);

$pdf->generatedDate = sprintf(
    '%04d/%02d/%02d',
    $jy,
    $jm,
    $jd
);
$pdf->SetMargins(10, 12, 10);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(8);
$pdf->SetAutoPageBreak(true, 18);

$pdf->AddPage();
$pdf->setRTL(true);

$pdf->SetFont($fontname, 'B', 16);
$pdf->Cell(0, 10, $title, 0, 1, 'C');

if (!empty($filter)) {

    $pdf->Ln(3);

    $pdf->SetFont($fontname, '', 11);

    $pdf->MultiCell(
        0,
        7,
        $filter,
        1,
        'C',
        false,
        1
    );

    $pdf->Ln(4);
}

if (!empty($tableData)) {

    $colCount = 0;

    foreach ($tableData as $row) {
        $colCount = max($colCount, count($row));
    }

    if ($colCount > 0) {

        $pageWidth = $pdf->getPageWidth();
        $leftMargin = $pdf->getMargins()['left'];
        $rightMargin = $pdf->getMargins()['right'];

        $usableWidth = $pageWidth - $leftMargin - $rightMargin;
        $colWidth = $usableWidth / $colCount;

        foreach ($tableData as $rowIndex => $row) {

            while (count($row) < $colCount) {
                $row[] = '';
            }

            $isHeader = ($rowIndex === 0);

            if ($isHeader) {

                $pdf->SetFillColor(44, 62, 80);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont($fontname, 'B', 10);

            } else {

                $pdf->SetTextColor(0, 0, 0);

                $pdf->SetFillColor(
                    ($rowIndex % 2 === 0) ? 245 : 255,
                    245,
                    245
                );

                $pdf->SetFont($fontname, '', 9);
            }

            foreach ($row as $cell) {

                $cell = removeEmoji((string)$cell);

                $pdf->Cell(
                    $colWidth,
                    8,
                    $cell,
                    1,
                    0,
                    'C',
                    true
                );
            }

            $pdf->Ln();
        }
    }
}

$filename = 'system_report_' . date('Y-m-d_H-i-s') . '.pdf';

$pdf->Output($filename, 'D');