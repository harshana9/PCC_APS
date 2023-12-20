<?php
require_once "../../database/dbcon.php";

//Databse Connection
$dbCon = new DbCon();
$conn = $dbCon->getConn();

$sql = "SELECT * FROM `branch`;";
$stmt = $conn->prepare($sql);
//$stmt->bindParam(':token', $token);
$stmt->execute();
$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
if(count($result)>0){
    foreach($result as $row) {
        $sql2 = "SELECT * FROM `zone` WHERE `zone_name`='".$row["branch_zone"]."';";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute();
        $result2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        if(count($result2)>0){
            foreach($result2 as $row2) {
                $sql3 = "UPDATE `branch` SET `branch_zone`=".$row2["zone_id"]." WHERE `branch_zone`='".$row2["zone_name"]."';";
                echo $sql3;
            }
        }
    }
}

?>