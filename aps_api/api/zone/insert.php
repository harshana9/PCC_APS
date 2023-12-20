<?php
/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/zone/insert.php?token=JWT_TOKEN
 *
 * Request Body sample
 * [
 *    {
 *        "name":"Kalutara"
 *    }
 * ]
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Params of JWTValidation(token, admin, view, delete, create, update)
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
    $requestBody = load_attribute_set($requestBody);

    // Request body existence check
    if ($requestBody == null || empty($requestBody)) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist or is empty.", false, 400);
        exit;
    }

    // Define data attributes
    $dataset = array();

    foreach ($requestBody as $item) {
        $dataitem = array();
        try {
            // Capture Required attributes
            $dataitem["name"] = load_attribute($item, "name", true, true);
            array_push($dataset, $dataitem);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            header("Status: 400 Bad Request", false, 400);
            header("Error: $msg", false, 400);
            exit;
        }
    }

    // Enter data to database
    try {
        $count = 0;
        // prepare sql and bind parameters
        foreach ($dataset as $item) {
            $sql = "INSERT INTO `zone`(`zone_name`) VALUES (:zone_name);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':zone_name', $item["name"]);
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
?>
