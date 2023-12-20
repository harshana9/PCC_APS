<?php
/**
 * Retrieves branch data from the 'branch' table based on the provided ID (optional) after JWT token validation.
 * 
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/branch/view.php?token=JWT_TOKEN&id=PRODUCT_ID
 * 
 * Product ID is optional.
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

    // Define data attributes
    $returnBody = [];
    $branch_id = isset($_GET["id"]) ? $_GET["id"] : null;

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    try {
        // prepare SQL and bind parameters
        $sql = ($branch_id === null)
            ? "SELECT * FROM `branch` LEFT JOIN `zone` ON `branch`.`branch_zone`=`zone`.`zone_id` WHERE `branch_deleted`=0 AND `branch_id`>:branch_id ORDER BY `branch_code`;"
            : "SELECT * FROM `branch` LEFT JOIN `zone` ON `branch`.`branch_zone`=`zone`.`zone_id` WHERE `branch_id`=:branch_id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':branch_id', $branch_id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if(count($result) > 0){
            foreach($result as $row) {
                $returnItem = [
                    "id" => $row["branch_id"],
                    "name" => $row["branch_name"],
                    "code" => $row["branch_code"],
                    "zone" => $row["zone_name"],
                    "zoneId" => $row["zone_id"],
                    "email" => $row["branch_email"]
                ];
                array_push($returnBody, $returnItem);
            }
        }
        
        handleSuccess(200, "Request Fulfilled.", $returnBody);
    } catch(PDOException $e) {
        handleServerError(500, $e->getMessage());
    }
} else {
    handleBadRequest(400, "Invalid request method");
}

?>
