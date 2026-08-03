<?php
date_default_timezone_set('Asia/Tehran');

require_once __DIR__ . '/tcpdf/tcpdf.php';

$fontname = TCPDF_FONTS::addTTFfont(
    __DIR__ . '/styles/Fonts/Vazir.ttf',
    'TrueTypeUnicode',
    '',
    32
);
class MyPDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('dejavusans', '', 12);
        $this->Cell(0,10,'گزارش سیستم ها',0,1,'C');
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('dejavusans','',8);
        $this->Cell(0,10,'صفحه '.$this->getAliasNumPage().' از '.$this->getAliasNbPages(),0,0,'C');
    }
}


$pdf = new MyPDF('L','mm','A4',true,'UTF-8',false);

$pdf->SetMargins(3,8,3);
$pdf->SetAutoPageBreak(true,3);

$pdf->AddPage();
$pdf->setRTL(true);
$pdf->SetFont($fontname, '', 10);

$css='
<style>

table{
width:100%;
border-collapse:collapse;
font-size:10px;
direction:rtl;
}

th{
background-color:#667eea;
color:#ffffff;
padding:5px;
border:1px solid #000;
text-align:center;
align:center;
}

td{
padding:4px;
border:1px solid #999;
text-align:center;
}

</style>
';


$html=$css.$_POST['html'];

$pdf->writeHTML($html,true,false,true,false,'');


$filename = 'system_report_' . date('Y-m-d_H-i-s') . '.pdf';
$pdf->Output($filename, 'D');