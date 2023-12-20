<?php
/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/pdf/report/stsReport.php?token=JWT_TOKEN&status=STATUS_ID
 */

// Include necessary files
require_once "StatusReport.php";
require_once "../../request/jwtVerify.php";

// Set headers for allowing access and specifying allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Verify JWT Token
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // Validate token for view permissions
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

    $statusReport;

    // Define column set for the report
    $columnSet = [
        ["app_id", "Ap.ID", 15],
        ["sts_name", "Status", 20],
        ["app_merchant_id", "MID", 44],
        ["bust_name", "M Type", 25],
        ["prod_name", "Prod.", 18],
        ["app_date", "App.Date", 25],
        ["branch_name", "Branch", 33]
    ];

    // Create StatusReport Object based on status ID or as a default report
    if (isset($_GET["status"])) {
        $statusReport = ($_GET["status"] == "null") ? new StatusReport(null, $columnSet) : new StatusReport($_GET["status"], $columnSet);
    } else {
        $statusReport = new StatusReport();
    }

    // Generate PDF report
    $statusReport->generate();
}
?>