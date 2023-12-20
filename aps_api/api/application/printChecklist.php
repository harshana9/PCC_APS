<?php

/**
 * API Endpoint: `/aps_api/api/application/printChecklist.php`
 * Request URI format: `http://IP_ADDRESS:PORT_NUMBER/aps_api/api/application/printChecklist.php?token=JWT_TOKEN&id=APPLICATION_ID`
 * Request Body sample: N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";
require_once('../../letters/TCPDF/examples/checklist.php');
require_once "../../request/php-jwt/JwtHandler.php";

// Set headers for CORS and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

$token = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // JWT Token Validation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);

            try {
                // Validate JWT token with view permission
                JWTValidation($token, false, true, false, false, false);
            } catch (Exception $e) {
                returnErrorResponse(400, "Authentication Failed");
            }
        } else {
            throw new Exception("Error: Authentication Token Required.");
        }
    } catch (Exception $e) {
        throw new Exception("Error: Token might be timed out.");
    }

    // Define data attributes
    $returnBody = [];
    $application_id = null;

    // Check existence of request parameter 'id'
    if (isset($_GET["id"])) {
        $application_id = $_GET["id"];
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Select data from the database
    try {
        $product = null;
        $merchant = null;
        $merchant_type = null;
        $branch = null;
        $checklist = [];
        $circuler = null;
        $user = null;

        // Get basic application data
        $sql = "CALL view_application(:app_id);";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':app_id', $application_id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (count($result) > 0) {
            foreach ($result as $row) {
                $product = $row["prod_name"];
                $merchant = $row["app_merchant_name"];
                $merchant_type = $row["bust_name"];
                $branch = $row["branch_name"];
            }
        }

        // Fetch checklist data
        $sql = "SELECT `application_checklist`.`app_chk_chk_id`, `standerd_cheklist`.`chk_show_name`, `application_checklist`.`app_chk_checked` FROM `application_checklist` LEFT JOIN `standerd_cheklist` ON `application_checklist`.`app_chk_chk_id`=`standerd_cheklist`.`chk_id` WHERE `application_checklist`.`app_chk_app_id`=:app_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':app_id', $application_id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $checklist[$row["chk_show_name"]] = $row["app_chk_checked"];
            }
        }

        // Set 'circuler' based on 'product'
        if ($product == "POS" || $product == "IPG") {
            $circuler = "1564/2022";
        } elseif ($product == "MPOS") {
            $circuler = "1694/2023";
        }

        // Get user details from JWT token
        if ($token != null) {
            $jwt = new JwtHandler();
            $data =  $jwt->jwtDecodeData($token);

            $sql = "SELECT `usr_fname`, `usr_lname` FROM `user` WHERE `usr_username`=:username;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $data->username);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                foreach ($result as $row) {
                    $user = $row["usr_fname"] . " " . $row["usr_lname"];
                }
            }
        }

        // Generate and print checklist
        print_checklist($product, $merchant, $merchant_type, $branch, $checklist, $circuler, $user);

        header("Status: 200 Request Fulfilled.", true, 200);
        echo  json_encode($returnBody);
        exit;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        returnErrorResponse(500, $msg);
    }
} else {
    returnErrorResponse(400, "Invalid request method");
}

// Function to return error response with appropriate status code and message
function returnErrorResponse($statusCode, $errorMessage)
{
    header("Status: $statusCode", false, $statusCode);
    header("Error: $errorMessage", false, $statusCode);
    exit;
}
?>