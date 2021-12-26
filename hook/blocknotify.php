<?php
require_once(dirname(__FILE__,2) . "/conn/functions.php");
require_once(dirname(__FILE__,2) . "/conn/easybitcoin.php");

$hash = $_GET['tx'];

$btc = new Bitcoin(USER_BITCOIN, PASS_BITCOIN);

$block = $btc->getblockheader($hash, true);

$size = $block['height'] + $block['confirmations'];

	if(($infos = GetTXIDlist($size)) !== false){
		
		foreach($infos as $info){
			
			if(($transaction = $btc->getrawtransaction($info['txid'], 1)) === false){
				continue;
			}
			
			$inputs = array();
			$outputs = array();
			$fee = 0;
			
			foreach($transaction['vin'] as $parent){
				
				$vout = $parent['vout'];
				
				if(($trparent = $btc->getrawtransaction($parent['txid'], 1)) === false){
					continue;					
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
				'txid' => $info['txid'],
				'category' => ($info['side'] ? 'send' : 'receive'),
				'address' => $info['addr'],
				'amount' => $info['amount'],
				'hash' => $transaction['hash'],
				'version' => $transaction['version'],
				'size' => $transaction['size'],
				'vsize' => $transaction['vsize'],
				'weight' => $transaction['weight'],
				'locktime' => $transaction['locktime'],
				'blockhash' => $transaction['blockhash'],
				'blockheight' => $block['height'],
				'blocktime' => $transaction['blocktime'],
				'confirmations' => $transaction['confirmations'],
				'time' => $transaction['time'],
				'fee' => number_format($fee, 8, ".", ""),
				'inputs' => $inputs,
				'outputs' => $outputs
			);
			
			if(is_null($info['hook']) OR SendHookRequest($info['hook'], $data)){
				UpdateStatusTXID($info['id']);
			}
			
		}
		
	}