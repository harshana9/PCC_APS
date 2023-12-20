<?php

/**
 * Handles adding checklist items to a product based on a POST request.
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/product/addChecklistItem.php?token=JWT_TOKEN
 *
 * Request Body sample:
 * {
 *    "productId": 8,
 *    "businessType": 1,
 *    "ChecklistItemId": 2
 * }
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers to allow access and define allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token
                JWTValidation($token, true, false, false, true, false);
            } catch (Exception $e) {
                header("Status: 400 Bad Request", false, 400);
                header("Error: Authentication Failed", false, 400);
                exit;
            }
        } else {
            header("Status: 400 Bad Request", false, 400);
            header("Error: Authentication Token Required.", false, 400);
            exit;
        }
    } catch (Exception $e) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Token might be timeout.", false, 400);
        exit;
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Take post request body
    $requestBody = file_get_contents('php://input');

    // Request body existence check
    if ($requestBody == null) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $product_id = null;
    $business_type = null;
    $checklist_item_id = null;

    try {
        // Capture Required attributes
        $product_id = load_attribute($requestBody, "productId", true);
        $business_type = load_attribute($requestBody, "businessType", true);
        $checklist_item_id = load_attribute($requestBody, "ChecklistItemId", true);

        // Enter data into the database
        $count = 0;
        $sql = "INSERT INTO `product_check_list`(`product_id`, `business_type`, `checklist_item_id`) VALUES (:prod_id, :bust_id, :chk_id);";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':prod_id', $product_id);
        $stmt->bindParam(':bust_id', $business_type);
        $stmt->bindParam(':chk_id', $checklist_item_id);
        $stmt->execute();
        $count += $stmt->rowCount();

        if ($count > 0) {
            header("Status: 200 Request Fulfilled ($count items)", true, 200);
            exit;
        } else {
            header("Status: 500 Internal Server Error", false, 500);
            header("Error: Database insert error.", false, 500);
            exit;
        }
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
