<?php
require_once(dirname(__FILE__,2) . "/conn/functions.php");
require_once(dirname(__FILE__, 2) . "/conn/easybitcoin.php");

/*

{
	conf: 1 / integer,
	wallet: name wallet / chars without space,
	pass: password wallet / chars without space,
	address: bitcoin address to watch,
	hook: optional / url
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

if(!isset($PARAMS['conf'],$PARAMS['wallet'],$PARAMS['pass'],$PARAMS['hook'])){ //Check if call is
	$response["code"] = 501;
	$response["message"] = "Invalid call";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}elseif(is_int($PARAMS['conf'])){ //Check if confirmation is a number valid
	$response["code"] = 302;
	$response["message"] = "Invalid format of number confirmations";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}elseif(!is_null($PARAMS['hook']) AND !filter_var($PARAMS['hook'], FILTER_VALIDATE_URL)){ //Check if the hook parameter is a url or not defined
	$response["code"] = 303;
	$response["message"] = "Invalid webhook URL";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}elseif(strlen($PARAMS['wallet']) < 4 OR strlen($PARAMS['wallet']) > 30 OR strlen($PARAMS['pass']) < 6 OR strlen($PARAMS['pass']) > 80){ //Check rules of lenght of name and pass of wallet
	$response["code"] = 304;
	$response["message"] = "Incorret lenght in wallet name or password";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}

$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN, SERVER_RPC, PORT_RPC, PATH_WALLET . $PARAMS['wallet']); //create a new connection to RPC wallet

if(!is_null($btc->walletpassphrase($PARAMS['pass'], 30))){ //decrypt the wallet
	
	$response["code"] = 301;
	$response["message"] = "Error decoding wallet";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
	
}

	if(($public = $btc->validateaddress($PARAMS['address'])) === false OR ($public['isvalid'] !== true)){
		$response["code"] = 305;
		$response["message"] = "Invalid address";
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}else{		
		$btc->importaddress($PARAMS['address'], "", false);		
	}
	
	SaveAddress($public, $PARAMS['conf'], $PARAMS['hook']); //save address, confirmations e hook if exists

	$response["code"] = 200;
	$response["info"] = "success";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();