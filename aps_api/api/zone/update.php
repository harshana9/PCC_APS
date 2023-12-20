<?php
/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/zone/update.php?token=JWT_TOKEN&id=ZONE_ID
 *
 * Request Body sample
 * {
 *     "name":"Kalutara"
 * }
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Params of JWTValidation(token, admin, view, delete, create, update)
                JWTValidation($token, true, false, false, false, true);
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

    // Request Parameter existence check
    if (!(isset($_GET["id"]))) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request parameter id does not exist.", false, 400);
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
    $zone_id = null;
    $zone_name = null;

    try {
        // Capture Required attributes
        $zone_name = load_attribute($requestBody, "name", true);
        $zone_id = $_GET["id"];
    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    // Update data in the database
    try {
        // prepare sql and bind parameters
        $count = 0;
        $sql = "UPDATE `zone` SET `zone_name`=:zone_name WHERE `zone_id`=:zone_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':zone_name', $zone_name);
        $stmt->bindParam(':zone_id', $zone_id);
        $stmt->execute();
        $count += $stmt->rowCount();

        if ($count > 0) {
            header("Status: 200 Data Updated.($count items)", true, 200);
            exit;
        } else {
            header("Status: 500 Internal Server Error", false, 500);
            header("Error: Database update error.", false, 500);
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
