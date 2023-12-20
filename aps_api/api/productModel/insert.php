<?php
/**
 * Inserts product models into the database.
 * 
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/productModel/insert.php?token=JWT_TOKEN
 *
 * Request Body sample
 * [
 *     {
 *         "name":"EPIC 520C",
 *         "contact":"0111235678"
 *     }
 * ]
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // JWT Token Validation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token for necessary permissions
                JWTValidation($token, true, false, false, true, false);
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

    // Fetch POST request body
    $requestBody = file_get_contents('php://input');
    $requestBody = load_attribute_set($requestBody);

    // Check for request body existence
    if ($requestBody == null) {
        header("Status: 400 Bad Request", false, 400);
        header("Error: Request body does not exist.", false, 400);
        exit;
    }

    // Initialize variables
    $dataset = [];

    // Loop through each item in the request body
    foreach ($requestBody as $item) {
        $dataItem = [];
        try {
            // Capture and validate required attributes for each item
            $dataItem["name"] = load_attribute($item, "name", true, true);
            $dataItem["contact"] = load_attribute($item, "contact", true, true);
            
            // Add valid data item to the dataset
            array_push($dataset, $dataItem);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            header("Status: 400 Bad Request", false, 400);
            header("Error: $msg", false, 400);
            exit;
        }
    }

    // Insert data into the database
    try {
        $count = 0;
        // Prepare SQL and bind parameters for each item in the dataset
        foreach ($dataset as $item) {
            $sql = "INSERT INTO `product_model`(`prod_model_name`, `prod_model_contact`) VALUES (:prod_model_name,:prod_contact);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':prod_model_name', $item["name"]);
            $stmt->bindParam(':prod_contact', $item["contact"]);
            $stmt->execute();
            $count += $stmt->rowCount();
        }

        // Check for successful data insertion
        if ($count > 0) {
            header("Status: 200 Data Inserted($count items)", true, 200);
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
