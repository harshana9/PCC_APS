<?php

/**
 * Handles POST request to insert user data.
 *
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/user/insert.php?token=JWT_TOKEN
 *
 * Request Body sample
 * {
 *    "username":"jhon",
 *    "firstname":"Jhon",
 *    "password":"123",
 *    "lastname":"Doe",
 *    "email":"jhon@email.com",
 *    "admin":true,
 *    "create":true,
 *    "update":true,
 *    "view":true,
 *    "delete":true
 * }
 *
 * Only the first 3 params are required
 */

// includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // JWT Validation
        if (!isset($_GET['token'])) {
            throw new Exception("Error: Authentication Token Required.");
        }

        $token = trim($_GET['token']);
        // Validate JWT parameters (token, admin, view, delete, create, update)
        JWTValidation($token, true, false, false, true, false);
    } catch (Exception $e) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: " . $e->getMessage(), false, 400);
        exit;
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Take post request body
    $requestBody = file_get_contents('php://input');

    // Request body existence check
    if ($requestBody === false || empty($requestBody)) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $usr_username = null;
    $usr_fname = null;
    $usr_lname = null;
    $usr_email = null;
    $usr_admin = false;
    $usr_create = false;
    $usr_update = false;
    $usr_view = false;
    $usr_delete = false;
    $usr_password = null;

    // Take data from HTTP request body
    try {
        // Capture Required attributes
        $usr_username = load_attribute($requestBody, "username", true);
        $usr_fname = load_attribute($requestBody, "firstname", true);
        $usr_password = load_attribute($requestBody, "password", true);

        // Capture optional attributes
        $usr_lname = load_attribute($requestBody, "lastname");
        $usr_email = load_attribute($requestBody, "email");
        $usr_admin = load_attribute($requestBody, "admin");
        $usr_create = load_attribute($requestBody, "create");
        $usr_update = load_attribute($requestBody, "update");
        $usr_view = load_attribute($requestBody, "view");
        $usr_delete = load_attribute($requestBody, "delete");
    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    // Enter data into the database
    try {
        // prepare SQL and bind parameters
        $sql = "INSERT INTO `user`(`usr_username`, `usr_fname`, `usr_lname`, `usr_email`, `usr_admin`, `usr_create`, `usr_update`, `usr_view`, `usr_delete`, `usr_password`) 
                VALUES (:usr_username, :usr_fname, :usr_lname, :usr_email, :usr_admin, :usr_create, :usr_update, :usr_view, :usr_delete, password(:usr_password));";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usr_username', $usr_username);
        $stmt->bindParam(':usr_fname', $usr_fname);
        $stmt->bindParam(':usr_lname', $usr_lname);
        $stmt->bindParam(':usr_email', $usr_email);
        $stmt->bindParam(':usr_admin', $usr_admin);
        $stmt->bindParam(':usr_create', $usr_create);
        $stmt->bindParam(':usr_update', $usr_update);
        $stmt->bindParam(':usr_view', $usr_view);
        $stmt->bindParam(':usr_delete', $usr_delete);
        $stmt->bindParam(':usr_password', $usr_password);
        $stmt->execute();
        $id = $conn->lastInsertId();

        if ($id > 0) {
            header("Status: 200 Data Inserted", true, 200);
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
?>
