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

$json_params = file_get_contents("php://input");

if(!isValidJSON($json_params)){
	$response["code"] = 500;
	$response["message"] = "Invalid call";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}

$PARAMS = json_decode($json_params, true);

if(!isset($PARAMS['wallet'],$PARAMS['pass'],$PARAMS['address'])){
	$response["code"] = 501;
	$response["message"] = "Invalid call";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}

$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN, SERVER_RPC, PORT_RPC, PATH_WALLET . $PARAMS['wallet']);

if(!is_null($btc->walletpassphrase($PARAMS['pass'], 30))){
	
	$response["code"] = 301;
	$response["message"] = $btc->response['error']['message'];
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
	
}

	$balance = 0;
	$unconfirmed = 0;
	
	if(($unspends = $btc->listunspent(6, 9999999, array($PARAMS['address']))) === false){
		$response["code"] = 1003;
		$response["message"] = $btc->response['error']['message'];
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	foreach($unspends as $unspend){
		
		$balance = number_format($balance + $unspend['amount'], 8, ".", "");
		
	}

	
	if(($unspends = $btc->listunspent(0, 5, array($PARAMS['address']))) === false){
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

