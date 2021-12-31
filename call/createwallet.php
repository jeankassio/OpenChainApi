<?php
require_once(dirname(__FILE__,2) . "/conn/functions.php");
require_once(dirname(__FILE__, 2) . "/conn/easybitcoin.php");


/*

{
	name: name wallet,
	pass: password wallet
}

*/


$json_params = file_get_contents("php://input"); // Get the json data

if(!isValidJSON($json_params)){ //check validate of json
	$response["code"] = 500;
	$response["message"] = "Invalid call";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}

$PARAMS = json_decode($json_params, true); //Convert json to array

if(strlen($PARAMS['name']) < 4 OR strlen($PARAMS['name']) > 30){ //Apply rules to lenght name of wallet
	
	$response["code"] = 301;
	$response["message"] = "Wallet name needs to be between 4 and 30 characters";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
	
}elseif(strlen($PARAMS['pass']) < 6 OR strlen($PARAMS['pass']) > 80){ //Apply rules to lenght pass of wallet
	
	$response["code"] = 302;
	$response["message"] = "Password needs to be between 6 and 80 characters";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
	
}

$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN); //Create a connection to RPC bitcoin
	
	$wallet = array_search($PARAMS['name'], $btc->listwallets()); //List wallets and check if exist

	if($wallet !== false){ //if wallet exist
		
		$response["code"] = 303;
		$response["message"] = "Wallet name unavailable";
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
		
	}
	
	
	$wallet = $btc->createwallet($PARAMS['name'], false, false, $PARAMS['pass'], false, false, true); //create a wallet
	
	if(isset($wallet['name'])){ //If return that id, wallet as create
		
		if($wallet['name'] == $PARAMS['name']){ //check if as same, for security
			
			$response["code"] = 200;
			$response["message"] = "Success";
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
			
		}else{
			
			$response["code"] = 305;
			$response["message"] = $btc->response['error']['message'];
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
			
		}
		
	}else{
		
		$response["code"] = 304;
		$response["message"] = $btc->response['error']['message'];
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	