<?php
require_once(dirname(__FILE__,2) . "/conn/functions.php");
require_once(dirname(__FILE__,2) . "/conn/easybitcoin.php");

$txid = $_GET['tx'];

$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN);


	if(($transaction = $btc->getrawtransaction($txid, 1)) === false){		
		exit();		
	}elseif(!isset($transaction['blockhash'])){		
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
	
	$height = $btc->getblock($transaction['blockhash'], 1)['height'];
	
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

	foreach($data['inputs'] as $input){
		
		if((GetAddress($input['address'])) !== false){			
			InsertTXID($data['txid'], $input['address'], $height, 1, $input['value']);
		}
		
	}

	foreach($data['outputs'] as $input){
		
		if((GetAddress($input['address'])) !== false){			
			InsertTXID($data['txid'], $input['address'], $height, 0, $input['value']);
		}
		
	}
