<?php

/*
Request URI format
http://IP_ADDRESS:PORT_NUMBER/aps_api/api/autoLogout/getTime.php

Request Body sample
N/A

*/

//includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $iniPath="../../conf/conf.ini";
    $ini = parse_ini_file($iniPath);

    header("Status: 200 Request Fulfilled.",true,200);
    echo  json_encode(array("timeout"=>$ini["TIMEOUT"]));
    exit;
}
else{
    header("Status: 400 Bad Request",false,400);
    header("Error: Invalid request method",false,400);
    exit;    
}
?>