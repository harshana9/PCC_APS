<?php
/*
Request URI format
http://IP_ADDRESS:PORT_NUMBER/aps_api/api/pdf/report/stsReport.php?token=JWT_TOKEN&status=STATUS_ID

*/

require_once "StatusReport.php";
require_once "../../request/jwtVerify.php";


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

	$statusReport;

	//StatusReport Object
	if(isset($_GET["status"])){
		if($_GET["status"]=="null"){
			$statusReport = new StatusReport();
		}
		else{
			$statusReport = new StatusReport($_GET["status"]);
		}
	}
	else{
		$statusReport = new StatusReport();
	}


	//Generate Pdf
	$statusReport->generate();

}

?>