<?php

/**
 * View Checklist Items for a Product Module
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/product/viewChecklistItems.php?token=JWT_TOKEN&productId=PRODUCT_ID&businessType=BUSINESS_TYPE
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
                JWTValidation($token, true, true, false, false, false);
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
    $business_type = null;

    // Request Parameter existence check
    if (isset($_GET["productId"])) {
        $product_id = $_GET["productId"];
    }
    if (isset($_GET["businessType"])) {
        $business_type = $_GET["businessType"];
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Select data from the database
    try {
        // Prepare SQL and bind parameters
        $sql = "SELECT `chk_id`,`chk_show_name`,`business_type` 
                FROM `standerd_cheklist` 
                LEFT JOIN `product_check_list` ON `product_check_list`.`cheklist_item_id`=`standerd_cheklist`.`chk_id` 
                WHERE `product_check_list`.`product_id`=:product_id AND `product_check_list`.`business_type`=:business_type AND `product_check_list`.`deleted`=0;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':business_type', $business_type);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            $returnItem = array();
            foreach ($result as $row) {
                $returnItem["id"] = $row["chk_id"];
                $returnItem["label"] = $row["chk_show_name"];
                $returnItem["merchantType"] = $row["business_type"];
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
