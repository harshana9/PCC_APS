<?php

/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/standerdChekListItems/update.php?token=JWT_TOKEN&id=CHK_ID
 *
 * Request Body sample
 * {
 *     "label":"NIC not provided"
 * }
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

/**
 * Validates if the request method is PUT and performs necessary operations accordingly
 */
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // JWT Validation
        if (!isset($_GET['token'])) {
            throw new Exception("Authentication Token Required.");
        }

        $token = trim($_GET['token']);
        // Validate JWT parameters (token, admin, view, delete, create, update)
        JWTValidation($token, true, false, false, false, true);
    } catch (Exception $e) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: " . $e->getMessage(), false, 400);
        exit;
    }

    // Request Parameter existence check
    if (!isset($_GET["id"])) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request parameter 'id' does not exist.", false, 400);
        exit;
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Take post request body
    $requestBody = file_get_contents('php://input');

    // Request body existence check
    if ($requestBody === null) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $chkLabel = null;
    $chkId = null;

    try {
        // Capture Required attributes
        $chkLabel = load_attribute($requestBody, "label", true);
        $chkId = $_GET["id"];

        // Capture optional attributes
        // n/a
    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    // Enter data into the database
    try {
        // Prepare SQL and bind parameters
        $count = 0;
        $sql = "UPDATE `standerd_cheklist` SET `chk_show_name` = :chk_show_name WHERE `chk_id` = :chk_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':chk_show_name', $chkLabel);
        $stmt->bindParam(':chk_id', $chkId);
        $stmt->execute();
        $count += $stmt->rowCount();

        if ($count > 0) {
            header("Status: 200 Data Updated ($count items)", true, 200);
            exit;
        } else {
            header("Status: 500 Internal Server Error", false, 500);
            header("Error: Database Update error.", false, 500);
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
