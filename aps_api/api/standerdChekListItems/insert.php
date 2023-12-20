<?php

/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/standerdChekListItems/insert.php?token=JWT_TOKEN
 *
 * Request Body sample
 * [
 *     {
 *         "label":"Application Signed By Merchant"
 *     },
 *     {
 *         "label":"Second label"
 *     }
 * ]
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

/**
 * Validates if the request method is POST and performs necessary operations accordingly
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // JWT Validation
        if (!isset($_GET['token'])) {
            throw new Exception("Error: Authentication Token Required.");
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
    if ($requestBody == null) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $dataset = array();

    foreach ($requestBody as $item) {
        // Take data from http request body
        $dataitem = array();
        try {
            // Capture Required attributes
            $dataitem["label"] = load_attribute($item, "label", true, true);
            array_push($dataset, $dataitem);
            // Capture optional attributes
            // n/a
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
        // Prepare sql and bind parameters
        foreach ($dataset as $chkitem) {
            $sql = "INSERT INTO `standerd_cheklist`(`chk_show_name`) VALUES (:chk_label);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':chk_label', $chkitem["label"]);
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
