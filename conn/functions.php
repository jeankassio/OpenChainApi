<?php
require(dirname(__FILE__) . "/system.ini.php");

function isValidJSON($str) {
   json_decode($str);
   return json_last_error() == JSON_ERROR_NONE;
}

function SaveAddress($addr, $conf = 1, $hook = NULL){
	global $conn;
	
	$sql = $conn->prepare("INSERT INTO tbl_address(_address, _confirmations, _webhook) VALUES(:addr, :conf, :hook)");
	return $sql->execute(array(
		':addr' => $addr,
		':conf' => $conf,
		':hook' => $hook
	));
	
}

function UpdateAddress($addr, $conf, $hook){
	global $conn;
	
	$sql = $conn->prepare("UPDATE tbl_address SET _confirmations = :conf, _webhook = :hook WHERE _address = :addr");
	return $sql->execute(array(
		':addr' => $addr,
		':conf' => $conf,
		':hook' => $hook
	));
	
}

function GetAddress($addr){
	global $conn;
	
	$sql = $conn->prepare("SELECT _id FROM tbl_address WHERE _address = :addr");
	$sql->execute(array(
		':addr' => $addr
	));
	
	if($sql->rowCount() > 0){
		return $sql->fetch()[0];
	}else{
		return false;
	}
	
}

function InsertTXID($txid, $addr, $height, $side, $amount){
	global $conn;
	
	if(($address = GetAddress($addr)) !== false){
	
		$sql = $conn->prepare("SELECT _txid FROM tbl_txid WHERE _txid = :txid AND _address = :addr");
		if($sql->execute(array(
			':txid' => $txid,
			':addr' => $address
		))){
		
			if($sql->rowCount() == 0){
				
				$sql = $conn->prepare("INSERT INTO tbl_txid(_txid,_address,_amount,_height,_side) VALUES(:txid, :addr,:amount,:height,:side)");
				return $sql->execute(array(
					':txid' => $txid,
					':addr' => $address,
					':amount' => $amount,
					':height' => $height,
					':side' => $side
				));
				
			}else{
				return true;
			}
			
		}else{
			return false;
		}
	
	}else{
		return false;
	}
	
}

function GetTXIDlist($size){
	global $conn;
	
	$sql = $conn->prepare("SELECT tbl_txid._id AS id,tbl_txid._txid AS txid,tbl_address._confirmations AS conf,tbl_address._address AS addr,tbl_txid._side AS side,tbl_address._webhook AS hook, tbl_txid._amount AS amount FROM tbl_txid LEFT JOIN tbl_address ON tbl_txid._address = tbl_address._id WHERE (:size - tbl_txid._height) >= tbl_address._confirmations AND tbl_txid._executed = :executed");
	$sql->execute(array(
		':size' => $size,
		':executed' => '0'
	));
	
	if($sql->rowCount() > 0){
		
		return $sql->fetchAll(PDO::FETCH_NAMED);
		
	}else{
		return false;
	}
	
}

function SendHookRequest($hook, $data){
	global $conn;
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $hook);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$outp = curl_exec($ch);
	
	curl_close ($ch);

	return ($outp == "OK");
	
}

function UpdateStatusTXID($id){
	global $conn;
	
	$sql = $conn->prepare("UPDATE tbl_txid SET _executed = :executed WHERE _id = :id");
	return $sql->execute(array(
		':executed' => '1',
		':id' => $id
	));
	
}

























