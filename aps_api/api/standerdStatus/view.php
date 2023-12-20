<?php

/**
 * Handles GET request to retrieve data from the 'standerd_status' table.
 *
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/standerdStatus/view.php?token=JWT_TOKEN
 *
 * Request Body sample
 * N/A
 */

// includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // JWT Validation
        if (!isset($_GET['token'])) {
            throw new Exception("Error: Authentication Token Required.");
        }

        $token = trim($_GET['token']);
        // Validate JWT parameters (token, admin, view, delete, create, update)
        JWTValidation($token, false, true, false, false, false);
    } catch (Exception $e) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: " . $e->getMessage(), false, 400);
        exit;
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Define data attributes
    $returnBody = array();

    // Select data from the database
    try {
        // Prepare SQL and bind parameters
        $sql = "SELECT * FROM `standerd_status` WHERE `sts_deleted`=0 AND `sts_id`>1 ORDER BY `sts_name`;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $returnItem = array(
                    "id" => $row["sts_id"],
                    "status" => $row["sts_name"],
                    "color" => $row["sts_color"]
                );
                array_push($returnBody, $returnItem);
            }
        }

        header("Status: 200 Request Fulfilled.", true, 200);
        echo json_encode($returnBody);
        exit;
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
