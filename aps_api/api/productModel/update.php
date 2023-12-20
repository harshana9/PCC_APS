<?php
/**
 * Updates a product model in the database.
 * 
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/productModel/update.php?token=JWT_TOKEN&id=PRODUCT_ID
 *
 * Request Body sample
 * {
 *     "name":"EPIC 520C",
 *     "contact":"0111235678"
 * }
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

// Handling PUT requests
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    // JWT Token Validation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token for necessary permissions
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

    // Check existence of request parameter
    if (!(isset($_GET["id"]))) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request parameter id does not exist.", false, 400);
        exit;
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Fetch PUT request body
    $requestBody = file_get_contents('php://input');

    // Check for request body existence
    if ($requestBody == null) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist.", false, 400);
        exit;
    }

    // Define data attributes
    $prod_model_id = null;
    $prod_model_name = null;
    $prod_model_contact = null;

    try {
        // Capture and validate required attributes from the request body
        $prod_model_contact = load_attribute($requestBody, "contact", true);
        $prod_model_name = load_attribute($requestBody, "name", true);

        $prod_model_id = $_GET["id"];

        // Capture optional attributes (not applicable here)
    } catch (Exception $e) {
        $msg = $e->getMessage();
        header("Status: 400 Bad Request", false, 400);
        header("Error: $msg", false, 400);
        exit;
    }

    // Update data in the database
    try {
        // Prepare SQL and bind parameters for updating the product model
        $count = 0;
        $sql = "UPDATE `product_model` SET `prod_model_name`=:prod_model_name,`prod_model_contact`=:prod_model_contact WHERE `prod_model_id`=:prod_model_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':prod_model_name', $prod_model_name);
        $stmt->bindParam(':prod_model_contact', $prod_model_contact);
        $stmt->bindParam(':prod_model_id', $prod_model_id);
        $stmt->execute();
        $count += $stmt->rowCount();

        // Check for successful data update
        if ($count > 0) {
            header("Status: 200 Data Updated.($count items)", true, 200);
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