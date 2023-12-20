<?php
/**
 * Deletes an item from the 'branch' table in the database based on the provided ID.
 * 
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/standerdStatus/delete.php?token=JWT_TOKEN&id=ITEM_ID
 * 
 * Request Body sample: n/a
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Enable CORS and set allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");

// Validate HTTP request method
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    // JWT Token Validation
    try {
        if(isset($_GET['token'])){
            $jwtToken = trim($_GET['token']);
            try {
                // Validate JWT token for required permissions
                JWTValidation($jwtToken, true, false, true, false, false);
            } catch (Exception $e) {
                // Handle authentication failure
                handleBadRequest(400, "Authentication Failed");
            }
        } else {
            // Token not provided in the request
            handleBadRequest(400, "Authentication Token Required.");
        }
    } catch (Exception $e) {
        // Token might have expired
        handleBadRequest(400, "Token might be expired.");
    }

    // Establish database connection
    $dbConnection = new DbCon();
    $conn = $dbConnection->getConn();

    // Check for 'id' parameter existence
    if (!isset($_GET["id"])) {
        handleBadRequest(400, "Request parameter 'id' does not exist.");
    }

    // Extract and validate 'id' parameter
    $itemId = $_GET["id"];
    if (empty($itemId)) {
        handleBadRequest(400, "Request argument for 'id' does not exist.");
    }

    try {
        // Prepare SQL statement and bind parameters
        $sql = "UPDATE `branch` SET `branch_deleted` = 1 WHERE `branch_id` = :branch_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':branch_id', $itemId);
        $stmt->execute();
        $affectedRows = $stmt->rowCount();

        // Check affected rows for successful deletion
        if ($affectedRows == 1) {
            handleSuccess(200, "One item deleted");
        } elseif ($affectedRows > 1) {
            // More than one row affected, which should not happen
            handleServerError(500, "Critical Error! Multiple Rows deleted. Please ask for developer support");
        } else {
            // No rows affected, possible database delete error
            handleServerError(500, "Database Delete error.");
        }
    } catch (PDOException $e) {
        // Handle database-related errors
        handleServerError(500, $e->getMessage());
    }
} else {
    // Invalid request method
    handleBadRequest(400, "Invalid request method");
}

/**
 * Handle Bad Request and set appropriate HTTP response code and error message.
 *
 * @param int $statusCode HTTP status code
 * @param string $errorMessage Error message to display
 */
function handleBadRequest($statusCode, $errorMessage) {
    header("Status: $statusCode Bad Request", false, $statusCode);
    header("Error: $errorMessage", false, $statusCode);
    exit;
}

/**
 * Handle Server Error and set appropriate HTTP response code and error message.
 *
 * @param int $statusCode HTTP status code
 * @param string $errorMessage Error message to display
 */
function handleServerError($statusCode, $errorMessage) {
    header("Status: $statusCode Internal Server Error", false, $statusCode);
    header("Error: $errorMessage", false, $statusCode);
    exit;
}

/**
 * Handle Success and set appropriate HTTP response code and success message.
 *
 * @param int $statusCode HTTP status code
 * @param string $successMessage Success message to display
 */
function handleSuccess($statusCode, $successMessage) {
    header("Status: $statusCode $successMessage", true, $statusCode);
    exit;
}
?>