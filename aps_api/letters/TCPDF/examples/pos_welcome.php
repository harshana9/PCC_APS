<?php
function generate_pos_welcome_letter($mid, $rate, $monthlyvolume, $fee, $contact, $date, $address, $save_type="D", $branch=null){

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

    // create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->setCreator(PDF_CREATOR);
    $pdf->setAuthor('Peoples Card Centre');
    $pdf->setTitle('Welcome Letter');
    $pdf->setSubject('Welcome Letter');
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

    $address_arr = explode (",", $address); 

    $html="<p>";

    $comma=null;

    if(isset($address_arr[0])){
        $html.=$address_arr[0].",<br/>";
    }
    else{
        $html.="<br/>";
    }

    if(isset($address_arr[1])){
        $html.=$address_arr[1].",<br/>";
    }
    else{
        $html.="<br/>";
    }

    if(count($address_arr)>=3){
        for ($i=2; $i < count($address_arr); $i++) { 
            $html.=$address_arr[$i].",";
        }
    }

    // Set some content to print
    $html .= <<<EOD
    </p>

    <p>$date</p>

    <p>Dear Sir,<br/><p><p><center><u><b>
    Welcome to your New Merchant Membership</b></u><br/></p><p>

    We are happy to inform you that your establishment has been enrolled as a merchant facilitated by People’s Card Centre. Please note that your merchant number <b>$mid</b> and the commission rate applicable for each VISA and MasterCard transaction would be <b>$rate%</b>.</p>

    <p>We advise you to maintain a monthly card sales volume that is not less than $monthlyvolume LKR and keep records receipts of your sales for 12 months. If you fail to maintain $monthlyvolume LKR for a particular month <b>$fee</b>LKR would be deducted as a penalty fee and this penalty fee can be varied according to the banks’ requirement. In addition, failure to receive penalty fees for three consecutive months would unavoidably lead us to cancel your merchant membership.</p>

    <p>Further, you have to settle your POS machine daily on business days to receive the payments and failure to settle the machine within 7 days would lead customers’ right to charge back the payments.</p><p>
    Further, please follow below guidelines to safeguard your POS terminal and when performing transactions using cards.
    </p>


    <ul type='disc'>
        <li>Always perform a proper security due-diligence on cards. <font color="black"> A guide to understand basic security characteristics of cards is given under Annexure-I in this letter. Every payment card has these universal characteristics on the surface.
        </li>
        <li><font color="black">Your POS terminal is PIN enabled. Always ask for the PIN from customer if the terminal is prompting to enter the PIN</li>
        <li><font color="black">Your POS terminal is EMV (Chip) & Contactless enabled. Always look for the microchip embedded <font color="white">___</font> and contactless sign on <font color="white">___</font> the card before        executing the transaction
        </font>
        </li>
        <li><font color="black">Always use insert mode for EMV (Chip) cards & touch mode for Contactless enabled cards.
        </font>
        </li>
        <li><font color="black">If an EMV (Chip) card is giving a “chip reading error” always alert it to the customer and perform transaction using the mag-strip mode.
        </font>
        </li>
        <li><font color="black">If your terminal is constantly giving “chip reader error” , please alert it to the Bank or POS terminal service company as soon as possible
        </font>
        </li>
        <li><font color="black">Always keep the POS terminal in a secure location, away from direct sunlight and moisture
        </font>
        </li>
        <li><font color="black">Your PSTN/GPRS/Android wireless POS terminal is equipped with state of the art security controls and comes with tamper protection. Do not try to do technical maintenances by yourself as it will tamper the terminal and erase of data in the device. Tampered device is chargeable from the merchant.
        </font>
        </li>
        <li><font color="black">It is the merchant’s responsibility to restrict unauthorized access to the POS terminal.
        </font>
        </li>
        <li><font color="black">Do not swipe cards in your cash register machine (terminology: double swipe) right after performing the transaction in the POS terminal. This is a violation of PCI compliance and will be subjected to a fine in a data compromise incident.
        </font>
        </li>
        <li><font color="black">Do not accept cards which you feel suspicious. Use of counterfeited, Lost or stolen cards will result unnecessary losses to your business.
        </font>
        </li>
        <li>Get the necessary identification (Copy of NIC/Passport) of the customer where it is necessary especially for high value transactions.</li>
        <li>Settle your POS machine daily on business days to receive the payments and failure to settle the machine within 7 days would lead customers’ right to charge back the payments.</li>
        <li><font color="black">The POS terminal and its accessories provided are property of People’s Bank. Any lost/theft or damage to these appliances will be chargeable from the merchant.
        </font>
        </li>
        <li><font color="black">In the event of any technical failure, please contact   <b>$contact</b>
        </font>
        </li>

    </ul>
    <p>We hope the new relationship established would be mutually beneficial and welcome your comments and suggestions to improve our service.</p>

    <p></p>
    <p></p>
    <p></p>

    <p>Manager-Merchant Acquiring</p>
    <p>This is a computer generated letter. Signature not required.</p>
    EOD;

    if($branch!=null){
        if(gettype($branch)=="array"){
            $html .= "<p>CC:";
            foreach ($branch as $cc) {
                $html.=$cc." ";
            }
            $html .= "</p>";
        }
        else{
            $html .= "<p>CC:".$branch."</p>";
        }
    }

    // Image example with resizing
    $img_sim=str_replace("\\","/",$dir.'\images\sim.jpg');
    $img_tap=str_replace("\\","/",$dir.'\images\tap.jpg');
    $img_ann=str_replace("\\","/",$dir.'\images\welcome_annexure1.jpg');

    $pdf->Image($img_sim, 57, 226, 6, 5, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);

    // Image example with resizing
    //$dir_sim=$dir.'images/tap.jpg';
    $pdf->Image($img_tap, 105, 226, 6, 5, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);

    // print a block of text using Write()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    // ---------------------------------------------------------

    $pdf->AddPage();

    // Image example with resizing
    $pdf->Image($img_ann, 30, 45, 145, 138, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);

    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', "<p>Annexure –I</p>", 0, 1, 0, true, '', true);

    //Close and output PDF document
    ob_end_clean();
    
    if($save_type=="S"){
        $file_path="cache/".$mid."_POS_Welcome.pdf";
        return $pdf->Output($file_path, "S");
    }
    elseif($save_type=="D"){
        $pdf->Output("POS_Welcome.pdf", "D");
    }
}

?>
