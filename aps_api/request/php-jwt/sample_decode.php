<?php
if (isset($_GET['token'])) {
    require 'JwtHandler.php';
    //require "src/JWT.php";
    $jwt = new JwtHandler();

    $data =  $jwt->jwtDecodeData(trim($_GET['token']));

    //use Firebase\JWT\JWT;

    /*$decoded = JWT::decode(trim($_GET['token']), new Key("this_is_my_secrect", "HS256"));
    $user_id = $decoded->data;*/

    print_r($data);


    /*if(isset($data->id) && isset($data->name) && isset($data->email)):
        echo "<ul>
        <li>ID => $data->id</li>
        <li>Name => $data->name</li>
        <li>Email => $data->email</li>
        </ul>";
    else:
        print_r($data);
    endif;*/
}
?>
<form action="" method="GET">
    <label for="_token"><strong>Enter Token</strong></label>
    <input type="text" name="token" id="_token">
    <input type="submit" value="Docode">
</form>