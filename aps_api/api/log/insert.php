<?php
/**
 * Handles insertion of log data into the database based on POST requests.
 *
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/log/insert.php?token=JWT_TOKEN
 *
 * Request Body sample
 * {
 *     "applicationId": 16,
 *     "status": 10,
 *     "reason": [12, 12, 23],
 *     "comment": "Need review"
 * }
 *
 * * reason is optional
 * * comment is optional
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Headers to allow access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

$usernameFromToken = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // JWT Validation
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token and retrieve username
                $usernameFromToken = JWTValidation($token, false, false, false, false, true);
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

    // Capture POST request body
    $requestBody = file_get_contents('php://input');

    // Check if request body exists
    if ($requestBody == null) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $appId = null;
    $status = null;
    $reason = null;
    $comment = null;

    try {
        // Capture required attributes
        $appId = load_attribute($requestBody, "applicationId", true);
        $status = load_attribute($requestBody, "status", true);

        // Capture optional attributes
        $reason = load_attribute($requestBody, "reason");
        $comment = load_attribute($requestBody, "comment");

    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    try {
        $count = 0;
        $logId = null;

        // Prepare SQL and bind parameters for inserting log data
        $sql = "CALL create_log_new(:app_id, :status, :comment, :username);";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':app_id', $appId);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':username', $usernameFromToken);
        $stmt->execute();
        $logId = $stmt->fetchColumn();
        $count += $stmt->rowCount();

        // Insert reasons if available for the log
        if ($logId != null) {
            foreach ($reason as $key) {
                $sql = "INSERT INTO `log_reason`(`log_id`, `reason_id`) VALUES (:log_id, :reason_id);";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':log_id', $logId);
                $stmt->bindParam(':reason_id', $key);
                $stmt->execute();
                $count += $stmt->rowCount();
            }
        }

        // Check the success of database insertion
        if ($count > 0) {
            header("Status: 200 Application Created ($count items)", true, 200);
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
