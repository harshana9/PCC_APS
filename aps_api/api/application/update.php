<?php

/**
 * API Endpoint: `/aps_api/api/application/update.php`
 * Request URI format: `http://IP_ADDRESS:PORT_NUMBER/aps_api/api/application/update.php?token=JWT_TOKEN&id=APPLICATION_ID`
 * Request Body sample: {
 *     "MerchantName":"Amal",
 *     "ApplicationEmail":"amal@something.com",
 *     "MerchantId":4353,
 *     "OneTimeFee":24243,
 *     "MonthlyRate":2.5,
 *     "MonthlyFixedFee":2000,
 *     "Nic":'35335',
 *     "ProductModel":35,
 *     "Address":"Test, Test, LK."
 * }
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// CORS headers and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    // JWT Token Validation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token for update permission
                JWTValidation($token, false, false, false, false, true);
            } catch (Exception $e) {
                returnErrorResponse(400, "Authentication Failed");
            }
        } else {
            returnErrorResponse(400, "Authentication Token Required.");
        }
    } catch (Exception $e) {
        returnErrorResponse(400, "Token might be timeout.");
    }

    // Check existence of request parameter 'id'
    if (!(isset($_GET["id"]))) {
        returnErrorResponse(400, "Request parameter id does not exist.");
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Take post request body
    $requestBody = file_get_contents('php://input');

    // Check existence of request body
    if ($requestBody == null) {
        returnErrorResponse(400, "Request body does not exist.");
    }

    // Define data attributes
    $prod_description = null;
    $prod_id = null;

    try {
        // Capture Required attributes
        $MerchantName = load_attribute($requestBody, "MerchantName", true);
        $ApplicationEmail = load_attribute($requestBody, "ApplicationEmail");
        $MerchantId = load_attribute($requestBody, "MerchantId", true);
        $MonthlyFixedFee = load_attribute($requestBody, "MonthlyFixedFee", true);
        $MonthlyRate = load_attribute($requestBody, "MonthlyRate", true);
        $OneTimeFee = load_attribute($requestBody, "OneTimeFee", true);
        $app_nic = load_attribute($requestBody, "Nic");
        $product_model = load_attribute($requestBody, "ProductModel");
        $address = load_attribute($requestBody, "Address");
        $app_id = $_GET["id"];

        // Enter data into the database
        $count = 0;
        $sql = "UPDATE `application` SET `app_merchant_name`=:app_merchant_name, `app_contact_email`=:app_contact_email, `app_merchant_id`=:app_merchant_id, `app_one_time_fee`=:app_one_time_fee, `app_montly_fixed_fee`=:app_Monthly_fixed_fee, `app_fee_rate`=:app_fee_rate, `app_nic`=:app_nic, `app_product_model`=:product_model, `app_address`=:app_address WHERE `app_id`=:app_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':app_merchant_name', $MerchantName);
        $stmt->bindParam(':app_contact_email', $ApplicationEmail);
        $stmt->bindParam(':app_merchant_id', $MerchantId);
        $stmt->bindParam(':app_Monthly_fixed_fee', $MonthlyFixedFee);
        $stmt->bindParam(':app_fee_rate', $MonthlyRate);
        $stmt->bindParam(':app_one_time_fee', $OneTimeFee);
        $stmt->bindParam(':app_nic', $app_nic);
        $stmt->bindParam(':product_model', $product_model);
        $stmt->bindParam(':app_id', $app_id);
        $stmt->bindParam(':app_address', $address);
        $stmt->execute();
        $count += $stmt->rowCount();

        if ($count > 0) {
            header("Status: 200 Data Updated. ($count items)", true, 200);
            exit;
        } else {
            header("Status: 200 Nothing Updated", true, 200);
            exit;
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
?>
