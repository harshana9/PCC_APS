<?php
function generate_pos_ipg_reject_letter($mName, $appId, $reasons, $comments, $save_type="D"){

    // Include the main TCPDF library (search for installation path).
    require_once('tcpdf_include.php');


    // Extend the TCPDF class to create custom Header and Footer
    class MYPDF extends TCPDF {

    	//Page header
    	public function Header() {
    		// Logo
    		$image_file = K_PATH_IMAGES.'tcpdf_logo.jpg';
    		$this->Image($image_file, 25, 10, 60, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    		// Set font
    		//$this->setFont('helvetica', 'B', 20);
    		// Title
    		//$this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    	}

    	// Page footer
    	public function Footer() {
    		// Position at 15 mm from bottom
    		$this->setY(-15);
    		// Set font
    		$this->setFont('helvetica', '', 9);

    		$html = '<p align="center"><font color="DarkViolet">2<sup>nd</sup> Floor, No. 1161, Maradana Road, Colombo 08, Sri Lanka.<br/>Hotline: 2490490 &nbsp;&nbsp; Fax: 2169023 &nbsp;&nbsp; E-mail: card@peoplesbank.lk</font><br/></p>';
            $this->writeHTML($html, true, false, true, false, '');

    		// Page number
    		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    	}
    }


    //Directory
    $dir=dirname(__FILE__);

    $date=date('Y-m-d');

    // create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->setCreator(PDF_CREATOR);
    $pdf->setAuthor('Peoples Card Centre');
    $pdf->setTitle('Rejection Letter');
    $pdf->setSubject('Rejection Letter');
    $pdf->setKeywords('');

    // set default header data
    $pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->setMargins(25, 40, PDF_MARGIN_RIGHT);
    $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->setFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);



    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    	require_once(dirname(__FILE__).'/lang/eng.php');
    	$pdf->setLanguageArray($l);
    }

    // ---------------------------------------------------------

    // set font
    $pdf->SetFont('times', '', 12, '', true);

    // add a page
    $pdf->AddPage();

    // Set some content to print
    $html = <<<EOD

    <p><b>To : </b>The manager - $mName</p>
    <p><b>From:Manager - </b>Merchant Acquiring</p>
    <p><b>Subject: </b>Rejected POS/IPG/MPOS Applications</p>
    <p><b>Date: </b>$date</p>
    <p><b>RefNo(s): </b>$appId</p>

    <p>Dear Sir,</p>

    <p>We regret to inform you that the POS/IPG/MPOS applications, attached herewith, has been declined due to lack of the below mentioned requirement, mentioned below</p>
    <ul>
    EOD;

    foreach ($reasons as $reason) {
        $html .= "<li>".$reason."</li>";
    }

    if(!($comments=="" || $comments==null)){
        $comments="Comments:<br/>".$comments."</p>";
    }

    $html .= <<<EOD
    </ul>
    <p>If above mentioned requirements are not fulfilled within 15 days from the receipt of this letter. we are compelled to decline the application without further notice. Please use the reference number mentioned in the letter in any inquiry on thos regard.</p>

    <p>$comments</p>

    <p>K K M Nuwan Priyanga</p>

    <p></p>
    <p></p>
    <p></p>
    <p></p>
    <p></p>
    <p></p>

    <p>Manager-Merchant Acquiring</p>


    EOD;

    // print a block of text using Write()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    //Close and output PDF document
    ob_end_clean();

    if($save_type=="S"){
        $file_path="cache/".rand()."_Reject.pdf";
        return $pdf->Output($file_path, "S");
    }
    elseif($save_type=="D"){
        $pdf->Output("Reject.pdf", "D");
    }
}

?>
