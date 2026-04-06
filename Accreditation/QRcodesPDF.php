<?php
require_once(dirname(__FILE__).'/config.php');
require_once('Common/Lib/Fun_Modules.php');
require_once('Common/Lib/CommonLib.php');
require_once(__DIR__.'/Lib.php');
require_once('../Api/ISK-NG/config_defines.php');

CheckTourSession(true);
checkFullACL(AclAccreditation, 'acAdvanced', AclReadWrite);

if(!($QrCode=getParameter('GateNGConnection', '', [
    'type'=>$_SESSION['UseApi']==ISK_NG_LIVE_CODE ? 'socket' : 'http',
    'url'=>'http://'.gethostbyname($_SERVER['HTTP_HOST']).$CFG->ROOT_DIR,
    'socket'=>getModuleParameter('ISK-NG', 'SocketIP', gethostbyname($_SERVER['HTTP_HOST'])),
    'port'=>getModuleParameter('ISK-NG', 'SocketPort', '12346'),
], true))) {
	// no QR Code go and set things!
	CD_redirect('./QRcodes.php');
}

// Include the main TCPDF library (search for installation path).
require_once('Common/pdf/ResultPDF.inc.php');

// create new PDF document
$pdf = new ResultPDF('QrCode');//TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set style for barcode
$style = array(
		'border' => 2,
		'vpadding' => 'auto',
		'hpadding' => 'auto',
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255)
		'module_width' => 1, // width of a single module in points
		'module_height' => 1 // height of a single module in points
);

$Code=[
    'action'=>'setup',
];

if($QrCode['type']=='http') {
    $Code["serverUrl"]=$QrCode['url'];
} else {
    $Code["socketIP"]=$QrCode['socket'];
    $Code["socketPort"]=$QrCode['port'];
}

$Y=35;
$VBlock=($pdf->getPageHeight()-$Y-30);
$Size=min(110, $VBlock-12);
$Size = $Size + 4/2;
$Size = $Size - ($Size % 4);
$X=($pdf->getPageWidth()-$Size)/2;

$ActY=$Y ;
$pdf->SetFontSize(12);

$pdf->SetY($ActY-6);
$pdf->SetFont('', 'B', 20);
$pdf->Cell(0, 0, get_text('ISK-SETUP-'.$_SESSION['UseApi'],'Api'), 0, 1, 'C');
$pdf->SetFont('', '', 10);
$pdf->write2DBarcode(json_encode($Code), 'QRCODE,L', $X, $ActY+12, $Size, $Size, $style, 'N');
$ActY+= $VBlock;
$pdf->Ln(10);

$pdf->Cell(0, 0, get_text('ConnectionType','Api') . ": " . $QrCode['type'], 0, 1, 'L');
if($QrCode['type']=='http') {
    $pdf->Cell(0, 0, get_text('ISK-ServerUrl','Api') . ": " . $QrCode['url'], 0, 1, 'L');
} else {
    $pdf->Cell(0, 0, get_text('ISK-SocketIP','Api') . ": " . $QrCode['socket'], 0, 1, 'L');
    $pdf->Cell(0, 0, get_text('ISK-SocketPort','Api') . ": " . $QrCode['port'], 0, 1, 'L');
}

// -------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('QrCode.pdf', 'I');
