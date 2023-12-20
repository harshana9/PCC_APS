<?php

/**
 * API Endpoint: `/aps_api/api/application/insert.php`
 * Request URI format: `http://IP_ADDRESS:PORT_NUMBER/aps_api/api/application/insert.php?token=JWT_TOKEN`
 * Request Body sample:
 * {
 *     "date": "2023-12-10",
 *     "branch": 12,
 *     "product": 1,
 *     "businessType": 1,
 *     "merchant": "Jhon and Sons",
 *     "email": "john@jas.com"
 * }
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers for CORS and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Variable to hold username from token
$username_from_token = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // JWT Token Validation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);

            try {
                // Validate JWT token with create permission and get the username
                $username_from_token = JWTValidation($token, false, false, false, true, false);
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
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Take post request body
    $requestBody = file_get_contents('php://input');

    // Check if request body exists
    if ($requestBody === false || empty($requestBody)) {
        returnErrorResponse(400, "Request body does not exist.");
    }

    // Define data attributes
    $app_date = null;
    $app_branch = null;
    $app_productId = null;
    $app_businessType = null;
    $app_merchant = null;
    $app_email = null;
    $returnBody = [];

    try {
        // Capture Required attributes
        $app_date = load_attribute($requestBody, "date", true);
        $app_branch = load_attribute($requestBody, "branch", true);
        $app_productId = load_attribute($requestBody, "product", true);
        $app_businessType = load_attribute($requestBody, "businessType", true);

        // Capture optional attributes
        $app_merchant = load_attribute($requestBody, "merchant");
        $app_email = load_attribute($requestBody, "email");

    } catch (Exception $e) {
        $msg = $e->getMessage();
        returnErrorResponse(400, $msg);
    }

    // Enter data into the database
    try {
        $sql = "CALL create_application(:date, :merchant, :branch, :product_id, :merchant_type, :username, :email);";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':date', $app_date);
        $stmt->bindParam(':merchant', $app_merchant);
        $stmt->bindParam(':branch', $app_branch);
        $stmt->bindParam(':product_id', $app_productId);
        $stmt->bindParam(':merchant_type', $app_businessType);
        $stmt->bindParam(':username', $username_from_token);
        $stmt->bindParam(':email', $app_email);
        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (count($result) > 0) {
            foreach ($result as $row) {
                $returnBody["id"] = $row["last_app_id"];
            }
            echo json_encode($returnBody);
            exit;
        } else {
            returnErrorResponse(500, "Database insert error.");
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
