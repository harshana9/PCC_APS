<?php
/**
 * Product Model View API Endpoint
 *
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/productModel/view.php?token=JWT_TOKEN&id=PRODUCT_MODEL_ID
 *
 * Product model id is optional
 *
 * Request Body sample: N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // JWT Validation
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
    $returnBody = [];
    $prod_model_id = isset($_GET["id"]) ? $_GET["id"] : null;

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Select data from database
    try {
        // Prepare SQL and bind parameters
        $sql = ($prod_model_id == null)
            ? "SELECT * FROM `product_model` WHERE `prod_model_deleted`=0 AND `prod_model_id`>:prod_model_id ORDER BY `prod_model_name`;"
            : "SELECT * FROM `product_model` WHERE `prod_model_id`=:prod_model_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':prod_model_id', $prod_model_id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $returnItem = [
                    "id" => $row["prod_model_id"],
                    "name" => $row["prod_model_name"],
                    "contact" => $row["prod_model_contact"]
                ];
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
