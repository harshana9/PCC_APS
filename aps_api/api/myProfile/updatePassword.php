<?php
/**
 * Handles updating a user's password based on a PUT request.
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/myProfile/updatePassword.php?token=JWT_TOKEN&id=USER_ID
 *
 * Request Body sample:
 * {
 *     "oldPassword":"123",
 *     "newPassword":"12345"
 * }
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers to allow access and define allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token
                JWTValidation($token, false, false, false, false, false);
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
    if (!isset($_GET["id"])) {
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
    $oldPassword = null;
    $newPassword = null;
    $userId = null;

    try {
        // Capture Required attributes
        $oldPassword = load_attribute($requestBody, "oldPassword", true);
        $newPassword = load_attribute($requestBody, "newPassword", true);
        $userId = $_GET["id"];

        // Enter data to database
        $count = 0;
        $sql = "UPDATE `user` SET `usr_password`=PASSWORD(:new_password) WHERE `usr_id`=:usr_id AND `usr_password`=PASSWORD(:old_password);";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':new_password', $newPassword);
        $stmt->bindParam(':old_password', $oldPassword);
        $stmt->bindParam(':usr_id', $userId);
        $stmt->execute();
        $count += $stmt->rowCount();

        if ($count > 0) {
            header("Status: 200 Password Updated ($count items)", true, 200);
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