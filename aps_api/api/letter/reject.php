<?php
// Import necessary dependencies and utilities
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";
require_once '../../letters/TCPDF/examples/pos_ipg_reject.php';

// Set headers for allowing access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

/**
 * Validates the JWT token for authorization.
 *
 * @param string $token The JWT token for validation
 *
 * @throws Exception When the validation fails or token is missing
 */
function validateToken($token)
{
    if (empty($token)) {
        throw new Exception("Error: Authentication Token Required.");
    }

    try {
        // Params for JWTValidation(token, damin, view, delete, create, update)
        JWTValidation($token, false, true, false, false, false);
    } catch (Exception $e) {
        header("Status: 400 Bad Request", false, 400);
        header("Authentication Failed", false, 400);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Validate JWT token
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            validateToken($token);
        } else {
            throw new Exception("Error: Authentication Token Required.");
        }
    } catch (Exception $e) {
        throw new Exception("Error: Token might be timeout.");
    }

    // Initialize variables
    $returnBody = array();
    $validateAppId = null;
    $generateAppId = null;

    // Check for existence of request parameters
    if (isset($_GET["valId"])) {
        $validateAppId = $_GET["valId"];
    }

    if (isset($_GET["genId"])) {
        $generateAppId = $_GET["genId"];
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    try {
        if ($validateAppId != null) {
            if ($validateAppId != null) {
                // Fetch data from the database for validation application ID
                $logId = null;
                $statusName = null;
                $logComments = null;

                $sql = "SELECT * FROM `application_status_log` LEFT JOIN `standerd_status` ON `application_status_log`.`log_status`=`standerd_status`.`sts_id` WHERE `log_app_id`=:app_id ORDER BY `log_datetime` DESC LIMIT 1;";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':app_id', $validateAppId);
                $stmt->execute();
                $logset = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if (count($logset) > 0) {
                    foreach ($logset as $log) {
                        $logId = $log["log_id"];
                        $statusName = $log["sts_name"];
                        $logComments = $log["log_comments"];
                    }
                }

                // Check if the status is "Rejected" and respond accordingly
                if ($statusName == "Rejected") {
                    echo json_encode(array("msg" => "OK"));
                } else {
                    echo json_encode(array("msg" => "This is not a rejected application."));
                }
            }
        }

        if ($generateAppId != null) {
            // Fetch data from the database for generation application ID
            $logId = null;
            $logComments = null;
            $reasonSet = array();
            $merchantName = null;
            $appId = null;

            $sql = "SELECT * FROM `application_status_log` LEFT JOIN `standerd_status` ON `application_status_log`.`log_status`=`standerd_status`.`sts_id` LEFT JOIN `application` ON `application_status_log`.`log_app_id`=`application`.`app_id` WHERE `log_app_id`=:app_id ORDER BY `log_datetime` DESC LIMIT 1;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':app_id', $generateAppId);
            $stmt->execute();
            $logset = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (count($logset) > 0) {
                foreach ($logset as $log) {
                    $logId = $log["log_id"];
                    $logComments = $log["log_comments"];
                    $merchantName = $log["app_merchant_name"];
                    $appId = $log["app_id"];
                }
            }

            if ($logId != null) {
                // Fetch reasons associated with the log ID
                $sql = "SELECT `log_reason`.`reason_id`,`reason_reason` FROM `log_reason` LEFT JOIN `standerd_status_reason` ON `log_reason`.`reason_id`=`standerd_status_reason`.`reason_id` WHERE `log_reason`.`log_id`=:log_id;";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':log_id', $logId);
                $stmt->execute();
                $reasonSetResult = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if (count($reasonSetResult) > 0) {
                    foreach ($reasonSetResult as $inRow) {
                        array_push($reasonSet, $inRow["reason_reason"]);
                    }
                }
            }

            // Generate POS IPG reject letter
            generate_pos_ipg_reject_letter($merchantName, $appId, $reasonSet, $logComments);
        }

        header("Status: 200 Request Fulfilled.", true, 200);
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
