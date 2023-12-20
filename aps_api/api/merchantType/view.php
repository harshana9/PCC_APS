<?php
/**
 * Retrieves merchant types from the database based on a GET request.
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/merchantType/view.php?token=JWT_TOKEN
 *
 * Request Body sample: N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers to allow access and define allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token for view permission
                JWTValidation($token, false, true, false, false, false);
            } catch (Exception $e) {
                header("Status: 400 Bad Request", false, 400);
                header("Authentication Failed", false, 400);
                exit;
            }
        } else {
            throw new Exception("Error: Authentication Token Required.");
        }
    } catch (Exception $e) {
        throw new Exception("Error: Token might be timeout.");
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Define data attributes
    $returnBody = array();

    // Select data from the database
    try {
        // Prepare SQL and execute
        $sql = "SELECT * FROM `business_type` WHERE `bust_deleted` = 0;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (count($result) > 0) {
            foreach ($result as $row) {
                $returnItem = array();
                $returnItem["id"] = $row["bust_id"];
                $returnItem["status"] = $row["bust_name"];
                $returnItem["description"] = $row["bust_description"];
                array_push($returnBody, $returnItem);
            }
        }
        
        header("Status: 200 Request Fulfilled.", true, 200);
        echo json_encode($returnBody);
        exit;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        header("Status: 500 Internal Server Error", false, 500);
        header("Error: $msg", false, 500);
        exit;
    }
} else {
    header("Status: 400 Bad Request", false, 400);
    header("Error: Invalid request method", false, 400);
    exit;
}
?>