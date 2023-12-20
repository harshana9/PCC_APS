<?php

require_once("PHPMailer/src/PHPMailer.php");
require_once("PHPMailer/src/SMTP.php");

class SendMail {

  private $ini;
  private $subject;
  private $body;
  private $to_address;


  public function __construct(){
    $dir=dirname(__FILE__)."\..\conf\conf.ini";
    $this->ini = parse_ini_file(str_replace("\\","/",$dir));
    //print_r($this->ini);
  }

  public function send($subject, $body, $to_address, $attachment=null){
    $this->subject = $subject;
    $this->body = $body;
    $this->to_address = $to_address;

    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->IsSMTP(); // enable SMTP

    $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    //$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
    $mail->SMTPDebug = 0;
    $mail->Host = $this->ini["HOST"];
    $mail->Port = $this->ini["PORT"]; // or 587
    $mail->IsHTML(true);
    $mail->Username = $this->ini["USERNAME"];
    $mail->Password = $this->ini["PASSWORD"];
    $mail->SetFrom($this->ini["FROM"]);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AddAddress($to_address);
    $mail->AddEmbeddedImage('email_signature.jpg','sign', 'email_signature.jpg');
    if($attachment!=null){
      $filename=rand()."_Welcome.pdf";
      $pdf = fopen ($filename,'w');
      fwrite ($pdf,$attachment);
      //close output file
      fclose ($pdf);
      $mail->addAttachment($filename);

    }

    if(!$mail->Send()) {
      return "Mailer Error: " . $mail->ErrorInfo;
    } else {
      return "Message has been sent";
    }
  }
}

?>