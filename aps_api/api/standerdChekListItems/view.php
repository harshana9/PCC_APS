<?php

/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/standerdChekListItems/view.php?token=JWT_TOKEN
 *
 * Request Body sample
 * N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // JWT Validation
    try {
        // Validate JWT Token
        if (!isset($_GET['token'])) {
            throw new Exception("Error: Authentication Token Required.");
        }

        $token = trim($_GET['token']);

        // Validate token for admin view permissions
        JWTValidation($token, true, true, false, false, false);
    } catch (Exception $e) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Authentication Failed", false, 400);
        exit;
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Define data attributes
    $returnBody = array();

    // Retrieve data from the database
    try {
        $sql = "SELECT * FROM `standerd_cheklist` WHERE `chk_deleted`=0;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $returnItem = array();
                $returnItem["id"] = $row["chk_id"];
                $returnItem["label"] = $row["chk_show_name"];
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
?>
