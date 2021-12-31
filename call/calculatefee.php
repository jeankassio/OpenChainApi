<?php
require_once(dirname(__FILE__,2) . "/conn/functions.php");
require_once(dirname(__FILE__,2) . "/conn/easybitcoin.php");

/*
Necessary send POST JSON:

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
	]
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

if(!isset($PARAMS['inputs'],$PARAMS['outputs'],$PARAMS['wallet'],$PARAMS['pass'])){ //check if call is right
	$response["code"] = 501;
	$response["message"] = "Invalid call";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
}

$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN, SERVER_RPC, PORT_RPC, PATH_WALLET . $PARAMS['wallet']); //Connect to RPC Wallet

$inputs = $PARAMS['inputs'];
$_outputs = $PARAMS['outputs'];
$replaceable = true;
$segwit = true;


if(!is_null($btc->walletpassphrase($PARAMS['pass'], 30))){ //Decrypt Wallet
	
	$response["code"] = 301;
	$response["message"] = "Error decoding wallet";
	$response["date"] = date("Y-m-d H:i:s");		
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit();
	
}

$addresses = array();

foreach($inputs as $addrs){

	if(($vaddr = $btc->validateaddress($addrs['address'])) === false OR ($vaddr['isvalid'] !== true)){ //Check validate of address
		$response["code"] = 1000;
		$response["message"] = "Invalid address";
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}

	if(($tpriv = $btc->dumpprivkey($addrs['address'])) === false){ //Get the private key
		$response["code"] = 1001;
		$response["message"] = "Invalid private key";
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}

	if($tpriv !== $addrs['private']){ //Compare private key to continue

		$response["code"] = 1002;
		$response["message"] = "Invalid private key";
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
		
	}
	
	if(substr($addrs['address'], 0, 1) != '3' AND substr($addrs['address'], 0, 3) != 'bc1'){
		$segwit = false;
	}
	
	$addresses[] = $addrs['address'];
	
}
	
	
	if(($unspends = $btc->listunspent(0, 9999999, $addresses)) === false){ //Get unspent values of inputs
		$response["code"] = 1003;
		$response["message"] = $btc->response['error']['message'];
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	
	$amount = 0;
	$input = array();
	$output = array();
	
		foreach($unspends as $unspend){
			
			$input[] = array(
				'txid' => $unspend['txid'],
				'vout' => $unspend['vout']
			);
			
			$amount = number_format($amount + $unspend['amount'], 8, ".", "");
			
		}
		
		foreach($_outputs as $_output){ //Distribute the amount values in outputs
			
			$output[][trim($_output['recipient'])] =  number_format($_output['value'], 8, ".", "");
			
			$amount = number_format($amount - $_output['value'], 8, ".", "");
			
			if(substr($_output['recipient'], 0, 1) != '3' AND substr($_output['recipient'], 0, 3) != 'bc1'){
				$segwit = false;
			}			
			
		}
	
	
	if($amount > 0){ //If rest a change, send to a address in input
		
		$iaddr = $addresses[count($addresses) -1];
		
		if((array_key_exists($iaddr, $output)) === false){ //If not exist address input in output, add
			
			$output[][$iaddr] = $amount;
			
		}else{ 
			
			foreach($output as $ind=>$addr){ 
				
				if($addr[0] == $iaddr){ //if exist, increase the value
					$output[$ind][$iaddr] = number_format($output[$ind][$iaddr] + $amount, 8, ".", "");
				}
				
			}
			
		}
		
	}
	
	
	if(($rawtransaction = $btc->createrawtransaction($input, $output, 0, $replaceable)) === false){ //create the raw transaction
			$response["code"] = 1004;
			$response["message"] = $btc->response['error']['message'];
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
	}
	
	if(($signrawtransaction = $btc->signrawtransactionwithwallet($rawtransaction)) === false){ //sign the raw transaction
			$response["code"] = 1005;
			$response["message"] = $btc->response['error']['message'];
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
	}
	
	if(($decoderawtransaction = $btc->decoderawtransaction($signrawtransaction['hex'])) === false){ //decode raw transaction with hex sign
			$response["code"] = 1006;
			$response["message"] = $btc->response['error']['message'];
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
	}
	
	if(($feeperkbyte = $btc->estimatesmartfee(6, "CONSERVATIVE")) === false){ //get fee per kylobyte in fast fee
			$response["code"] = 1007;
			$response["message"] = $btc->response['error']['message'];
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
	}
	
	$size = ($segwit ? $decoderawtransaction['vsize'] : $decoderawtransaction['size']);
	
	$conservative = number_format(($feeperkbyte['feerate'] / 1000) * $size, 8, ".", "");
	
	if(($feeperkbyte = $btc->estimatesmartfee(6, "ECONOMICAL")) === false){ //get fee per kylobyte in economical fee
			$response["code"] = 1007;
			$response["message"] = $btc->response['error']['message'];
			$response["date"] = date("Y-m-d H:i:s");		
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			exit();
	}
	
	$economical = number_format(($feeperkbyte['feerate'] / 1000) * $size, 8, ".", "");
	
		$response["code"] = 200;
		$response["info"] = array(
							'fast' => $conservative,
							'economical' => $economical,
							'size' => $decoderawtransaction['size'],
							'vsize' => $decoderawtransaction['vsize'],
							);
		$response["date"] = date("Y-m-d H:i:s");		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();




















