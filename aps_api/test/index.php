<?php
$txt_token_uri=null;
$txt_username=null;
$txt_password=null;
$txt_uri=null;
$txt_token=null;
$txt_params=null;
$txt_request_body=null;
$txt_response_body=null;
$txt_code=null;
$txt_message=null;

if(isset$_POST["btn_token"]){
    $txt_token_uri=$_POST["txt_token_uri"];
    $txt_username=$_POST["txt_username"];
    $txt_password=$_POST["txt_password"];
    
}

if(isset($_POST["btn_call"])){
    $txt
    $uri = $_POST["txt_uri"]."?token=".$_POST["txt_token"];

}


?>

<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>APS_API_TEST</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
</head>

<body style="background: rgb(203,203,203);">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 style="text-align: center;margin-top: 14px;">API Test Interface</h1>
                <h3>Login Token Generate</h3>
                <div class="input-group"></div>
                <form>
                    <div class="input-group" style="margin: 10px;">
                        <span class="input-group-text">URI</span>
                        <input class="form-control" type="text" name="txt_token_uri">
                    </div>
                    <div class="input-group" style="margin: 10px;">
                        <span class="input-group-text">Username</span>
                        <input class="form-control" type="text" name="txt_username">
                    </div>
                    <div class="input-group" style="margin: 10px;">
                        <span class="input-group-text">Password&nbsp;</span>
                        <input class="form-control" type="text" name="txt_password">
                    </div>
                    <input class="btn btn-success" type="submit" name="btn_token" style="margin: 10px;margin-top: 1px;">
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h3>API Call</h3>
                <form method="post" action="index.php">
                    <input class="form-control" type="text" style="margin-top: 14px;" placeholder="URI" name="txt_uri">
                    <textarea class="form-control" placeholder="Token" style="margin-top: 14px;" name="txt_token"></textarea>
                    <textarea class="form-control" placeholder="Parameters Ex:  &amp;id=1&amp;name=abc" style="margin-top: 14px;" name="txt_param"></textarea>
                    <textarea class="form-control" placeholder="Body (JSON)" style="margin-top: 14px;" name="txt_request_body"></textarea>
                    <input class="btn btn-primary" type="submit" style="margin-top: 11px;" name="btn_call">
                </form>
            </div>
            <div class="col-md-6">
                <h3>API Response</h3>
                <form>
                    <textarea class="form-control" placeholder="Body (JSON)" style="margin-top: 14px;"></textarea>
                    <input class="form-control" type="text" style="margin-top: 14px;" placeholder="Code">
                    <textarea class="form-control" placeholder="Message / Error" style="margin-top: 14px;"></textarea>
                </form>
            </div>
        </div>
    </div>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>