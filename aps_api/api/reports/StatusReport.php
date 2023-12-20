<?php

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/tcpdf.php');
require_once('../../database/dbcon.php');

//Company data load
$ini = parse_ini_file('../../conf/conf.ini');

class MYPDF extends TCPDF {
    // Colored table
    public function ColoredTable($header,$data, $header_width, $strip_data) {
        //$w means column widths
        //$w=$header_width;
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        // Header
        $num_headers = count($header);
        for($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($header_width[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = 0;
        foreach($data as $row) {
            for ($i=0; $i < count($header); $i++) { 
                if($strip_data[$i]==null){
                    $this->Cell($header_width[$i], 6, $row[$i], 'LR', 0, 'L', $fill);
                }
                else{
                    $this->Cell($header_width[$i], 6, substr($row[$i],0,$strip_data[$i]), 'LR', 0, 'L', $fill);
                } 
            }
            /*
            $this->Cell($w[1], 6, substr($row[1],0,9), 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, $row[2], 'LR', 0, 'L', $fill);
            $this->Cell($w[3], 6, substr($row[3],0,12), 'LR', 0, 'L', $fill);
            $this->Cell($w[4], 6, substr($row[4],0,5), 'LR', 0, 'L', $fill);
            $this->Cell($w[5], 6, $row[5], 'LR', 0, 'L', $fill);
            $this->Cell($w[6], 6, substr($row[6],0,12), 'LR', 0, 'L', $fill);*/
            //$this->Cell($w[6], 6, number_format($row[3]), 'LR', 0, 'R', $fill);

            $this->Ln();
            $fill=!$fill;
        }
        $this->Cell(array_sum($header_width), 0, '', 'T');
    }
}


class StatusReport {

    private $status;
    private $ini;
    private $column_set;
    private $sql;

    public function __construct($status=null, $column_set, $sql, $iniPath="../../conf/conf.ini")
    {
        $this->ini = $ini = parse_ini_file($iniPath);
        $this->status = $status;
        $this->sql = $sql;
        $this->column_set=$column_set;

        date_default_timezone_set($ini['time_zone']);
    }

    public function generate(){
        //Databse Connection
        $dbCon = new DbCon();
        $conn = $dbCon->getConn();

        $ini = $this->ini;
        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


        //echo K_PATH_IMAGES;

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Application Proceesing System @'.$ini["COMPANY"]);
        $pdf->SetTitle('Status Report');
        $pdf->SetSubject('System Generated Report');
        $pdf->SetKeywords($ini['ADDRESSL1'].$ini['ADDRESSL2'], 'Hotline: '.$ini["TELEPHONE"]);

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
        $pdf->SetFont('', '', 10, '', true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // set text shadow effect
        $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        //get date time
        $date_time=date("d-m-Y h:i:sa");

        // Set some content to print
        $html = <<<EOD
        <font size="15">Application Status Report</font><br/>
        <font>Generated at : $date_time</font><br/>

        EOD;

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        //Get data set
        $data=array();
        $header_width=array();
        $header=array();

        $hide_compleated=2;

        $stmt = $conn->prepare($this->sql);
        //$stmt->bindParam(':compleated', $hide_compleated);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(count($result)>0){
            $returnItem=array();
            foreach($result as $row) {
                $dataset=array();
                foreach ($this->column_set as $value){
                    array_push($dataset, $row[$value[0]]);
                }
                if($this->status==null){
                    array_push($data, $dataset);
                }
                else{
                    //echo "--".$this->status."--".$row["sts_id"];
                    if($this->status==$row["sts_id"]){

                        //echo $this->status;

                        array_push($data, $dataset);
                    }
                }
            }
        }

        // column headers
        $header_width=array();
        //$header = array('Ap.ID', 'Status', 'MID', 'M Type', 'Prod.','App.Date','Branch');
        $header=array();
        $strip_data=array();
        foreach ($this->column_set as $value){
            array_push($header, $value[1]);
            array_push($header_width, $value[2]);
            array_push($strip_data, $value[3]);
        }


        //Create table
        $pdf->ColoredTable($header, $data, $header_width, $strip_data);

        // ---------------------------------------------------------

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        
        $file_name="StatusReport ".date("d-m-Y(h-i-sa)").".pdf";
        $pdf->Output($file_name, 'I');

        //============================================================+
        // END OF FILE
        //============================================================+
    }
}