<?php

/**
 * Handles DELETE request to delete a standard reason item by ID.
 *
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/standerdReason/delete.php?token=JWT_TOKEN&id=ITEM_ID
 *
 * Request Body sample
 * n/a
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // JWT Validation
        if (!isset($_GET['token'])) {
            throw new Exception("Authentication Token Required.");
        }

        $token = trim($_GET['token']);
        // Validate JWT parameters (token, admin, view, delete, create, update)
        JWTValidation($token, true, false, true, false, false);
    } catch (Exception $e) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: " . $e->getMessage(), false, 400);
        exit;
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Take post request body
    $requestBody = file_get_contents('php://input');

    // Request Parameter existence check
    if (!isset($_GET["id"])) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request parameter 'id' does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $reasonId = null;

    try {
        // Capture Required attributes
        $reasonId = $_GET["id"];

        if ($reasonId === null || $reasonId === "") {
            header("Status: 400 Bad Request", false, 400);
            header("Error: Request argument for 'id' does not exist.", false, 400);
            exit;
        }

        // Capture optional attributes
        // n/a
    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    // Delete data from the database
    try {
        // prepare sql and bind parameters
        $sql = "UPDATE `standerd_status_reason` SET `reason_deleted`=1 WHERE `reason_id`=:reason_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':reason_id', $reasonId);
        $stmt->execute();
        $affected = $stmt->rowCount();

        if ($affected === 1) {
            header("Status: 200 One item Deleted", true, 200);
            exit;
        } elseif ($affected > 1) {
            header("Status: 500 Internal Server Error", false, 500);
            header("Error: Critical Error! Multiple Rows deleted. Please ask for developer support", false, 500);
            exit;
        } else {
            header("Status: 500 Internal Server Error", false, 500);
            header("Error: Database Delete error.", false, 500);
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
