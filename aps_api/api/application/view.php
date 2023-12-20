<?php

/**
 * API Endpoint: `/aps_api/api/application/view.php`
 * Request URI format: `http://IP_ADDRESS:PORT_NUMBER/aps_api/api/application/view.php?token=JWT_TOKEN&id=APPLICATION_ID&hideCompleated=true`
 * Application id is optional.
 * Request Body sample: N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

// CORS headers and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // JWT Token Validation
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate JWT token for view permission
                JWTValidation($token, false, true, false, false, false);
            } catch (Exception $e) {
                returnErrorResponse(400, "Authentication Failed");
            }
        } else {
            throw new Exception("Error: Authentication Token Required.");
        }
    } catch (Exception $e) {
        throw new Exception("Error: Token might be timeout.");
    }

    // Define data attributes
    $returnBody = array();
    $application_id = null;
    $pageSize = null;
    $pageNumber = null;
    $hideApproved = null;
    $searchKey = null;
    $first_rec_num = null;

    // Request Parameter existence check
    if (isset($_GET["id"])) {
        $application_id = $_GET["id"];
    }
    if (isset($_GET["pageSize"])) {
        $pageSize = $_GET["pageSize"];
    }
    if (isset($_GET["pageNumber"])) {
        $pageNumber = $_GET["pageNumber"];
    }
    if (isset($_GET["hideApproved"])) {
        $hideApproved = ($_GET['hideApproved'] == "true") ? 2 : 0;
    }
    if (isset($_GET["searchKey"])) {
        $searchKey = $_GET["searchKey"];
    }

    // Pre-process Params
    $searchKey = "%$searchKey%";
    $first_rec_num = $pageNumber * $pageSize;

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    // Select data from the database
    try {
        $sql = null;
        if ($application_id == null) {
            // Query for pagination and search
            $sql = "CALL search_with_paging(:compleated, :first_record_num, :num_of_res_per_pg, :keyword)";
        } else {
            // Query for specific application view
            $sql = "CALL view_application(:app_id)";
        }
        $stmt = $conn->prepare($sql);
        if ($application_id == null) {
            // Bind parameters for pagination and search
            $stmt->bindParam(':compleated', $hideApproved);
            $stmt->bindParam(':first_record_num', $first_rec_num);
            $stmt->bindParam(':num_of_res_per_pg', $pageSize);
            $stmt->bindParam(':keyword', $searchKey);
        } else {
            // Bind parameters for specific application view
            $stmt->bindParam(':app_id', $application_id);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            // Process fetched data
            $returnBody = processData($result, $application_id);
        }

        // Send response
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

// Function to process fetched data
function processData($result, $application_id)
{
    $returnBody = array();
    $next_page_count = 0;

    foreach ($result as $row) {
        // Process each row data
        $returnItem = array();
        $returnItem["id"] = $row["app_id"];
        // Populate other attributes here...

        if ($application_id != null) {
            // Additional processing for specific application view
            $reasonSet = array(); // Process reason set here...
            // Populate other details for specific view...

            $returnItem["reason"] = $reasonSet;
            // Populate additional details...
        } else {
            $next_page_count = $row["items_for_next"];
        }

        array_push($returnBody, $returnItem);
    }

    $returnBody["result"] = $returnBody;
    $returnBody["next_page_count"] = $next_page_count;

    return $returnBody;
}

// Function to return error response with appropriate status code and message
function returnErrorResponse($statusCode, $errorMessage)
{
    header("Status: $statusCode", false, $statusCode);
    header("Error: $errorMessage", false, $statusCode);
    exit;
}
?>
