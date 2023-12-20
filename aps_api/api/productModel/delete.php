<?php

/**
 * Delete Product Model Module
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/productModel/delete.php?token=JWT_TOKEN&id=PRODUCT_MODEL_ID
 *
 * Request Body sample:
 * n/a
 */

//includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    //JwtValidation
    try{
        if(isset($_GET['token'])){
            $token = trim($_GET['token']);
            try{
                //params of JWTValidation(token, admin, view, delete, create, update)
                JWTValidation($token,true, false, true, false, false);
            }
            catch(Exception $e){
                //echo $e;
                header("Status: 400 Bad Request",false,400);
                header("Error: Authentication Failed" ,false,400);
                exit; 
            }
        }
        else{
            header("Status: 400 Bad Request",false,400);
            header("Error: Authentication Token Required." ,false,400);
            exit; 
        }
    }
    catch(Exception $e){
        header("Status: 400 Bad Request",false,400);
        header("Error: Token might be timeout." ,false,400);
        exit; 
    }

    //Databse Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    //Take post request body
    $requestBody = file_get_contents('php://input');

    //Request Patameter existance check
    if(!(isset($_GET["id"]))){
        header("Status: 400 Bad Request",false,400);
        header("Error: Request parameter id does not exist.",false,400);
        exit;
    }

    //define data attributes
    $id=null;

    //Take data get parameter
    try{
        //Capture Required attributes
        $id=$_GET["id"];

        if($id==null or $id==""){
            header("Status: 400 Bad Request",false,400);
            header("Error: Request argument for id does not exist.",false,400);
            exit;           
        }

        //capture optional attributes
        //n/a
    }
    catch(Exception $e){
        $msg = $e->getMessage();
        header("Status: 400 Bad Request",false,400);
        header("Error: $msg",false,400);
        exit;
    }

    //Enter data to database
    try{
        // prepare sql and bind parameters
        $sql = "UPDATE `product_model` SET `prod_model_deleted`=1 WHERE `prod_model_id`=:prod_model_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':prod_model_id', $id);
        $stmt->execute();
        $affected = $stmt->rowCount();

        if($affected==1){
            header("Status: 200 One item Deleted",true,200);
            exit;
        }
        elseif ($affected>1) {
            header("Status: 500 Internal Server Error",false,500);
            header("Error: Critical Error! Multiple Rows deleted. Please ask for developer support",false,500);
            exit;
        }
        else{
            header("Status: 500 Internal Server Error",false,500);
            header("Error: Database Delete error.",false,500);
            exit;
        }
    }
    catch(PDOException $e){
        $msg = $e->getMessage();
        //print_r($e);
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
