<?php

/**
 * View Product Module
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/product/view.php?token=JWT_TOKEN&id=PRODUCT_ID
 *
 * Product id is optional
 *
 * Request Body sample:
 * N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // JwtValidation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Params of JWTValidation(token, admin, view, delete, create, update)
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
    $product_id = null;

    // Request Parameter existence check
    if (isset($_GET["id"])) {
        $product_id = $_GET["id"];
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Select data from the database
    try {
        // Prepare SQL and bind parameters
        $sql = null;
        if ($product_id == null) {
            $product_id = 0;
            $sql = "SELECT * FROM `product` WHERE `prod_deleted`=0 AND `prod_id`>:prod_id;";
        } else {
            $sql = "SELECT * FROM `product` WHERE `prod_id`=:prod_id;";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':prod_id', $product_id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            $returnItem = array();
            foreach ($result as $row) {
                $returnItem["id"] = $row["prod_id"];
                $returnItem["product"] = $row["prod_name"];
                $returnItem["description"] = $row["prod_description"];
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
