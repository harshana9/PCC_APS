<?php

/*
Request URI format
http://IP_ADDRESS:PORT_NUMBER/aps_api/execl/report/file.php?token=JWT_TOKEN&id=STATUS_ID

*/

//includes
require_once "../../database/dbcon.php";
require_once "../../request/jwtVerify.php";
require_once '../spout-3.3.0/src/Spout/Autoloader/autoload.php';

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


    $filePath = "StatusReport ".date("d-m-Y(h-i-sa)").".xlsx";

    $writer = WriterEntityFactory::createXLSXWriter();
    // $writer = WriterEntityFactory::createODSWriter();
    // $writer = WriterEntityFactory::createCSVWriter();

    $writer->openToFile($filePath); // write data to a file or to a PHP stream
    //$writer->openToBrowser($fileName); // stream data directly to the browser

    $cells = [
        WriterEntityFactory::createCell('Creation Date'),
        WriterEntityFactory::createCell('Merchnat Name'),
        WriterEntityFactory::createCell('Product'),
        WriterEntityFactory::createCell('MID'),
        WriterEntityFactory::createCell('NIC'),
        WriterEntityFactory::createCell('Introduced Branch'),
        WriterEntityFactory::createCell('Branch Code'),
    ];

    /** add a row at a time **/
    $singleRow = WriterEntityFactory::createRow($cells);
    $writer->addRow($singleRow);

    /** add multiple rows at a time **/
    /*$multipleRows = [
        WriterEntityFactory::createRow($cells),
        WriterEntityFactory::createRow($cells),
    ];
    $writer->addRows($multipleRows); */

    /** Shortcut: add a row from an array of values */
    //$values = ['Carl', 'is', 'great!'];
    $values=array();

    //Databse Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    //Select data from database
    try{
        // prepare sql and bind parameters
        $sql=null;
        if(isset($_GET["status"])){
            $status=$_GET["status"];
            $sql="CALL approved_exel_report(:status);";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if(count($result)>0){
            $returnItem=array();
            foreach($result as $row) {
                $values = [$row["app_date"],$row["app_merchant_name"],$row["prod_name"],$row["app_merchant_id"],'',$row["branch_name"],$row["branch_code"]];

                $rowFromValues = WriterEntityFactory::createRowFromArray($values);
                $writer->addRow($rowFromValues);
            }
        }
    }
    catch(PDOException $e){
        $msg = $e->getMessage();
        header("Status: 500 Internal Server Error",false,500);
        header("Error: $msg",false,500);
        exit;
    }


    $writer->close();

    //-------------------------------------File Download-----------
    header("Location: $filePath");
    die();


}

?>