<?php
/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/user/resetPassword.php?token=JWT_TOKEN&id=USER_ID
 *
 * Request Body sample
 * N/A
 */

// includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    // Load INI file
    $ini = parse_ini_file("../../conf/conf.ini");

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // params of JWTValidation(token, admin, view, delete, create, update)
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

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Request Parameter existence check
    if (!(isset($_GET["id"]))) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request parameter id does not exist.", false, 400);
        exit;
    }

    // define data attributes
    $userId = null;
    $new_password = $ini["PASSWORD"];

    try {
        // Capture Required attributes
        $userId = $_GET["id"];

    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    // Enter data to database
    try {
        $sql = "UPDATE `user` SET `usr_password`=password(:new_password) WHERE `usr_id`=:userId AND `usr_deleted`=0;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':new_password', $new_password);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count > 0) {
            header("Status: 200 Password Reset Success.", true, 200);
            exit;
        } else {
            header("Status: 500 Internal Server Error", false, 500);
            header("Error: Database Update error.", false, 500);
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
