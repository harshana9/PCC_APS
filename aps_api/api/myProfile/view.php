<?php
/**
 * Handles viewing user profiles based on a GET request.
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/myProfile/view.php?token=JWT_TOKEN&id=USER_ID
 *
 * User id is optional.
 * Request Body sample: N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers to allow access and define allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token
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
    $user_id = null;

    // Request Parameter existence check
    if (isset($_GET["id"])) {
        $user_id = $_GET["id"];
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Select data from database
    try {
        if ($user_id != null) {
            $sql = "SELECT * FROM `user` WHERE `usr_username`=:usr_id;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usr_id', $user_id);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                foreach ($result as $row) {
                    $returnItem = array(
                        "id" => $row["usr_id"],
                        "username" => $row["usr_username"],
                        "firstname" => $row["usr_fname"],
                        "lastname" => $row["usr_lname"],
                        "email" => $row["usr_email"],
                        "admin" => $row["usr_admin"],
                        "view" => $row["usr_view"],
                        "insert" => $row["usr_create"],
                        "update" => $row["usr_update"],
                        "delete" => $row["usr_delete"]
                    );
                    array_push($returnBody, $returnItem);
                }
            }

            header("Status: 200 Request Fulfilled.", true, 200);
            echo json_encode($returnBody);
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
