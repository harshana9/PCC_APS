<?php

require "src/JWT.php";
require "src/Key.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler
{
    protected $jwt_secrect;
    protected $token;
    protected $issuedAt;
    protected $expire;
    protected $jwt;
    protected $ini;

    public function __construct()
    {
        //Load Config file
        $this->ini = parse_ini_file("../../conf/conf.ini");

        // set your default time-zone
        date_default_timezone_set($this->ini["time_zone"]);
        $this->issuedAt = time();

        // Token Validity (3600 second = 1hr)
        $this->expire = $this->issuedAt + $this->ini["token_valid_time"];

        // Set your secret or signature
        $this->jwt_secrect = $this->ini["jwt_secret_key"];
    }

    public function jwtEncodeData($iss, $data)
    {

        $this->token = array(
            //Adding the identifier to the token (who issue the token)
            "iss" => $iss,
            "aud" => $iss,
            // Adding the current timestamp to the token, for identifying that when the token was issued.
            "iat" => $this->issuedAt,
            // Token expiration
            "exp" => $this->expire,
            // Payload
            "data" => $data
        );

        $this->jwt = JWT::encode($this->token, $this->jwt_secrect, 'HS256');
        return $this->jwt;
    }

    public function jwtDecodeData($jwt_token)
    {
        try {
            $data = JWT::decode($jwt_token, new Key($this->ini["jwt_secret_key"], "HS256"));
            return $data->data;
        } catch (Exception $e) {
            throw $e;        
        }
    }
}

?>