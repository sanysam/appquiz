<?php
//general db functions
function check_unique($tblname,$fldname,$fldvalue){
	global $db;
    //echo "select ".$fldname." as fld_value from ".$tblname." where ".$fldname." =  ".$fldvalue;
	$SqlChk =   $db->query("select ".$fldname." as fld_value from ".$tblname." where ".$fldname." = ? ",$fldvalue);
	$nrC    =   $db->num_rows;
	if(!$nrC){
		return  true;
	}else{
		return  false;
	}
}

function errorlog($error,$query){
	$message  = 'Invalid query: ' . $error . "-";
    $message .= 'Whole query: ' . $query;
	error_log($message);
	return true;
}

function validateInput($v){return addslashes($v);}

//general api functions
function validatePrivateKey($privatekey,$profileid){	
	$privatekeycode	=	'';
	if($privatekey && $profileid){
		global $db;
		//get the accessid based on mode
		$SqlChk =   $db->query("select USERNAME,PASSWORD,APIKEY from tbl_merchant_api_access where STATUS='Y' and PROFILEID=? ",$profileid);
		$nrC    =   $db->num_rows;
		if($nrC){
			foreach($SqlChk AS $global_result) {
				$api_username	=	$global_result["USERNAME"];
				$api_password	=	$global_result["PASSWORD"];
				$api_key		=	$global_result["APIKEY"];
				$api_login		=	$api_username.":|:".$api_password;
			}
			//get the salt
			$privatekeycode	=	 hash('sha256', $api_key.'@'.$api_login);
			if($privatekeycode==$privatekey){	return true; }
		}
	}
	return false;
}

function validateKey($key,$mode){
	if($key && $mode){
		global $db;
		//get the accessid based on mode
		$SqlChk =   $db->query("select ACCESSID,CHANNELID from tbl_channel_master where CHANNEL = ? ",$mode);
		$nrC    =   $db->num_rows;
		if($nrC){
			foreach($SqlChk AS $global_result) {
				$accessid	=	$global_result["ACCESSID"];
				$channelid	=	$global_result["CHANNELID"];
			}
			//get the salt
			$myFile = "/var/www/paykeys/".$mode.".inc";
			$fh = fopen($myFile, 'r');
			$theData = fread($fh, filesize($myFile));
			fclose($fh);
			
			$keycode	=	 hash('sha256', $accessid.'~'.$theData);
			if($keycode==$key){	return $channelid; }
		}
	}
	return false;
}

function validateSecretKey($key,$merchantid){
	if($key && $merchantid){
		global $db;
		//get the accessid based on mode
		$SqlChk =   $db->query("select ACCESSID,CHANNELID from tbl_channel_master where CHANNEL = ? ",$mode);
		$nrC    =   $db->num_rows;
		if($nrC){
			foreach($SqlChk AS $global_result) {
				$accessid	=	$global_result["ACCESSID"];
				$channelid	=	$global_result["CHANNELID"];
			}
			//get the salt
			$myFile = "/var/www/paykeys/".$mode.".inc";
			$fh = fopen($myFile, 'r');
			$theData = fread($fh, filesize($myFile));
			fclose($fh);
			
			$keycode	=	 hash('sha256', $accessid.'~'.$theData);
			if($keycode==$key){	return $channelid; }
		}
	}
	return false;
}

function validateMerchantChannel($profileid,$channelid){
	$profileDetails=array();
	if($profileid && $channelid){
		global $db;
		$SqlChk =   $db->query("select a.MERCHANT_CHANNEL_PG_ID,a.MERCHANTID from tbl_merchant_channel_pg_rates a,tbl_channel_pg_master b where a.CHANNEL_PG_ID=b.CHANNEL_PG_ID and a.PROFILEID=? and b.CHANNELID=? and b.CHANNEL_PG_STATUS=1 and a.MERCHANT_CHANNEL_PG_STATUS=1 ORDER BY a.PRIORITY DESC LIMIT 1",$profileid,$channelid);
		$nrC    =   $db->num_rows;
		if($nrC){
			foreach($SqlChk AS $global_result) {
				$profileDetails["MERCHANTID"]				=	$global_result["MERCHANTID"];
				$profileDetails["MERCHANT_CHANNEL_PG_ID"]	=	$global_result["MERCHANT_CHANNEL_PG_ID"];
			}
			return $profileDetails;
		}	
	}
	return false;
}

function validateMerchantDomain($merchantid,$domain){
	if($merchantid && validate_numeric($merchantid)){
		global $db;
		$merchantid = validateInput($merchantid);
		
		$SqlChk =   $db->query("SELECT MERCHANTID FROM `tbl_merchant_master` a,tbl_merchant_channel_pg_urls b WHERE a.MERCHANTID=b.MERCHANTID and a.MERCHANT_STATUS=1 and a.`MERCHANTID`= ? and b.MERCHANT_REGISTERED_DOMAIN=?",$merchantid,$domain);
		$nrC    =   $db->num_rows;
		if($nrC){
			return true;
		}		
	}
	return false;
}

function getMerchantDetails($merchantid,$profileid){
	$mercDetails=array();
	if($profileid && validate_numeric($profileid) && $merchantid && validate_numeric($merchantid)){
		global $db;
		$profileid 	= validateInput($profileid);
		$merchantid = validateInput($merchantid);
		
		$SqlChk =   $db->query("SELECT MERCHANT,USERNAME,MERCHANT_TXN_STATUS_POST,MERCHANT_SUCCESS_URL,MERCHANT_IPN_URL FROM `tbl_merchant_master` a, tbl_merchant_channel_pg_urls b, tbl_merchant_api_access c WHERE a.PROFILEID = b.PROFILEID AND a.PROFILEID = c.PROFILEID AND a.PROFILEID=? AND a.MERCHANTID=?", $profileid, $merchantid);
		$nrC    =   $db->num_rows;
		if($nrC){
			foreach($SqlChk AS $global_result) {
				$mercDetails["MERCHANT"]				=	$global_result["MERCHANT"];
				$mercDetails["MERCHANT_API_USERNAME"]	=	$global_result["USERNAME"];	
				$mercDetails["MERCHANT_TXN_STATUS_POST"]=	$global_result["MERCHANT_TXN_STATUS_POST"];	
				$mercDetails["MERCHANT_SUCCESS_URL"]	=	$global_result["MERCHANT_SUCCESS_URL"];
				$mercDetails["MERCHANT_IPN_URL"]		=	$global_result["MERCHANT_IPN_URL"];
			}
			return $mercDetails;
		}		
	}
	return false;
}

function getMerchantHandler($merchantid,$mode){
	$mercPayDetails=array();
	if($merchantid && validate_numeric($merchantid)){
		global $db;
		
		$SqlChk =   $db->query("SELECT a.MERCHANTID ,a.MERCHANT_CHANNEL_PG_ID,a.CHANNEL_PG_ID,BANKMID,LOCATIONID,HANDLER,HANDLERTYPE,b.PGID,b.CHANNELID,a.MIN_AMOUNT,a.MAX_AMOUNT,b.AGGREGATORID FROM `tbl_merchant_channel_pg_rates` a,tbl_channel_pg_master b,tbl_channel_master c where a.CHANNEL_PG_ID=b.CHANNEL_PG_ID and b.`CHANNELID`=c.`CHANNELID` and LOWER(c.`CHANNEL`)=? and a.`PRIORITY`=1 and a.`MERCHANT_CHANNEL_PG_STATUS`=1 and `MERCHANTID`=? LIMIT 1",$mode,$merchantid);
		$nrC    =   $db->num_rows;
		if($nrC){
			foreach($SqlChk AS $global_result) {
				$mercPayDetails["MERCHANTID"]				=	$global_result["MERCHANTID"];
				$mercPayDetails["MERCHANT_CHANNEL_PG_ID"]	=	$global_result["MERCHANT_CHANNEL_PG_ID"];
				$mercPayDetails["CHANNEL_PG_ID"]			=	$global_result["CHANNEL_PG_ID"];
				$mercPayDetails["BANKMID"]					=	$global_result["BANKMID"];	
				$mercPayDetails["TID"]						=	$global_result["LOCATIONID"];
				$mercPayDetails["HANDLER"]					=	$global_result["HANDLER"];
				$mercPayDetails["HANDLERTYPE"]				=	$global_result["HANDLERTYPE"];
				$mercPayDetails["MIN_AMOUNT"]				=	$global_result["MIN_AMOUNT"];
				$mercPayDetails["MAX_AMOUNT"]				=	$global_result["MAX_AMOUNT"];
				$mercPayDetails["PAYMENTGATEWAYID"]			=	$global_result["PGID"];	
				$mercPayDetails["PAYMENTTYPEID"]			=	$global_result["CHANNELID"];
				$mercPayDetails["AGGREGATORID"]				=	$global_result["AGGREGATORID"];
			}
			return $mercPayDetails;
		}		
	}
	return false;
}

function getCurrency($currencycode){
	if($currencycode){
		global $db;
		$SqlChk =   $db->query("select CURRENCYBANKCODE from tbl_currency_master where LOWER(CURRENCY)=?",$currencycode);
		$nrC    =   $db->num_rows;
		if($nrC){
			foreach($SqlChk AS $global_result) {
				$bankCurrencyCode	=	$global_result["CURRENCYBANKCODE"];
			}
			return $bankCurrencyCode;
		}
	}
	return false;	
}

function checkSanboxMode($profileid){
	$sanboxtbl		=	'';
	$sandboxstatus	=	'';
	if($profileid && validate_numeric($profileid)){
		global $db;
		$profileid = validateInput($profileid);
		$SqlChk =   $db->query("select SANDBOX from tbl_business_profiles where PROFILEID=?",$profileid);
		$nrC    =   $db->num_rows;
		if($nrC){
			foreach($SqlChk AS $global_result) {
				$sandboxstatus	=	$global_result["SANDBOX"];
			}
			if($sandboxstatus=='Y'){	$sanboxtbl = 'sbox_';	}
		}
	}
	return  $sanboxtbl;
}

function checkMerchantTxnId($profileid,$merchanttxnid){
	if($profileid && validate_numeric($profileid)){
		global $db, $sanboxextn;
		$profileid 	= 	validateInput($profileid);
		
		$SqlChk =   $db->query("select TXNID from ".$sanboxextn."tbl_transaction_master where PROFILEID=? and MERCHANT_TXN_ID=?",$profileid,$merchanttxnid);
		$nrC    =   $db->num_rows;
		if(!$nrC){
			return true;
		}
	}
	return false;
}

function getFirstKey(){
	$paramDetails	=	array();
	global $db;
	$SqlChk =   $db->query("select PARAM,PARAMVAL from tbl_global");
	$nrC    =   $db->num_rows;
	if($nrC){
		foreach($SqlChk AS $global_result) {
			$paramDetails[$global_result["PARAM"]]	=	$global_result["PARAMVAL"];
		}
		return $paramDetails;
	}
	return false;
}

function getSecondKey(){
	$theData	=	'';	
	$myFile 	= 	"/var/www/paykeys/cckey.inc";
	$fh = fopen($myFile, 'r');
	$theData = fread($fh, filesize($myFile));
	fclose($fh);
	return $theData;
}
?>