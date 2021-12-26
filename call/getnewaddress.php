<?php
require_once(dirname(__FILE__,2) . "/conn/functions.php");
require_once(dirname(__FILE__, 2) . "/conn/easybitcoin.php");

/*

{
	conf: 1 / integer,
	wallet: name wallet / chars without space,
	pass: password wallet / chars without space,
	hook: optional / url
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

if(!isset($PARAMS['conf'],$PARAMS['wallet'],$PARAMS['pass'],$PARAMS['hook'])){
	$response["code"] = 501;
	$response["message"] = "Invalid call";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}elseif(is_int($PARAMS['conf'])){
	$response["code"] = 302;
	$response["message"] = "Invalid format of number confirmations";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}elseif(!is_null($PARAMS['hook']) AND !filter_var($PARAMS['hook'], FILTER_VALIDATE_URL)){
	$response["code"] = 303;
	$response["message"] = "Invalid webhook URL";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}elseif(strlen($PARAMS['wallet']) < 4 OR strlen($PARAMS['wallet']) > 30 OR strlen($PARAMS['pass']) < 6 OR strlen($PARAMS['pass']) > 80){
	$response["code"] = 304;
	$response["message"] = "Incorret lenght in wallet name or password";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}

$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN, SERVER_RPC, PORT_RPC, PATH_WALLET . $PARAMS['wallet']);

if(!is_null($btc->walletpassphrase($PARAMS['pass'], 30))){
	
	$response["code"] = 301;
	$response["message"] = "Error decoding wallet";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
	
}

$public = $btc->getnewaddress();
$private = $btc->dumpprivkey($public);

SaveAddress($public, $PARAMS['conf'], $PARAMS['hook']);

$btc->walletlock();

$response["code"] = 200;
$response["info"] = array(
	'public' => $public,
	'private' => $private
);
$response["date"] = date("Y-m-d H:i:s");		
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();


/*

pegar endereço gerado
buscar chave privada dele
retornar endereço + chave privada

*/
