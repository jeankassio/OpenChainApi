<?php
require_once(dirname(__FILE__,2) . "/conn/functions.php");
require_once(dirname(__FILE__, 2) . "/conn/easybitcoin.php");

/*

{
	txid: transaction id
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



if(!isset($PARAMS['txid'])){
	$response["code"] = 501;
	$response["message"] = "Invalid call";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}
$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN);

$txid = $PARAMS['txid'];

	if(($transaction = $btc->getrawtransaction($txid, 1)) === false){
		
		$response["code"] = 501;
		$response["message"] = $btc->response['error']['message'];
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
		
	}
	
	$inputs = array();
	$outputs = array();
	$fee = 0;
	
	foreach($transaction['vin'] as $parent){
		
		$vout = $parent['vout'];
		
		if(($trparent = $btc->getrawtransaction($parent['txid'], 1)) === false){
			
			$response["code"] = 501;
			$response["message"] = $btc->response['error']['message'];
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
			
		}
		
		$key = array_search($vout, array_column($trparent['vout'], 'n'));
		
		$inputs[] = array(
			'address' => $trparent['vout'][$key]['scriptPubKey']['address'],
			'value' => number_format($trparent['vout'][$key]['value'], 8, ".", "")
		);
		
		$fee += $trparent['vout'][$key]['value'];
		
	}
	
	foreach($transaction['vout'] as $parent){
		
		$outputs[] = array(
			'address' => $parent['scriptPubKey']['address'],
			'value' => number_format($parent['value'], 8, ".", "")
		);
		
		$fee -= $parent['value'];
		
	}
	
	$data = array(
		'txid' => $transaction['txid'],
		'hash' => $transaction['hash'],
		'version' => $transaction['version'],
		'size' => $transaction['size'],
		'vsize' => $transaction['vsize'],
		'weight' => $transaction['weight'],
		'locktime' => $transaction['locktime'],
		'blockhash' => $transaction['blockhash'],
		'confirmations' => $transaction['confirmations'],
		'time' => $transaction['time'],
		'blocktime' => $transaction['blocktime'],
		'fee' => number_format($fee, 8, ".", ""),
		'inputs' => $inputs,
		'outputs' => $outputs
	);
	
	$response["code"] = 200;
	$response["info"] = $data;
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
	
	