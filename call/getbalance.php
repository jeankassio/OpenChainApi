<?php
require_once(dirname(__FILE__,2) . "/conn/functions.php");
require_once(dirname(__FILE__, 2) . "/conn/easybitcoin.php");

/*

{
	wallet: name wallet / chars without space,
	pass: password wallet / chars without space,
	address: address in wallet / chars without space,
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

if(!isset($PARAMS['wallet'],$PARAMS['pass'],$PARAMS['address'])){
	$response["code"] = 501;
	$response["message"] = "Invalid call";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}

$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN, SERVER_RPC, PORT_RPC, PATH_WALLET . $PARAMS['wallet']); //Connect to RPC Wallet

if(!is_null($btc->walletpassphrase($PARAMS['pass'], 30))){ //Decrypt Wallet
	
	$response["code"] = 301;
	$response["message"] = $btc->response['error']['message'];
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
	
}

	$balance = 0;
	$unconfirmed = 0;
	
	if(($unspends = $btc->listunspent(6, 9999999, array($PARAMS['address']))) === false){ //Get the unspent values of the address with 6 or more confirmations
		$response["code"] = 1003;
		$response["message"] = $btc->response['error']['message'];
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	foreach($unspends as $unspend){
		
		$balance = number_format($balance + $unspend['amount'], 8, ".", "");
		
	}

	
	if(($unspends = $btc->listunspent(0, 5, array($PARAMS['address']))) === false){ //Get the unspent values of the address with 5 or less confirmations
		$response["code"] = 1003;
		$response["message"] = $btc->response['error']['message'];
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	foreach($unspends as $unspend){
		
		$unconfirmed = number_format($unconfirmed + $unspend['amount'], 8, ".", "");
		
	}

$response["code"] = 200;
$response["info"] = array(
	'balance' => number_format($balance, 8, ".", ""),
	'unconfirmed' => number_format($unconfirmed, 8, ".", "")
);
$response["date"] = date("Y-m-d H:i:s");		
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();

