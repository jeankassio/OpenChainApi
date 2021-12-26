<?php
require_once(dirname(__FILE__,2) . "/conn/functions.php");
require_once(dirname(__FILE__,2) . "/conn/easybitcoin.php");

/*
JSON TO RECEIVE:

{
	"wallet" : "wallet name",
	"pass" : "wallet password",
	"inputs" : [
		{
			"address" : "",
			"private" : ""
		},...
	],
	"outputs" : [
		{
			"recipient" : "",
			"value" : 0.00000001
		},{
			"recipient" : "",
			"value" : 0.00000001
		},
	],
	"fee" : 0.00000001,
	"replaceable" : false
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

if(!isset($PARAMS['inputs'],$PARAMS['outputs'],$PARAMS['replaceable'],$PARAMS['fee'],$PARAMS['wallet'],$PARAMS['pass'])){
	$response["code"] = 501;
	$response["message"] = "Invalid call";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}

$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN, SERVER_RPC, PORT_RPC, PATH_WALLET . $PARAMS['wallet']);

if(!is_null($btc->walletpassphrase($PARAMS['pass'], 120))){
	
	$response["code"] = 301;
	$response["message"] = "Error decoding wallet";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
	
}


$inputs = $PARAMS['inputs'];
$_outputs = $PARAMS['outputs'];
$fee = number_format($PARAMS['fee'], 8, ".", "");
$replaceable = ($PARAMS['replaceable'] ? true : false);


$addresses = array();

foreach($inputs as $addrs){

	if(($vaddr = $btc->validateaddress($addrs['address'])) === false OR ($vaddr['isvalid'] !== true)){
		$response["code"] = 1000;
		$response["message"] = "Invalid address";
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}

	if(($tpriv = $btc->dumpprivkey($addrs['address'])) === false){
		$response["code"] = 1001;
		$response["message"] = "Invalid private key";
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}

	if($tpriv !== $addrs['private']){

		$response["code"] = 1002;
		$response["message"] = "Invalid private key";
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
		
	}
	
	$addresses[] = trim($addrs['address']);
	
}
	
	
	if(($unspends = $btc->listunspent(0, 9999999, $addresses)) === false){
		$response["code"] = 1003;
		$response["message"] = $btc->response['error']['message'];
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}
	

	$amount = ($fee * -1);
	$input = array();
	$output = array();
	
		foreach($unspends as $unspend){
			
			$input[] = array(
				'txid' => $unspend['txid'],
				'vout' => $unspend['vout']
			);
			
			$amount = number_format($amount + $unspend['amount'], 8, ".", "");
			
		}
		
		foreach($_outputs as $_output){
			
			$output[][trim($_output['recipient'])] =  number_format($_output['value'], 8, ".", "");
			
			$amount = number_format($amount - $_output['value'], 8, ".", "");
			
		}
	
	
	if($amount > 0){
		
		$iaddr = $addresses[count($addresses) -1];
		
		if((array_key_exists($iaddr, $output)) === false){
			
			$output[][$iaddr] = $amount;
			
		}else{
			
			foreach($output as $ind=>$addr){
				
				if($addr[0] == $iaddr){
					$output[$ind][$iaddr] = number_format($output[$ind][$iaddr] + $amount, 8, ".", "");
				}
				
			}
			
		}
		
	}elseif($amount < 0){
		$response["code"] = 1008;
		$response["message"] = "insufficient funds";
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	
	if(($rawtransaction = $btc->createrawtransaction($input,$output,0,$replaceable)) === false){
			$response["code"] = 1004;
			$response["message"] = $btc->response['error']['message'] . " - ". json_encode($output);
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
	}
	
	if(($signrawtransaction = $btc->signrawtransactionwithwallet($rawtransaction)) === false){
			$response["code"] = 1005;
			$response["message"] = $btc->response['error']['message'];
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
	}
	
	if(($return_txid = $btc->sendrawtransaction($signrawtransaction['hex'])) === false){
			$response["code"] = 1006;
			$response["message"] = $btc->response['error']['message'];
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
	}else{
		
			$response["code"] = 200;
			$response["info"] = $return_txid;
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
	}
	
















