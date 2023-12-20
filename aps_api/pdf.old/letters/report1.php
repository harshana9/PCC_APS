<?php

// Include the main TCPDF library (search for installation path).
require_once('../tcpdf/tcpdf.php');

//Company data load
$ini = parse_ini_file('../../conf/conf.ini');


// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


//echo K_PATH_IMAGES;

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Application Proceesing System @'.$ini["COMPANY"]);
$pdf->SetTitle('Letter');
$pdf->SetSubject($ini['ADDRESSL1'].$ini['ADDRESSL2'], 'Hotline: '.$ini["TELEPHONE"]);
$pdf->SetKeywords('www.peoplesbank.lk');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $ini["COMPANY"], $ini['ADDRESSL1'].chr(10).$ini['ADDRESSL2'].chr(10).'Hotline: '.$ini["TELEPHONE"].chr(10).'Email: '.$ini["EMAIL"], array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('times', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

// Set some content to print
$html = <<<EOD

<p>Dear valued custome,</br>your application for POS mechine at peopel's bank card center has been rejected due follwing reason/s.</p><ol><li>Incompleate application.</li><li>NIC not certifified</li></ol><br/><br/><p>Please contact for further claifications.</p><font color='red'>Signature not required</font>

EOD;

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('report1.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+