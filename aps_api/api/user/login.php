<?php
/**
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/user/login.php
 *
 * Request Body sample
 * {
 *     "username":"user",
 *     "password":"123"
 * }
 * *All params are optional
 */

// includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require "../../request/php-jwt/JwtHandler.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Database Connection
        $dbCon = new DbCon();
        $conn = $dbCon->getConn();

        // Take post request body
        $requestBody = file_get_contents('php://input');

        // Request body existence check
        if ($requestBody == null) {
            header("Status: 400 Bad Request", false, 400);
            header("Error: Request body does not exist.", false, 400);
            exit;
        }

        // Define data attributes
        $usr_username = null;
        $usr_password = null;

        $returnBody = array("username" => null, "admin" => false, "view" => false, "create" => false, "update" => false, "delete" => false);

        // Take data from HTTP request body
        try {
            // Capture Required attributes
            $usr_username = load_attribute($requestBody, "username", true);
            $usr_password = load_attribute($requestBody, "password", true);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            header("Status: 400 Bad Request", false, 400);
            header("Error: $msg", false, 400);
            exit;
        }

        // Select data from the database
        try {
            // prepare SQL and bind parameters
            $sql = "SELECT * FROM `user` WHERE `usr_username`=:usr_username AND `usr_password`=password(:usr_password) AND `usr_deleted`=0;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usr_username', $usr_username);
            $stmt->bindParam(':usr_password', $usr_password);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) == 1) {
                foreach ($result as $row) {
                    $returnBody["username"] = $row["usr_username"];
                    if ($row["usr_view"] == 1) {
                        $returnBody["view"] = true;
                    }
                    if ($row["usr_admin"] == 1) {
                        $returnBody["admin"] = true;
                    }
                    if ($row["usr_create"] == 1) {
                        $returnBody["create"] = true;
                    }
                    if ($row["usr_update"] == 1) {
                        $returnBody["update"] = true;
                    }
                    if ($row["usr_delete"] == 1) {
                        $returnBody["delete"] = true;
                    }
                }
            }

            if ($returnBody["username"] != null) {
                $ini = parse_ini_file("./../../conf/conf.ini");
                $jwt = new JwtHandler();

                $token = $jwt->jwtEncodeData(
                    $ini["jwt_token_path"],
                    $returnBody
                );

                $returnBody["token"] = $token;

                header("Status: 200 Login Success", true, 200);
                echo  json_encode($returnBody);
                exit;
            } else {
                header("Status: 401 Unauthorized", false, 401);
                exit;
            }
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            header("Status: 500 Internal Server Error", false, 500);
            header("Error: $msg", false, 500);
            exit;
        }
    } catch (Exception $e) {
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
