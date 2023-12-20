<?php
/**
 * Handles retrieval of log data from the database based on GET requests.
 *
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/log/view.php?token=JWT_TOKEN&id=APPLICATION_ID
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Headers to allow access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token for view permission
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
        throw new Exception("Error: Token might be timed out.");
    }

    // Define data attributes
    $returnBody = array();
    $app_id = null;

    // Check existence of request parameter
    if (isset($_GET["id"])) {
        $app_id = $_GET["id"];
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Select log data from the database
    try {
        $sql = "SELECT `application_status_log`.`log_id`, `application_status_log`.`log_datetime`,`standerd_status`.`sts_name`, `application_status_log`.`log_comments`, `user`.`usr_username` FROM `application_status_log` LEFT JOIN `standerd_status` ON `application_status_log`.`log_status`=`standerd_status`.`sts_id` LEFT JOIN `user` ON `application_status_log`.`log_user`=`user`.`usr_id` WHERE `application_status_log`.`log_app_id`=:log_app_id ORDER BY `application_status_log`.`log_datetime`;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':log_app_id', $app_id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $returnItem = array();
                $returnItem["log_id"] = $row["log_id"];
                $returnItem["datetime"] = $row["log_datetime"];
                $returnItem["status"] = $row["sts_name"];

                // Fetch reasons related to the log
                $sql = "SELECT `log_reason`.`reason_id`,`reason_reason` FROM `log_reason` LEFT JOIN `standerd_status_reason` ON `log_reason`.`reason_id`=`standerd_status_reason`.`reason_id` WHERE `log_reason`.`log_id`=" . $row["log_id"] . ";";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $reason_set_result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $reasonSet = array();
                if (count($reason_set_result) > 0) {
                    foreach ($reason_set_result as $in_row) {
                        $reason = array();
                        $reason["id"] = $in_row["reason_id"];
                        $reason["reason"] = $in_row["reason_reason"];
                        array_push($reasonSet, $reason);
                    }
                }
                $returnItem["reason"] = $reasonSet;

                $returnItem["comment"] = $row["log_comments"];
                $returnItem["user"] = $row["usr_username"];
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
