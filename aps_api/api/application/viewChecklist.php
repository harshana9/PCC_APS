<?php

/**
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/application/viewChecklist.php?token=JWT_TOKEN&id=APPLICATION_ID
 * Request Body sample: N/A
 */

// includes
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
                // params of JWTValidation(token, damin, view, delete, create, update)
                JWTValidation($token, false, true, false, false, false);
            } catch (Exception $e) {
                header("Status: 400 Bad Request", false, 400);
                header("Authentication Failed", false, 400);
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
    $application_id = null;

    // Request Parameter existence check
    if (isset($_GET["id"])) {
        $application_id = $_GET["id"];
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Select data from database
    try {
        // prepare sql and bind parameters
        $sql = "SELECT `application_checklist`.`app_chk_chk_id`, `standerd_cheklist`.`chk_show_name`, `application_checklist`.`app_chk_checked` FROM `application_checklist` LEFT JOIN `standerd_cheklist` ON `application_checklist`.`app_chk_chk_id`=`standerd_cheklist`.`chk_id` WHERE `application_checklist`.`app_chk_app_id`=:app_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':app_id', $application_id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            foreach ($result as $row) {
                // Initialize $returnItem inside the loop
                $returnItem = array();
                $returnItem["id"] = $row["app_chk_chk_id"];
                $returnItem["label"] = $row["chk_show_name"];
                $returnItem["checked"] = ($row["app_chk_checked"] == 1) ? true : false;

                // Push each checklist item into the return body
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
