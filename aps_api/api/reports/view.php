<?php

/*
Request URI format
http://IP_ADDRESS:PORT_NUMBER/aps_api/api/reports/view.php?
token=JWT_TOKEN
&out_app_id
&out_product
&out_branch_name
&out_branch_code
&out_zone
&out_status
&out_merchant_type
&out_date
&out_merchant_name
&out_mid
&out_nic
&fil_from
&fil_to
&fil_product
&fil_branch
&fil_zone
&fil_status
&fil_merchant_type
&order_by
&order
&expo_file_type

Request Body sample

*/

//includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";
require_once 'spout-3.3.0/src/Spout/Autoloader/autoload.php';
require_once "StatusReport.php";

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    //JwtValidation
    try{
        if(isset($_GET['token'])){
            $token = trim($_GET['token']);
            try{
                //params of JWTValidation(token, damin, view, delete, create, update)
                JWTValidation($token,false, true, false, false, false);
            }
            catch(Exception $e){
                //echo $e;
                header("Status: 400 Bad Request",false,400);
                header("Authentication Failed" ,false,400);
                exit; 
            }
        }
        else{
            throw new Exception("Error: Authentication Token Required.");
        }
    }
    catch(Exception $e){
        throw new Exception("Error: Token might be timeout.");
    }

    //define data atrributes
    $returnBody=array();
    $out_app_id=null;
    $out_product=null;
    $out_branch_name=null;
    $out_branch_code=null;
    $out_zone=null;
    $out_status=null;
    $out_merchant_type=null;
    $out_date=null;
    $out_merchant_name=null;
    $out_mid=null;
    $out_nic=null;
    $fil_from=null;
    $fil_to=null;
    $fil_product=null;
    $fil_branch=null;
    $fil_zone=null;
    $fil_status=null;
    $fil_merchant_type=null;
    $order_by=null;
    $order=null;
    $expo_file_type=null;

    //Request Patameter existance check
    //Output Columns
    $out_app_id='app_id';
    $output_feild_list="`".$out_app_id."`";
    /*if(isset($_GET["out_app_id"])){
        if($_GET["out_app_id"]=='true'){
            $out_app_id='app_id';
            $output_feild_list.="`".$out_app_id."`";
        }
        $output_feild_list.="`".$out_app_id."`";
    }*/
    if(isset($_GET["out_product"])){
        if($_GET["out_product"]=='true'){
            $out_product='prod_name';
            $output_feild_list.=",`".$out_product."`";
        }
    }
    if(isset($_GET["out_branch_name"])){
        if($_GET["out_branch_name"]=='true'){
            $out_branch_name='branch_name';
            $output_feild_list.=",`".$out_branch_name."`";
        }
    }
    if(isset($_GET["out_branch_code"])){
        if($_GET["out_branch_code"]=='true'){
            $out_branch_code='branch_code';
            $output_feild_list.=",`".$out_branch_code."`";
        }
    }
    if(isset($_GET["out_zone"])){
        if($_GET["out_zone"]=='true'){
            $out_zone='zone_name';
            $output_feild_list.=",`".$out_zone."`";
        }
    }
    if(isset($_GET["out_status"])){
        if($_GET["out_status"]=='true'){
            $out_status='sts_name';
            $output_feild_list.=",`".$out_status."`";
        }
    }
    if(isset($_GET["out_merchant_type"])){
        if($_GET["out_merchant_type"]=='true'){
            $out_merchant_type='bust_name';
            $output_feild_list.=",`".$out_merchant_type."`";
        }
    }
    if(isset($_GET["out_date"])){
        if($_GET["out_date"]=='true'){
            $out_date='app_date';
            $output_feild_list.=",`".$out_date."`";
        }
    }
    if(isset($_GET["out_merchant_name"])){
        if($_GET["out_merchant_name"]=='true'){
            $out_merchant_name='app_merchant_name';
            $output_feild_list.=",`".$out_merchant_name."`";
        }
    }
    if(isset($_GET["out_mid"])){
        if($_GET["out_mid"]=='true'){
            $out_mid='app_merchant_id';
            $output_feild_list.=",`".$out_mid."`";
        }
    }
    if(isset($_GET["out_nic"])){
        if($_GET["out_nic"]=='true'){
            $out_nic='app_nic';
            $output_feild_list.=",`".$out_nic."`";
        }
    }
    //Filter Params
    $whare_conditions="";
    if(isset($_GET["fil_from"])){
        $fil_from=$_GET['fil_from'];
        if($fil_from=="" || $fil_from=="null"){
            $fil_from=null;
        }
        if($fil_from!=null){
            $whare_conditions.=" AND `app_date` >= CAST('".$fil_from."' AS DATE)";
        }
    }
    if(isset($_GET["fil_to"])){
        //echo "Helloooo";
        $fil_to=$_GET['fil_to'];
        if($fil_to=="" || $fil_to=="null"){
            $fil_to=null;
        }
        if($fil_to!=null){
            $whare_conditions.=" AND `app_date` <= CAST('".$fil_to."' AS DATE)";
        }

    }
    if(isset($_GET["fil_product"])){
        $fil_product=$_GET['fil_product'];
        if($fil_product=="" || $fil_product=="null"){
            $fil_product=null;
        }
        if($fil_product!=null){
            $whare_conditions.=" AND `app_product_id`=".$fil_product;
        }
    }
    if(isset($_GET["fil_branch"])){
        $fil_branch=$_GET['fil_branch'];
        if($fil_branch=="" || $fil_branch=="null"){
            $fil_branch=null;
        }
        if($fil_branch!=null){
            $whare_conditions.=" AND `app_branch`=".$fil_branch;
        }
    }
    if(isset($_GET["fil_zone"])){
        $fil_zone=$_GET['fil_zone'];
        if($fil_zone=="" || $fil_zone=="null"){
            $fil_zone=null;
        }
        if($fil_zone!=null){
            $whare_conditions.=" AND `zone_id`=".$fil_zone;
        }
    }
    if(isset($_GET["fil_status"])){
        //echo "Heloooooooo".$_GET['fil_status'];
        $fil_status=$_GET['fil_status'];
        if($fil_status=="" || $fil_status=="null"){
            $fil_status=null;
        }
        if($fil_status!=null){
            $whare_conditions.=" AND `log_status`=".$fil_status;
        }
    }
    if(isset($_GET["fil_merchant_type"])){
        $fil_merchant_type=$_GET['fil_merchant_type'];
        if($fil_merchant_type=="" || $fil_merchant_type=="null"){
            $fil_merchant_type=null;
        }
        if($fil_merchant_type!=null){
            $whare_conditions.=" AND `app_business_type_id`=".$fil_merchant_type;
        }
    }
    //Other
    if(isset($_GET["expo_file_type"])){
        $expo_file_type=$_GET['expo_file_type'];
        /*if($expo_file_type=="null" || $expo_file_type==null || $expo_file_type==""){
            $expo_file_type="EXCEL";
        }*/
    }
    if(isset($_GET["order_by"])){
        $order_by=$_GET['order_by'];
        /*if($order_by=="null" || $order_by==null || $order_by==""){
            $order_by="app_date";
        }*/
    }
    if(isset($_GET["order"])){
        $order=$_GET['order'];
        /*if($order=="null" || $order==null || $order==""){
            $order="ASC";
        }*/
    }

    //Databse Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    //Select data from database
    try{
        // prepare sql and bind parameters
        $sql=null;
        $output_feild_list="*";
        $sql="SELECT ".$output_feild_list." FROM (
                SELECT * FROM `application` 
            LEFT JOIN `branch` 
            ON `application`.`app_branch`=`branch`.`branch_id` 
            LEFT JOIN `product` 
            ON `application`.`app_product_id`=`product`.`prod_id` 
            LEFT JOIN `business_type` 
            ON `application`.`app_business_type_id`=`business_type`.`bust_id` 
            LEFT JOIN `zone` 
            ON `branch`.`branch_zone`=`zone`.`zone_id` 
            WHERE `app_deleted`=0
        ) AS A LEFT JOIN (
            SELECT * FROM (
                SELECT max(`log_id`) AS `last_log_id` FROM `application_status_log` GROUP BY `log_app_id`) AS C 
                LEFT JOIN (
                    SELECT `log_id`,`log_app_id`, `log_datetime`, `log_status`, `sts_name`, `sts_color`, `sts_id`, `log_comments`
                    FROM `application_status_log` 
                    LEFT JOIN `standerd_status`
                    ON `application_status_log`.`log_status` = `standerd_status`.`sts_id`
                ) AS D 
                ON C.`last_log_id`=D.`log_id`
            ) AS B
            ON A.`app_id`=B.`log_app_id` 
        WHERE 1=1".$whare_conditions." ORDER BY `".$order_by."` ".$order.";";

        //echo $sql;

        if($expo_file_type=="EXCEL"){
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        //------------excel---------------------
            $filePath = "exports/excel/StatusReport ".date("d-m-Y(h-i-sa)").".xlsx";
            $writer = WriterEntityFactory::createXLSXWriter();

            $writer->openToFile($filePath); // write data to a file or to a PHP stream

            $cells = array();

            if($out_app_id!=null){
                array_push($cells,WriterEntityFactory::createCell('Application ID'));
            }

            if($out_product!=null){
                array_push($cells,WriterEntityFactory::createCell('Product'));
            }

            if($out_branch_name!=null){
                array_push($cells,WriterEntityFactory::createCell('Branch'));
            }

            if($out_branch_code!=null){
                array_push($cells,WriterEntityFactory::createCell('Branch Code'));
            }

            if($out_zone!=null){
                array_push($cells,WriterEntityFactory::createCell('Zone_Region'));
                //echo "Hello--885".$out_zone;
            }

            if($out_date!=null){
                array_push($cells,WriterEntityFactory::createCell('Application Date'));
            }

            if($out_merchant_type!=null){
                array_push($cells,WriterEntityFactory::createCell('Merchant Type'));
                //echo $out_merchant_type;
                //echo "Hello--88";
                
            }

            if($out_merchant_name!=null){
                array_push($cells,WriterEntityFactory::createCell('Merchant Name'));
                //echo "Hello--88";
            }

            if($out_status!=null){
                array_push($cells,WriterEntityFactory::createCell('Application Status'));
            }

            if($out_mid!=null){
                array_push($cells,WriterEntityFactory::createCell('MID'));
            }

            if($out_nic!=null){
                array_push($cells,WriterEntityFactory::createCell('NIC'));
            }

            /** add a row at a time **/
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            if(count($result)>0){
                $returnItem=array();
                foreach($result as $row) {
                    $cells = array();

                    if($out_app_id!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_app_id]));
                    }

                    if($out_product!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_product]));
                    }

                    if($out_branch_name!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_branch_name]));
                    }

                    if($out_branch_code!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_branch_code]));
                    }

                    if($out_zone!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_zone]));
                    }

                    if($out_date!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_date]));
                    }

                    if($out_merchant_type!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_merchant_type]));
                    }

                    if($out_merchant_name!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_merchant_name]));
                    }

                    if($out_status!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_status]));
                    }

                    if($out_mid!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_mid]));
                    }

                    if($out_nic!=null){
                        array_push($cells,WriterEntityFactory::createCell($row[$out_nic]));
                    }

                    /** add a row at a time **/
                    $singleRow = WriterEntityFactory::createRow($cells);
                    $writer->addRow($singleRow);
                }
            }

            $writer->close();

            //download
            header("Status: 200 Request Fulfilled.",true,200);
            header("Location: $filePath");
            die();
        }
        elseif ($expo_file_type=="PDF"){
            $statusReport;

            //Column set [databse_col_name, report_col_name, report_col_width, strip_character_count]
            $column_set=array();
            //$column_set=array(["app_id","Ap.ID",15], ["app_date","App.Date",25]);


            if($out_app_id!=null){
                array_push($column_set,["app_id","Ap.ID",12,null]);
            }

            if($out_product!=null){
                array_push($column_set,["prod_name","Prod.",13,null]);
            }

            if($out_branch_name!=null){
                array_push($column_set,["branch_name","Branch",33,20]);
            }

            if($out_branch_code!=null){
                array_push($column_set,["app_branch","Br.Cd",12,null]);
            }

            if($out_zone!=null){
                array_push($column_set,["zone_name","Zone",33,null]);
            }

            if($out_date!=null){
                array_push($column_set,["app_date","App.Date",20,null]);
            }

            if($out_merchant_type!=null){
                array_push($column_set,["bust_name","M Type",23,13]);
            }

            if($out_merchant_name!=null){
                array_push($column_set,["app_merchant_name","M Name",70,30]);
            }

            if($out_status!=null){
                array_push($column_set,["sts_name","Status",17,8]);
            }

            if($out_mid!=null){
                array_push($column_set,["app_merchant_id","MID",32,null]);
            }

            if($out_nic!=null){
                array_push($column_set,["app_nic","NIC",26,null]);
            }

            //StatusReport Object
            $statusReport = new StatusReport($fil_status,$column_set, $sql);

            //echo $sql;

            //Generate Pdf
            $statusReport->generate();
        }
    }
    catch(PDOException $e){
        $msg = $e->getMessage();
        header("Status: 500 Internal Server Error",false,500);
        header("Error: $msg",false,500);
        exit;
    }
}
else{
    header("Status: 400 Bad Request",false,400);
    header("Error: Invalid request method",false,400);
    exit;    
}
?>