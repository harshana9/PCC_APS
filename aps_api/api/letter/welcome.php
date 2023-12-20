<?php

require_once('../../letters/TCPDF/examples/pos_welcome.php');

?>

<?php

/**
 * 
 * Request URI format
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/letter/generate.php?token=JWT_TOKEN&appId=APPLICATION_ID&genId=APPLICATION_ID
 * 
 * 
 * Request Body sample
 * N/A
 * 
 * 
*/

//includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    //JwtValidation
    try{
        if(isset($_GET['token'])){

            $token = trim($_GET['token']);
            try{
                //params of JWTValidation(token, damin, view, delete, create, update)
                JWTValidation($token,false, true, false, false, false);
            }
            catch(Exception $e){
                //echo $e;
                header("Status: 400 Bad Request",false,400);
                header("Authentication Failed" ,false,400);
                exit; 
            }
        }
        else{
            throw new Exception("Error: Authentication Token Required.");
        }
    }
    catch(Exception $e){
        throw new Exception("Error: Token might be timeout.");
    }

    //define data atrributes
    $returnBody=array();
    $val_app_id=null;
    $gen_app_id=null;

    //Request Patameter existance check
    if(isset($_GET["valId"])){
        $val_app_id=$_GET["valId"];
    }



    if(isset($_GET["genId"])){
        $gen_app_id=$_GET["genId"];
    }

    //Databse Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();

    //Select data from database
    try{

    	if($val_app_id!=null || $gen_app_id!=null){
    		$prod_name=null;

    		$sql="CALL view_application(:app_id);";
    		$stmt = $conn->prepare($sql);
	        $stmt->bindParam(':app_id', $val_app_id);
	        $stmt->execute();
	        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);


	        $mid=null;
			$rate=null;
			$monthlyvolume="300,000";
			$fee="2500.00";
			$contact=null;
			$date=date('Y-m-d');
	        
	        if(count($result)>0){
	            $returnItem=array();
	            foreach($result as $row) {
	            	$prod_name=$row["prod_name"];
	            	$mid=$row["app_merchant_id"];
					$rate=$row["app_fee_rate"];
					$contact=$row["prod_model_contact"];
	            }
	        }

	        if($mid==null || $rate==null || $monthlyvolume==null || $fee==null || $contact==null || $date==null){
			        echo  json_encode(array("msg"=>"Application Data is not compleate."));
			}
			elseif($mid=="" || $rate=="" || $monthlyvolume=="" || $fee=="" || $contact=="" || $date==""){
				echo  json_encode(array("msg"=>"Application Data is not compleate."));
			}
			else{
				if($prod_name=="POS"){
		        	echo  json_encode(array("msg"=>"OK"));
		        }
		        else{
		        	echo  json_encode(array("msg"=>"This Product Does not Configured with letter"));
		        }
			}
    	}
    	if ($gen_app_id!=null){
    		//echo "-------";
    		$prod_name=null;
    		$sql="SELECT `product`.`prod_name` FROM `application` LEFT JOIN `product` ON `application`.`app_product_id`=`product`.`prod_id` WHERE `application`.`app_id`=:app_id";
    		$stmt = $conn->prepare($sql);
	        $stmt->bindParam(':app_id', $gen_app_id);
	        $stmt->execute();
	        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	        
	        if(count($result)>0){
	            $returnItem=array();
	            foreach($result as $row) {
	            	$prod_name=$row["prod_name"];
	            }
	        }

	        switch ($prod_name) {
	        	case "POS":
	        		//echo  json_encode(array("msg"=>"OK"));
	        		$mid=null;
					$rate=null;
					$monthlyvolume="300,000";
					$fee="2500.00";
					$contact=null;
					$date=date('Y-m-d');
					$address=null;
					$branch_name=null;

					$sql="CALL view_application(:app_id);";
		    		$stmt = $conn->prepare($sql);
			        $stmt->bindParam(':app_id', $gen_app_id);
			        $stmt->execute();
			        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			        
			        if(count($result)>0){
			            $returnItem=array();
			            foreach($result as $row) {
			            	$mid=$row["app_merchant_id"];
							$rate=$row["app_fee_rate"];
							$contact=$row["prod_model_contact"];
							$address=$row["app_address"];
							$branch_name=$row["branch_name"];
			            }
			        }

			        generate_pos_welcome_letter($mid, $rate, $monthlyvolume, $fee, $contact, $date, $address,"D",null);

	        		break;
	        	
	        	default:
	        		//echo  json_encode(array("msg"=>"This Product Does not Configured with letter"));
	        		break;
	        }    		
    	}
        
        header("Status: 200 Request Fulfilled.",true,200);
        //echo  json_encode($returnBody);
        exit;
    }
    catch(PDOException $e){
        $msg = $e->getMessage();
        header("Status: 500 Internal Server Error",false,500);
        header("Error: $msg",false,500);
        exit;
    }
}
else{
    header("Status: 400 Bad Request",false,400);
    header("Error: Invalid request method",false,400);
    exit;    
}
?>