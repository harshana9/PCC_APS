<?php
function generate_pos_welcome_letter(){
    // Include the main TCPDF library (search for installation path).
    require_once('../tcpdf/tcpdf.php');


    //Create Custom Header and Footer
    class MYPDF extends TCPDF {
        //page header
        public function Header() {
            //Logo
            $image_file = K_PATH_IMAGES.'tcpdf_logo.jpg';
            

            $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

            //Font
            $this->SetFont('helvetica', 'B', 20);

            //Title
            $this->Cell(0, 15, '<< TCPDF Example >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }
    }

    //Company data load
    $ini = parse_ini_file('../../conf/conf.ini');

    //Cllect Data From Database
    $mid=null;
    $rate=null;
    $monthlyvolume=null;
    $fee=null;

    // create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Application Proceesing System @'.$ini["COMPANY"]);
    $pdf->SetTitle('Letter');
    $pdf->SetSubject($ini['ADDRESSL1'].$ini['ADDRESSL2'], 'Hotline: '.$ini["TELEPHONE"]);
    $pdf->SetKeywords('www.peoplesbank.lk');

    // set default header data
    //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $ini["COMPANY"], $ini['ADDRESSL1'].chr(10).$ini['ADDRESSL2'].chr(10).'Hotline: '.$ini["TELEPHONE"].chr(10).'Email: '.$ini["EMAIL"], array(0,64,255), array(0,64,128));
    //$pdf->SetHeaderData(PDF_HEADER_LOGO, 68, '', '', null, null);
    $pdf->setPrintHeader(true);
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
    $pdf->SetFont('times', '', 12, '', true);

    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();

    // set text shadow effect
    $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

    // Set some content to print
    $html = <<<EOD
    <p>The Manager
    <br/>Asiri Medial 
    <br/>No.16,
    <br/>PB Balasooriya Shopping Complex
    <br/>Gohagoda Road,
    <br/>Katugastota.
    </p>
    EOD;

    $html .= "<p>".date('Y-m-d')."</p>";

    $html .= "<p>Dear Sir,<br/><p><p><center><u>
    Welcome to your New Merchant Membership</u><br/></p><p>";

    $html .="We are happy to inform you that your establishment has been enrolled as a merchant facilitated by People’s Card Centre. Please note that your merchant number ".$mid." and the commission rate applicable for each VISA and MasterCard transaction would be ".$rate."</p>";

    $html .= "<p>We advise you to maintain a monthly card sales volume that is not less than 300,000 LKR and keep records receipts of your sales for 12 months. If you fail to maintain 300,000 LKR for a particular month ".$fee."LKR would be deducted as a penalty fee and this penalty fee can be varied according to the banks’ requirement. In addition, failure to receive penalty fees for three consecutive months would unavoidably lead us to cancel your merchant membership.</p>";

    $html .= "<p>Further, you have to settle your POS machine daily on business days to receive the payments and failure to settle the machine within 7 days would lead customers’ right to charge back the payments.</p><p>
    Further, please follow below guidelines to safeguard your POS terminal and when performing transactions using cards.
    </p>";

    $html .= <<<EOD
    <ul type='disc'>
        <li>Always perform a proper security due-diligence on cards. <font color="red"> A guide to understand basic security characteristics of cards is given under Annexure-I in this letter. Every payment card has these universal characteristics on the surface.
        </li>
        <li><font color="red">Your POS terminal is PIN enabled. Always ask for the PIN from customer if the terminal is prompting to enter the PIN</li>
        <li><font color="red">Your POS terminal is EMV (Chip) & Contactless enabled. Always look for the microchip embedded <font color="white">___</font> and contactless sign on <font color="white">___</font> the card before        executing the transaction
        </font>
        </li>
        <li><font color="red">Always use insert mode for EMV (Chip) cards & touch mode for Contactless enabled cards.
        </font>
        </li>
        <li><font color="red">If an EMV (Chip) card is giving a “chip reading error” always alert it to the customer and perform transaction using the mag-strip mode.
        </font>
        </li>
        <li><font color="red">If your terminal is constantly giving “chip reader error” , please alert it to the Bank or POS terminal service company as soon as possible
        </font>
        </li>
        <li><font color="red">Always keep the POS terminal in a secure location, away from direct sunlight and moisture
        </font>
        </li>
        <li><font color="red">Your PSTN/GPRS/Android wireless POS terminal is equipped with state of the art security controls and comes with tamper protection. Do not try to do technical maintenances by yourself as it will tamper the terminal and erase of data in the device. Tampered device is chargeable from the merchant.
        </font>
        </li>
        <li><font color="red">It is the merchant’s responsibility to restrict unauthorized access to the POS terminal.
        </font>
        </li>
        <li><font color="red">Do not swipe cards in your cash register machine (terminology: double swipe) right after performing the transaction in the POS terminal. This is a violation of PCI compliance and will be subjected to a fine in a data compromise incident.
        </font>
        </li>
        <li><font color="red">Do not accept cards which you feel suspicious. Use of counterfeited, Lost or stolen cards will result unnecessary losses to your business.
        </font>
        </li>
        <li>Get the necessary identification (Copy of NIC/Passport) of the customer where it is necessary especially for high value transactions.</li>
        <li>Settle your POS machine daily on business days to receive the payments and failure to settle the machine within 7 days would lead customers’ right to charge back the payments.</li>
        <li><font color="red">The POS terminal and its accessories provided are property of People’s Bank. Any lost/theft or damage to these appliances will be chargeable from the merchant.
        </font>
        </li>
        <li><font color="red">In the event of any technical failure, please contact   <b>0701420420</b>
        </font>
        </li>

    </ul>
    <p>We hope the new relationship established would be mutually beneficial and welcome your comments and suggestions to improve our service.</p>

    <p></p>
    <p></p>
    <p></p>
    <p></p>
    <p></p>
    <p></p>

    <p>Manager-Merchant Acquiring</p>


    EOD;


    // Image example with resizing
    $pdf->Image('sim.jpg', 48, 229, 6, 5, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);

    // Image example with resizing
    $pdf->Image('tap.jpg', 95, 229, 6, 5, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);


    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    $pdf->AddPage();


    // Image example with resizing
    $pdf->Image('welcome_annexure1.jpg', 30, 40, 145, 138, 'JPG', 'http://www.tcpdf.org', '', true, 150, '', false, false, 0, false, false, false);

    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', "<p>Annexure –I</p>", 0, 1, 0, true, '', true);
}
?>