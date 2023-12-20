<?php

//attributes capture from request
function load_attribute($requestBody, $attibute_name, $required=false, $jason_decoded=false){
    //request body to json format
    $requestArray=array();
    if($jason_decoded){
    	$requestArray = $requestBody;
    }
    else{
    	$requestArray = json_decode($requestBody, true);
    }

    //take the attribute
	if(isset($requestArray[$attibute_name])){
		$dat = $requestArray[$attibute_name];
		if(gettype($dat)=="boolean"){
			//echo "If worked";
			$dat = $dat ? $dat=1 : $dat='null';
			//echo "Dat on fun-".$dat;
		}
		
		return $dat;
	}
	else{
        if($required==true){
            throw new Exception("Required parameter '$attibute_name' missing.");
        }
		return null;
	}
}

function load_attribute_Set($requestBody){
	$requestArray = json_decode($requestBody, true);
	return $requestArray;
}
?>