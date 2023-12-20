<?php
/**
 * Handles deletion of a specific merchant type from the database based on DELETE requests.
 *
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/merchantType/delete.php?token=JWT_TOKEN&id=ITEM_ID
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers to allow access and define allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token for delete permission
                JWTValidation($token, true, false, true, false, false);
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
        header("Error: Request parameter 'id' does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $id = null;

    // Fetch data from GET parameter
    try {
        // Capture Required attribute
        $id = $_GET["id"];

        if ($id == null or $id == "") {
            header("Status: 400 Bad Request", false, 400);
            header("Error: Request argument for 'id' does not exist.", false, 400);
            exit;
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    // Delete data from the database
    try {
        // Prepare SQL and bind parameters
        $sql = "UPDATE `business_type` SET `bust_deleted`=1 WHERE `bust_id`=:bust_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':bust_id', $id);
        $stmt->execute();
        $affected = $stmt->rowCount();

        if ($affected == 1) {
            header("Status: 200 One item Deleted", true, 200);
            exit;
        } elseif ($affected > 1) {
            header("Status: 500 Internal Server Error", false, 500);
            header("Error: Critical Error! Multiple Rows deleted. Please ask for developer support", false, 500);
            exit;
        } else {
            header("Status: 500 Internal Server Error", false, 500);
            header("Error: Database Delete error.", false, 500);
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
