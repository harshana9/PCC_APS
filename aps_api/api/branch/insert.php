<?php
/**
 * Inserts branch data into the 'branch' table in the database based on the provided JSON array.
 * 
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/branch/insert.php?token=JWT_TOKEN
 * 
 * Request Body sample:
 * [
 *     {
 *         "name":"Horana",
 *         "code":"041",
 *         "email":"horana@peoplesbank.lk",
 *         "zone":12
 *     },
 *     {
 *         "name":"Colombo City",
 *         "code":"066",
 *         "zone":12
 *     }
 * ]
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Validate HTTP request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // JWT Token Validation
    try {
        if(isset($_GET['token'])){
            $jwtToken = trim($_GET['token']);
            try {
                // Validate JWT token for required permissions
                JWTValidation($jwtToken, true, false, false, true, false);
            } catch (Exception $e) {
                // Handle authentication failure
                handleBadRequest(400, "Authentication Failed");
            }
        } else {
            // Token not provided in the request
            handleBadRequest(400, "Authentication Token Required.");
        }
    } catch (Exception $e) {
        // Token might have expired
        handleBadRequest(400, "Token might be expired.");
    }

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Take post request body
    $requestBody = file_get_contents('php://input');
    $requestBody = json_decode($requestBody, true);

    // Request body existence check
    if(empty($requestBody)){
        handleBadRequest(400, "Request body does not exist.");
    }

    $dataset = array();

    foreach ($requestBody as $item) {
        $dataItem = array();
        try {
            // Capture Required attributes
            $dataItem["name"] = loadAttribute($item, "name", true, true);
            $dataItem["code"] = loadAttribute($item, "code", true, true);
            $dataItem["zone"] = loadAttribute($item, "zone", true, true);

            // Capture optional attributes
            $dataItem["email"] = loadAttribute($item, "email", false, true);
            array_push($dataset, $dataItem);
        } catch(Exception $e) {
            $msg = $e->getMessage();
            handleBadRequest(400, $msg);
        }
    }

    // Enter data into the database
    try {
        $count = 0;

        // Prepare SQL and bind parameters
        foreach ($dataset as $item) {
            $sql = "INSERT INTO `branch`(`branch_name`, `branch_code`, `branch_email`, `branch_zone`) VALUES (:branch_name, :branch_code, :branch_email, :branch_zone);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':branch_name', $item["name"]);
            $stmt->bindParam(':branch_code', $item["code"]);
            $stmt->bindParam(':branch_email', $item["email"]);
            $stmt->bindParam(':branch_zone', $item["zone"]);
            $stmt->execute();
            $count += $stmt->rowCount();
        }

        if($count > 0){
            handleSuccess(200, "Data Inserted ($count items)");
        } else {
            handleServerError(500, "Database insert error.");
        }
    } catch(PDOException $e) {
        handleServerError(500, $e->getMessage());
    }
} else {
    handleBadRequest(400, "Invalid request method");
}

/**
 * Handle Bad Request and set appropriate HTTP response code and error message.
 *
 * @param int $statusCode HTTP status code
 * @param string $errorMessage Error message to display
 */
function handleBadRequest($statusCode, $errorMessage) {
    header("Status: $statusCode Bad Request", false, $statusCode);
    header("Error: $errorMessage", false, $statusCode);
    exit;
}

/**
 * Handle Server Error and set appropriate HTTP response code and error message.
 *
 * @param int $statusCode HTTP status code
 * @param string $errorMessage Error message to display
 */
function handleServerError($statusCode, $errorMessage) {
    header("Status: $statusCode Internal Server Error", false, $statusCode);
    header("Error: $errorMessage", false, $statusCode);
    exit;
}

/**
 * Handle Success and set appropriate HTTP response code and success message.
 *
 * @param int $statusCode HTTP status code
 * @param string $successMessage Success message to display
 */
function handleSuccess($statusCode, $successMessage) {
    header("Status: $statusCode $successMessage", true, $statusCode);
    exit;
}

/**
 * Load attribute from an array and perform validation.
 *
 * @param array $item The array containing the attributes
 * @param string $attributeName The name of the attribute to load
 * @param bool $isRequired Indicates if the attribute is required
 * @param bool $isString Indicates if the attribute must be a non-empty string
 * @return mixed The loaded attribute value
 * @throws Exception If attribute is missing or doesn't meet validation criteria
 */
function loadAttribute($item, $attributeName, $isRequired, $isString) {
    if (!isset($item[$attributeName]) && $isRequired) {
        throw new Exception("Request argument for $attributeName does not exist.");
    }

    $attributeValue = $item[$attributeName];

    if ($isRequired && $isString && (empty($attributeValue) || !is_string($attributeValue))) {
        throw new Exception("Invalid or missing value for $attributeName.");
    }

    return $attributeValue;
}
?>
