<?php
/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/user/permissionUpdate.php?token=JWT_TOKEN&id=USER_ID
 *
 * Request Body sample
 * {
 *     "admin":true,
 *     "update":true,
 *     "view":false,
 *     "delete"true,
 *     "insert"true
 * }
 * *All params are optional
 */

// includes
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

    // Take post request body
    $requestBody = file_get_contents('php://input');

    // Request body existence check
    if ($requestBody == null) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist.", false, 400);
        exit;
    }

    // Request Parameter existence check
    if (!(isset($_GET["id"]))) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request parameter id does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $userId = null;
    $admin = null;
    $insert = null;
    $view = null;
    $update = null;
    $delete = null;

    try {
        // Capture Required attributes
        $userId = $_GET["id"];

        // Capture optional attributes
        $admin = load_attribute($requestBody, "admin");
        $insert = load_attribute($requestBody, "insert");
        $view = load_attribute($requestBody, "view");
        $update = load_attribute($requestBody, "update");
        $delete = load_attribute($requestBody, "delete");
    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    // Enter data to database
    try {
        $count = 0;
        if ($admin != null) {
            $sql = "UPDATE `user` SET `usr_admin`=:usr_admin WHERE `usr_id`=:userId AND `usr_deleted`=0;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usr_admin', $admin);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $count += $stmt->rowCount();
        }
        if ($insert != null) {
            $sql = "UPDATE `user` SET `usr_create`=:usr_create WHERE `usr_id`=:userId AND `usr_deleted`=0;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usr_create', $insert);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $count += $stmt->rowCount();
        }
        if ($update != null) {
            $sql = "UPDATE `user` SET `usr_update`=:usr_update WHERE `usr_id`=:userId AND `usr_deleted`=0;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usr_update', $update);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $count += $stmt->rowCount();
        }
        if ($view != null) {
            $sql = "UPDATE `user` SET `usr_view`=:usr_view WHERE `usr_id`=:userId AND `usr_deleted`=0;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usr_view', $view);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $count += $stmt->rowCount();
        }
        if ($delete != null) {
            $sql = "UPDATE `user` SET `usr_delete`=:usr_delete WHERE `usr_id`=:userId AND `usr_deleted`=0;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usr_delete', $delete);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $count += $stmt->rowCount();
        }

        if ($count > 0) {
            header("Status: 200 Check List Updated ($count items)", true, 200);
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
