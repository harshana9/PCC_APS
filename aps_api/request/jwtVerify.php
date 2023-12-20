<?php

require_once "php-jwt/JwtHandler.php";

function JWTValidation($token=null,$admin=false, $view=false, $delete=false, $create=false, $update=false){
    //JWT token Validation
    if($token!=null) {
        $jwt = new JwtHandler();
        $data =  $jwt->jwtDecodeData($token);
        //echo  json_encode($data);

        if($admin==true AND ($admin xor $data->admin)) {
            throw new Exception("Error: Admin scope. Unauthorized.");
        }
        if($view==true AND ($view xor $data->view)) {
            throw new Exception("Error: View privilage required.");
        }
        if($update==true AND ($update xor $data->update)) {
            throw new Exception("Error: Update privilage required.");
        }
        if($create==true AND ($create xor $data->create)) {
            throw new Exception("Error: Create privilage required.");
        }
        if($delete==true AND ($delete xor $data->delete)) {
            throw new Exception("Error: Delete privilage required.");
        }

        return $data->username;
    }
    else{
        throw new Exception("Error: Authentication Failed.");
    }
}
?>