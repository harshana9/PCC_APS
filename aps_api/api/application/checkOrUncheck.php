<?php

/**
 * Endpoint to toggle checklist item status.
 * Request URI format: http://IP_ADDRESS:PORT_NUMBER/aps_api/api/application/checkOrUncheck.php?token=JWT_TOKEN
 * Request Body sample:
 * {
 *     "applicationId": 1,
 *     "checkId": 14
 * }
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers for CORS and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    // Validate JWT Token
    try {
        if (isset($_GET['token'])) {
            $jwtToken = trim($_GET['token']);

            try {
                // Validate JWT token with specific permissions
                JWTValidation($jwtToken, false, false, false, false, true);
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

    // Establish database connection
    $dbConnection = new DbCon();
    $conn = $dbConnection->getConn();

    // Retrieve request body
    $requestBody = file_get_contents('php://input');

    // Check for existence of request body
    if ($requestBody == null) {
        returnErrorResponse(400, "Request body does not exist.");
    }

    // Define data attributes
    $applicationId = null;
    $checkId = null;

    try {
        // Capture required attributes from the request body
        $applicationId = load_attribute($requestBody, "applicationId", true);
        $checkId = load_attribute($requestBody, "checkId", true);
    } catch (Exception $e) {
        // Return error if required attributes are missing
        $errorMsg = $e->getMessage();
        returnErrorResponse(400, $errorMsg);
    }

    // Update database with checklist item status
    try {
        // Prepare SQL and bind parameters
        $count = 1;
        $sql = "UPDATE `application_checklist` SET `app_chk_checked`=not(`app_chk_checked`) WHERE `app_chk_app_id`=:app_chk_app_id AND `app_chk_chk_id`=:app_chk_chk_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':app_chk_app_id', $applicationId);
        $stmt->bindParam(':app_chk_chk_id', $checkId);
        $stmt->execute();
        $count += $stmt->rowCount();

        if ($count > 0) {
            // Return success response with the count of updated items
            returnSuccessResponse(200, "Checklist Updated ($count items)");
        } else {
            // Return internal server error if update fails
            returnErrorResponse(500, "Database Update error.");
        }
    } catch (PDOException $e) {
        // Return internal server error for database exceptions
        $errorMsg = $e->getMessage();
        returnErrorResponse(500, $errorMsg);
    }
} else {
    // Return error for invalid request method
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
    header("Status: $statusCode Check List Updated", true, $statusCode);
    exit;
}