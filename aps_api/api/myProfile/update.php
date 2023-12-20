<?php
/**
 * Handles updating user profile data based on a PUT request.
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/myProfile/update.php?token=JWT_TOKEN&id=USER_ID
 *
 * Request Body sample:
 * {
 *     "email":"jd@email.com",
 *     "firstname":"John",
 *     "lastname":"Doe"
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
    $userFirstName = null;
    $userEmail = null;
    $userLastName = null;
    $userId = null;

    try {
        // Capture Required attributes
        $userEmail = load_attribute($requestBody, "email", false);
        $userFirstName = load_attribute($requestBody, "firstname", false);
        $userLastName = load_attribute($requestBody, "lastname", false);
        $userId = $_GET["id"];

        // Enter data to database
        $count = 0;
        $sql = "UPDATE `user` SET `usr_fname`=:usr_fname,`usr_lname`=:usr_lname,`usr_email`=:usr_email WHERE `usr_id`=:usr_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usr_fname', $userFirstName);
        $stmt->bindParam(':usr_lname', $userLastName);
        $stmt->bindParam(':usr_email', $userEmail);
        $stmt->bindParam(':usr_id', $userId);
        $stmt->execute();
        $count += $stmt->rowCount();

        if ($count > 0) {
            header("Status: 200 Data Updated ($count items)", true, 200);
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
