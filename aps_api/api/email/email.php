<?php
// Endpoint documentation

/**
 * Request URI format:
 * http://IP_ADDRESS:PORT_NUMBER/aps_api/api/email/email.php?token=JWT_TOKEN&id=APPLICATION_ID&type=branch
 * * Product id is optional
 *
 * Request Body sample: N/A
 */

// Includes
require_once "../../database/dbcon.php";
require_once "../../request/paramCapture.php";
require_once "../../request/jwtVerify.php";
require_once "../../mail/sendmail.php";
require_once "../../letters/TCPDF/examples/pos_welcome.php";
require_once "../../letters/TCPDF/examples/pos_ipg_reject.php";

$sendMail = new SendMail();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

$token = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            try {
                // JWTValidation(token, admin, view, delete, create, update)
                JWTValidation($token, false, true, false, false, false);
            } catch (Exception $e) {
                handleError("Authentication Failed", 400);
            }
        } else {
            throw new Exception("Error: Authentication Token Required.");
        }
    } catch (Exception $e) {
        handleError("Token might be timeout.", 400);
    }

    // Data attributes initialization
    $returnBody = array();
    $app_id = isset($_GET["id"]) ? $_GET["id"] : null;
    $type = isset($_GET["type"]) ? $_GET["type"] : null;

    // Database Connection
    $dbCon = new DbCon();
    $conn = $dbCon->getConn();
    
    //Select data from database
    try{
        $title=null;
        $body=null;
        $merchnat_email=null;
        $branch_email=null;
        $attachment=null;


    	if ($app_id!=null && $type!=null){

    		//Get application full details
            $log_data=getAppSts($app_id);

            $app_sts=$log_data[0];
            $log_reasons=$log_data[1];
            $log_comment=$log_data[2];
            $date=date('Y-m-d');
            $m_name=null;
            $mid=null;
            $one_time_fee=null;
            $rate=null;
            $monthlytarget="300,000";
            $paneltyfee="2500.00";
            $technical_support_contact=null;
            $prod_name=null;
            $address=null;
            $branch_name=null;

            $sql="CALL view_application(:app_id);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':app_id', $app_id);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if(count($result)>0){
                $returnItem=array();
                foreach($result as $row) {
                    $mid=$row["app_merchant_id"];
                    $m_name=$row["app_merchant_name"];
                    $one_time_fee=$row["app_one_time_fee"];
                    $merchnat_email=$row["app_contact_email"];
                    $branch_email=$row["branch_email"];
                    $rate=$row["app_fee_rate"];
                    $technical_support_contact=$row["prod_model_contact"];
                    $prod_name=$row["prod_name"];
                    $address=$row["app_address"];
                    $branch_name=$row["branch_name"];
                }
            }

	        if($prod_name=="MPOS" && ($app_sts=="Approved" || $app_sts=="Completed") && $type=="branch"){

		        //Validate application for null data
		        if($mid == "" || $one_time_fee==""){
		        	echo json_encode(array("msg"=>"Application Data is not compleate."));
		        }
		        else{

		        	//Setting up Email
					$title="MPOS LETTER TO INTRO BTANCH";

					$body="<p>Dear Team,</p><p>Please be advised that the following debit entries were made to your Branch's General Remittance Intermediate (GRI) for the sale of MPOS devices on ".$date." and the devices will be dispatched to the merchants promptly upon completion of the necessary formalities.</p><p>Please find below details of MPOS Merchants.</p><table border='1' cellspacing='0' cellpadding='5'><tr><td><b>Merchant Name</b></td><td><b>Merchant ID</b></td><td><b>Debit Amount (Rs)<b></td></tr><tr><td>".$m_name."</td><td>".$mid."</td><td>".$one_time_fee."</td></tr></table><p>If you have any queries or require further information regarding these debits or any related matters, please do not hesitate to reach out to us through 0112490400 and Ext 3. or email merchantdeployments@peoplesbank.lk</p><p>We appreciate your cooperation in promptly settling of your GRI and ensuring accurate records are maintained. Thank you for your continued support.</p><p>Manager-Merchant Acquiring<br/>People's Card Centre<br/>03<sup>rd</sup> Floor, No. 1166, Maradana Road,<br/>Colombo 08, Sri Lanka.</p><img src='cid:sign'/>";
				}
	        }
            elseif(($prod_name=="POS") && ($app_sts=="Approved" || $app_sts=="Completed") && $type=="merchant"){
                $attachment=generate_pos_welcome_letter($mid, $rate, $monthlytarget, $paneltyfee, $technical_support_contact, $date, $address, "S", null);
                $body="<p>Dear Sir,</p><p>Please find welcome letter attached hrerewith.</p><p>Manager-Merchant Acquiring<br/>People's Card Centre<br/>03<sup>rd</sup> Floor, No. 1166, Maradana Road,<br/>Colombo 08, Sri Lanka.</p><img src='cid:sign'/>";
                $title="Welcome to New Merchnatship";
            }
            elseif(($prod_name=="POS") && ($app_sts=="Approved" || $app_sts=="Completed") && $type=="branch"){
                $attachment=generate_pos_welcome_letter($mid, $rate, $monthlytarget, $paneltyfee, $technical_support_contact, $date, $address, "S", null);
                $body="<p>Dear Sir,</p><p>Please find welcome letter of merchnat ".$m_name." (MID:".$mid.") attached hrerewith.</p><p>Manager-Merchant Acquiring<br/>People's Card Centre<br/>03<sup>rd</sup> Floor, No. 1166, Maradana Road,<br/>Colombo 08, Sri Lanka.</p><img src='cid:sign'/>";
                $title="Welcome Letter of the merchant inroduced by your branch";
            }
            elseif(($prod_name=="POS") && ($app_sts=="Approved" || $app_sts=="Completed") && $type=="both"){
                $cc=array("Manager, Peoples bank ".$branch_name." Branch,");
                $attachment=generate_pos_welcome_letter($mid, $rate, $monthlytarget, $paneltyfee, $technical_support_contact, $date, $address, "S", $cc);
                $body="<p>Dear Sir,</p><p>Please find welcome letter attached hrerewith.</p><p>Manager-Merchant Acquiring<br/>People's Card Centre<br/>03<sup>rd</sup> Floor, No. 1166, Maradana Road,<br/>Colombo 08, Sri Lanka.</p><img src='cid:sign'/>";
                $title="Welcome Letter of the merchant";
            }
            elseif(($prod_name=="POS" || $prod_name=="MPOS" || $prod_name=="IPG") && ($app_sts=="Rejected") && ($type=="branch")){
                $attachment=generate_pos_ipg_reject_letter($m_name, $app_id, $log_reasons, $log_comment, $save_type="S");
                $body="<p>Dear Sir,</p><p>Please find rejection letter of merchnat ".$m_name." attached hrerewith.</p><p>Manager-Merchant Acquiring<br/>People's Card Centre<br/>03<sup>rd</sup> Floor, No. 1166, Maradana Road,<br/>Colombo 08, Sri Lanka.</p><img src='cid:sign'/>";
                $title="Rejection of the merchant inroduced by your branch";
            }
            elseif(($prod_name=="POS" || $prod_name=="MPOS" || $prod_name=="IPG") && ($app_sts=="Rejected") && ($type=="merchant")){
                $attachment=generate_pos_ipg_reject_letter($m_name, $app_id, $log_reasons, $log_comment, $save_type="S");
                $body="<p>Dear Sir,</p><p>We are sorry to inform you that your application for ".$prod_name." has been rejected. Please find more infomation in the letter attached hrerewith.<br/>People's Card Centre<br/>03<sup>rd</sup> Floor, No. 1166, Maradana Road,<br/>Colombo 08, Sri Lanka.</p><img src='cid:sign'/>";
                $title="Rejection Letter";
            }
	        else{
	        	echo json_encode(array("msg"=>"This Action not Configured for email."));
	        }

            if($title!=null && $body!=null){
                $send=null;
                $reciver="";

                //Testing email reciver
                /*$test_reciver="mail.harshana.lk@gmail.com";
                $branch_email=$test_reciver;
                $merchnat_email=$test_reciver;*/

                if($type=="branch"){
                    $reciver=$branch_email;
                    $send = $sendMail->send($title, $body, $branch_email, $attachment);
                    $reciver=$branch_email;
                }
                elseif($type=="merchant"){
                    $send = $sendMail->send($title, $body, $merchnat_email, $attachment);
                    $reciver=$merchnat_email;
                }
                elseif($type=="both"){
                    $send = $sendMail->send($title, $body, $branch_email, $attachment);
                    $send = $sendMail->send($title, $body, $merchnat_email, $attachment);
                    $reciver=$merchnat_email.", ".$branch_email;
                }


                if (str_contains($send, "Message has been sent")) {

                    echo json_encode(array("msg"=>"Email Sent to ".$reciver));
                }
                else{
                    echo json_encode(array("msg"=>"Email sending Failed."));
                }
            }
    	}
        
        //header("Status: 200 Request Fulfilled.",true,200);
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











function getAppSts($app_id){

	//DB Con
	$dbCon = new DbCon();
    $conn = $dbCon->getConn();

    //Get Product Status
    $app_sts=null;
    $log_id=null;
    $log_comment=null;
	$sql="SELECT * FROM `application_status_log` LEFT JOIN `standerd_status` ON `application_status_log`.`log_status`=`standerd_status`.`sts_id` LEFT JOIN `application` ON `application_status_log`.`log_app_id`=`application`.`app_id` WHERE `log_app_id`=:app_id ORDER BY `log_datetime` DESC LIMIT 1;";
	$stmt = $conn->prepare($sql);
    $stmt->bindParam(':app_id', $app_id);
    $stmt->execute();
    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    if(count($result)>0){
        $returnItem=array();
        foreach($result as $row) {
        	$app_sts=$row["sts_name"];
            $log_id=$row["log_id"];
            $log_comment=$row["log_comments"];
        }
    }

    //Get Reason List
    $reasonSet=array();
    $sql="SELECT `log_reason`.`reason_id`,`reason_reason` FROM `log_reason` LEFT JOIN `standerd_status_reason` ON `log_reason`.`reason_id`=`standerd_status_reason`.`reason_id` WHERE `log_reason`.`log_id`=:log_id;";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':log_id', $log_id);
    $stmt->execute();
    $reason_set_result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    if(count($reason_set_result)>0){
        foreach($reason_set_result as $in_row) {
            array_push($reasonSet, $in_row["reason_reason"]);
        }
    }

    return array($app_sts,$reasonSet,$log_comment);
}
?>