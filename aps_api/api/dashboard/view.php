<?php
/**
 * Retrieves dashboard statistics from the database after JWT token validation.
 * 
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/dashboard/view.php?token=JWT_TOKEN
 * 
 * Request Body sample: N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // JWT Token Validation
        if(isset($_GET['token'])){
            $token = trim($_GET['token']);
            try {
                // Validate JWT token for required permissions
                JWTValidation($token, false, true, false, false, false);
            } catch (Exception $e) {
                handleBadRequest(400, "Authentication Failed");
            }
        } else {
            throw new Exception("Error: Authentication Token Required.");
        }
    } catch (Exception $e) {
        handleBadRequest(400, "Token might be timeout.");
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Define data attributes
    $returnBody = [];

    try {
        // Select data from the database
        $sql = "CALL dashboard_stat();";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if(count($result) > 0){
            $total = 0;
            foreach($result as $row) {
                $returnItem = [
                    "status" => $row["sts_name"],
                    "count" => $row["count"],
                    "color" => $row["sts_color"]
                ];
                $total += $row["count"];
                array_push($returnBody, $returnItem);
            }

            // Add 'Total' statistics
            $arrTotal = [
                "status" => "Total",
                "count" => $total,
                "color" => "#964B00"
            ];
            array_push($returnBody, $arrTotal);
        }
        
        handleSuccess(200, "Request Fulfilled.", $returnBody);
    } catch(PDOException $e) {
        handleServerError(500, $e->getMessage());
    }
} else {
    handleBadRequest(400, "Invalid request method");
}

?>
