<?php

/**
 * API Endpoint: `/aps_api/api/application/delete.php`
 * Request URI format: `http://IP_ADDRESS:PORT_NUMBER/aps_api/api/application/delete.php?token=JWT_TOKEN&id=ITEM_ID`
 * Request Body: N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers for CORS and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    // JWT Token Validation
    try {
        if (isset($_GET['token'])) {
            $jwtToken = trim($_GET['token']);

            try {
                // Validate JWT token with delete permission
                JWTValidation($jwtToken, false, false, true, false, false);
            } catch (Exception $e) {
                // Return authentication error
                returnErrorResponse(400, "Authentication Failed");
            }
        } else {
            // Return error if token is missing
            returnErrorResponse(400, "Authentication Token Required.");
        }
    } catch (Exception $e) {
        // Return error if token might be expired or invalid
        returnErrorResponse(400, "Token might be timed out.");
    }

    // Database Connection
    $dbConnection = new DbCon();
    $conn = $dbConnection->getConn();

    // Retrieve data from GET parameters
    try {
        // Capture the 'id' parameter from GET
        if (!isset($_GET["id"])) {
            returnErrorResponse(400, "Request parameter id does not exist.");
        }

        $id = $_GET["id"];

        if (empty($id)) {
            returnErrorResponse(400, "Request argument for id does not exist.");
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        returnErrorResponse(400, $msg);
    }

    // Delete item from database
    try {
        $sql = "UPDATE `application` SET `app_deleted`=1 WHERE `app_id`=:app_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':app_id', $id);
        $stmt->execute();
        $affected = $stmt->rowCount();

        if ($affected == 1) {
            returnSuccessResponse(200, "One item Deleted");
        } elseif ($affected > 1) {
            returnErrorResponse(500, "Critical Error! Multiple Rows deleted. Please ask for developer support");
        } else {
            returnErrorResponse(500, "Database Delete error.");
        }
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

// Function to return success response with appropriate status code and message
function returnSuccessResponse($statusCode, $message)
{
    header("Status: $statusCode One item Deleted", true, $statusCode);
    exit;
}
