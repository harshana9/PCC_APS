<?php

/**
 * Handles POST request to insert standard reasons.
 *
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/standerdReason/insert.php?token=JWT_TOKEN
 *
 * Request Body sample
 * [
 *     {
 *         "reason":"Application Incomplete"
 *     },
 *     {
 *         "reason":"Agreement not signed"
 *     }
 * ]
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // JWT Validation
        if (!isset($_GET['token'])) {
            throw new Exception("Authentication Token Required.");
        }

        $token = trim($_GET['token']);
        // Validate JWT parameters (token, admin, view, delete, create, update)
        JWTValidation($token, true, false, false, true, false);
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
    $requestBody = load_attribute_set($requestBody);

    // Request body existence check
    if ($requestBody === null) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $dataset = [];

    foreach ($requestBody as $item) {
        $dataItem = [];
        try {
            // Capture Required attributes
            $dataItem["reason"] = load_attribute($item, "reason", true, true);
            
            // Capture optional attributes
            // N/A

            array_push($dataset, $dataItem);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            header("Status: 400 Bad Request", false, 400);
            header("Error: $msg", false, 400);
            exit;
        }
    }

    // Enter data into the database
    try {
        $count = 0;
        // Prepare SQL and bind parameters
        foreach ($dataset as $item) {
            $sql = "INSERT INTO `standerd_status_reason`(`reason_reason`) VALUES (:reason_reason);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':reason_reason', $item["reason"]);
            $stmt->execute();
            $count += $stmt->rowCount();
        }

        if ($count > 0) {
            header("Status: 200 Data Inserted($count items)", true, 200);
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
