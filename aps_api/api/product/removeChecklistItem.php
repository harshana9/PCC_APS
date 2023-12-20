<?php

/**
 * Remove checklist item module
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/product/removeChecklistItem.php?token=JWT_TOKEN&productId=PRODUCT_ID&businessType=BUSINESS_TYPE_ID&ChecklistItemId=CHECKLIST_ITEM_ID
 *
 * Request Body sample:
 * N/A
 **/

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers to allow access and define allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token
                JWTValidation($token, true, false, true, false, false);
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

    // Define data attributes
    $product_id = null;
    $business_type = null;
    $checklist_item_id = null;

    try {
        // Capture Required attributes
        if (isset($_GET["productId"]) && isset($_GET["businessType"]) && isset($_GET["ChecklistItemId"])) {
            if ($_GET["productId"] != "") {
                $product_id = $_GET["productId"];
            } else {
                throw new Exception("Required parameter productId missing.");
            }

            if ($_GET["businessType"] != "") {
                $business_type = $_GET["businessType"];
            } else {
                throw new Exception("Required parameter businessType missing.");
            }

            if ($_GET["ChecklistItemId"] != "") {
                $checklist_item_id = $_GET["ChecklistItemId"];
            } else {
                throw new Exception("Required parameter ChecklistItemId missing.");
            }
        } else {
            throw new Exception("One or more required parameter(s) missing.");
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    // Enter data into the database
    try {
        // prepare sql and bind parameters
        $count = 0;
        $sql = "UPDATE `product_check_list` SET `deleted`=1 WHERE `product_id`=:prod_id AND `business_type`=:bust_id AND `cheklist_item_id`=:chk_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':prod_id', $product_id);
        $stmt->bindParam(':bust_id', $business_type);
        $stmt->bindParam(':chk_id', $checklist_item_id);
        $stmt->execute();
        $count += $stmt->rowCount();

        if ($count > 0) {
            header("Status: 200 Request Fulfilled($count items)", true, 200);
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
