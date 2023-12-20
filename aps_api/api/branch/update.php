<?php
/**
 * Updates branch data in the 'branch' table based on the provided JSON and branch ID.
 * 
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/branch/update.php?token=JWT_TOKEN&id=PRODUCT_ID
 * 
 * Request Body sample:
 * {
 *     "name":"Horana",
 *     "code":"041",
 *     "email":"horana@peoplesbank.lk",
 *     "zone":12
 * }
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // JWT Token Validation
        if(isset($_GET['token'])){
            $token = trim($_GET['token']);
            try {
                // Validate JWT token for required permissions
                JWTValidation($token, true, false, false, false, true);
            } catch (Exception $e) {
                handleBadRequest(400, "Authentication Failed");
            }
        } else {
            handleBadRequest(400, "Authentication Token Required.");
        }
    } catch (Exception $e) {
        handleBadRequest(400, "Token might be timeout.");
    }

    // Check if 'id' parameter exists in the request
    if(!(isset($_GET["id"]))){
        handleBadRequest(400, "Request parameter id does not exist.");
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Take post request body
    $requestBody = file_get_contents('php://input');

    // Check if request body exists
    if($requestBody === null){
        handleBadRequest(400, "Request body does not exist.");
    }

    // Define data attributes
    $branch_id = $_GET["id"];
    $branch_code = null;
    $branch_email = null;
    $branch_name = null;
    $branch_zone = null;

    try {
        // Capture Required attributes from request body
        $branch_code = loadAttribute($requestBody, "code", true);
        $branch_email = loadAttribute($requestBody, "email", true);
        $branch_name = loadAttribute($requestBody, "name", true);
        $branch_zone = loadAttribute($requestBody, "zone", true);

        // Update data in the database
        $count = 0;
        $sql = "UPDATE `branch` SET `branch_name`=:branch_name,`branch_code`=:branch_code,`branch_email`=:branch_email, `branch_zone`=:branch_zone WHERE `branch_id`=:branch_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':branch_name', $branch_name);
        $stmt->bindParam(':branch_code', $branch_code);
        $stmt->bindParam(':branch_email', $branch_email);
        $stmt->bindParam(':branch_zone', $branch_zone);
        $stmt->bindParam(':branch_id', $branch_id);
        $stmt->execute();
        $count += $stmt->rowCount();

        // Handle success or failure
        if($count > 0){
            handleSuccess(200, "Data Updated ($count items)");
        } else {
            handleServerError(500, "Database update error.");
        }
    } catch(PDOException $e) {
        handleServerError(500, $e->getMessage());
    }
} else {
    handleBadRequest(400, "Invalid request method");
}