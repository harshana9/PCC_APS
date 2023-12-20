<?php

/**
 * Handles DELETE request to delete an item from the 'standerd_status' table.
 *
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/standerdStatus/delete.php?token=JWT_TOKEN&id=ITEM_ID
 *
 * Request Body sample
 * n/a
 */

// includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // JWT Validation
        if (!isset($_GET['token'])) {
            throw new Exception("Error: Authentication Token Required.");
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
    if (!(isset($_GET["id"])))) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request parameter id does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $id = null;

    // Take data get parameter
    try {
        // Capture Required attributes
        $id = $_GET["id"];

        if ($id == null or $id == "") {
            header("Status: 400 Bad Request", false, 400);
            header("Error: Request argument for id does not exist.", false, 400);
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

    // Enter data into the database
    try {
        // prepare sql and bind parameters
        $sql = "UPDATE `standerd_status` SET `sts_deleted`=1 WHERE `sts_id`=:sts_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':sts_id', $id);
        $stmt->execute();
        $affected = $stmt->rowCount();

        if ($affected == 1) {
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
?>
