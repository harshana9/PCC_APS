<?php

//includes
require_once "../mail/sendmail.php";

$sendMail = new SendMail();

$date="2023-12-12";
$m_name="John Doe";
$m_id="0000111";
$amount="13500.00";

$title="MPOS LETTER TO INTRO BTANCH";

$body="<p>Dear Team,</p><p>Please be advised that the following debit entries were made to your Branch's General Remittance Intermediate (GRI) for the sale of MPOS devices on ".$date." and the devices will be dispatched to the merchants promptly upon completion of the necessary formalities.</p><p>Please find below details of MPOS Merchants.</p><table border='1' cellspacing='0' cellpadding='5'><tr><td><b>Merchant Name</b></td><td><b>Merchant ID</b></td><td><b>Debit Amount (Rs)<b></td></tr><tr><td>".$m_name."</td><td>".$m_id."</td><td>".$amount."</td></tr></table><p>If you have any queries or require further information regarding these debits or any related matters, please do not hesitate to reach out to us through 0112490400 and Ext 3. or email merchantdeployments@peoplesbank.lk</p><p>We appreciate your cooperation in promptly settling of your GRI and ensuring accurate records are maintained. Thank you for your continued support.</p><p>Manager-Merchant Acquiring<br/>People's Card Centre<br/>03<sup>rd</sup> Floor, No. 1166, Maradana Road,<br/>Colombo 08, Sri Lanka.</p><img src='cid:sign'/>";

$send = $sendMail->send($title, $body, "mpos@peoplesbank.lk");

echo $send;

?>