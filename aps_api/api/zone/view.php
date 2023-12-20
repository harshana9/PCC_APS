<?php
/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/zone/view.php?token=JWT_TOKEN&id=ZONE_ID
 *
 * *zone id is optional
 *
 * Request Body sample
 * N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Params of JWTValidation(token, admin, view, delete, create, update)
                JWTValidation($token, false, true, false, false, false);
            } catch (Exception $e) {
                header("Status: 400 Bad Request", false, 400);
                header("Error: Authentication Failed", false, 400);
                exit;
            }
        } else {
            throw new Exception("Error: Authentication Token Required.");
        }
    } catch (Exception $e) {
        throw new Exception("Error: Token might be timeout.");
    }

    // Define data attributes
    $returnBody = array();
    $zone_id = null;

    // Request Parameter existence check
    if (isset($_GET["id"])) {
        $zone_id = $_GET["id"];
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Select data from database
    try {
        // Prepare SQL and bind parameters
        $sql = null;
        if ($zone_id == null) {
            $zone_id = 0;
            $sql = "SELECT * FROM `zone` WHERE `zone_deleted`=0 AND `zone_id`>:zone_id ORDER BY `zone_name`;";
        } else {
            $sql = "SELECT * FROM `zone` WHERE `zone_id`=:zone_id;";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':zone_id', $zone_id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $returnItem = array(
                    "id" => $row["zone_id"],
                    "name" => $row["zone_name"]
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
