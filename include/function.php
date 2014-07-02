<?php
date_default_timezone_set('Asia/Kolkata');
function getDateFormat($time_stamp){ return  date("d-m-Y H:i",$time_stamp);}
function isValidEmail($email){	return preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email);}
function validate_numeric($variable) {  return is_numeric($variable);}
function stripTags($variable) {	return strip_tags($variable);}
function sendDataOverPost($url, $fields, $method, $timeout=60, $port=80) {
	$ch = curl_init();
	if (strtoupper($method) == 'POST'){   
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	}else if (strtoupper($method) == 'SOLR'){
                curl_setopt($ch, CURLOPT_URL, $url . $fields);
        }else{
		curl_setopt($ch, CURLOPT_URL, $url . '?' . $fields);   
	}
        curl_setopt($ch, CURLOPT_PORT , $port);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
	$result	= curl_exec($ch);
	if($result === false){
		error_log("Curl cannot connect with remote host ".curl_error($ch)." URL :".$url);
	}
	curl_close($ch);
	return $result;
}

function sendXmlOverPost($url, $xml) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);

	// For xml, change the content-type.
	curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
	//if(CurlHelper::checkHttpsURL($url)) { 
	//  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	//  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	//}

	// Send to remote and return data to caller.
	$result	= curl_exec($ch);
	//$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
	curl_close($ch);
	
	return $result;
}

function time_to_sec($time) { 
    $hours = substr($time, 0, -6); 
    $minutes = substr($time, -5, 2); 
    $seconds = substr($time, -2); 

    return $hours * 3600 + $minutes * 60 + $seconds; 
} 

function sec_to_time($seconds) { 
    $hours = floor($seconds / 3600); 
    $minutes = floor($seconds % 3600 / 60); 
    $seconds = $seconds % 60; 

    return sprintf("%d:%02d:%02d", $hours, $minutes, $seconds); 
} 
function getlocationcoords($lat, $lon, $width, $height){
   $x = (($lon + 180) * ($width / 360));
   $y = ((($lat * -1) + 90) * ($height / 180));
   return array("x"=>($x),"y"=>($y));
}

/**
 * Converts a simpleXML element into an array. Preserves attributes and everything.
 * You can choose to get your elements either flattened, or stored in a custom index that
 * you define.
 * For example, for a given element
 * <field name="someName" type="someType"/>
 * if you choose to flatten attributes, you would get:
 * $array['field']['name'] = 'someName';
 * $array['field']['type'] = 'someType';
 * If you choose not to flatten, you get:
 * $array['field']['@attributes']['name'] = 'someName';
 * _____________________________________
 * Repeating fields are stored in indexed arrays. so for a markup such as:
 * <parent>
 * <child>a</child>
 * <child>b</child>
 * <child>c</child>
 * </parent>
 * you array would be:
 * $array['parent']['child'][0] = 'a';
 * $array['parent']['child'][1] = 'b';
 * ...And so on.
 * _____________________________________
 * @param simpleXMLElement $xml the XML to convert
 * @param boolean $flattenValues    Choose wether to flatten values
 *                                    or to set them under a particular index.
 *                                    defaults to true;
 * @param boolean $flattenAttributes Choose wether to flatten attributes
 *                                    or to set them under a particular index.
 *                                    Defaults to true;
 * @param boolean $flattenChildren    Choose wether to flatten children
 *                                    or to set them under a particular index.
 *                                    Defaults to true;
 * @param string $valueKey            index for values, in case $flattenValues was set to
		*                            false. Defaults to "@value"
 * @param string $attributesKey        index for attributes, in case $flattenAttributes was set to
		*                            false. Defaults to "@attributes"
 * @param string $childrenKey        index for children, in case $flattenChildren was set to
		*                            false. Defaults to "@children"
 * @return array the resulting array.
 */
function simpleXMLToArray($xml,
				$flattenValues=true,
				$flattenAttributes = true,
				$flattenChildren=true,
				$valueKey='@value',
				$attributesKey='@attributes',
				$childrenKey='@children'){

	$return = array();
	if(!($xml instanceof SimpleXMLElement)){return $return;}
	$name = $xml->getName();
	$_value = trim((string)$xml);
	if(strlen($_value)==0){$_value = null;};

	if($_value!==null){
		if(!$flattenValues){$return[$valueKey] = $_value;}
		else{$return = $_value;}
	}

	$children = array();
	$first = true;
	foreach($xml->children() as $elementName => $child){
		$value = simpleXMLToArray($child, $flattenValues, $flattenAttributes, $flattenChildren, $valueKey, $attributesKey, $childrenKey);
		if(isset($children[$elementName])){
			if($first){
				$temp = $children[$elementName];
				unset($children[$elementName]);
				$children[$elementName][] = $temp;
				$first=false;
			}
			$children[$elementName][] = $value;
		}
		else{
			$children[$elementName] = $value;
		}
	}
	if(count($children)>0){
		if(!$flattenChildren){$return[$childrenKey] = $children;}
		else{$return = array_merge($return,$children);}
	}

	$attributes = array();
	foreach($xml->attributes() as $name=>$value){
		$attributes[$name] = trim($value);
	}
	if(count($attributes)>0){
		if(!$flattenAttributes){$return[$attributesKey] = $attributes;}
		else{$return = array_merge($return, $attributes);}
	}
   
	return $return;
}
function extract_unit($string, $start, $end){
	$pos = stripos($string, $start);
	$str = substr($string, $pos);
	$str_two = substr($str, strlen($start));
	$second_pos = stripos($str_two, $end);
	$str_three = substr($str_two, 0, $second_pos);
	$unit = trim($str_three); // remove whitespaces
	return $unit;
}
function make_thumb($src,$dest,$desired_width){

  /* read the source image */
  $source_image = imagecreatefromjpeg($src);
  $width = imagesx($source_image);
  $height = imagesy($source_image);
  
  /* find the "desired height" of this thumbnail, relative to the desired width  */
  $desired_height = floor($height*($desired_width/$width));
  
  /* create a new, "virtual" image */
  $virtual_image = imagecreatetruecolor($desired_width,$desired_height);
  
  /* copy source image at a resized size */
  imagecopyresized($virtual_image,$source_image,0,0,0,0,$desired_width,$desired_height,$width,$height);
  
  /* create the physical thumbnail image to its destination */
  imagejpeg($virtual_image,$dest);
}
function isValidURL($url){
	return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}
function aes256Encrypt($key, $data) {
	if(32 !== strlen($key)) $key = hash('SHA256', $key, true);
	$padding = 16 - (strlen($data) % 16);
	$data .= str_repeat(chr($padding), $padding);
	return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, str_repeat("\0", 16));
}

function aes256Decrypt($key, $data) {
	if(32 !== strlen($key)) $key = hash('SHA256', $key, true);
	$data	 = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, str_repeat("\0", 16));
	$padding = ord($data[strlen($data) - 1]);
	return substr($data, 0, -$padding);
}

function sendemail($useremail,$userfullname,$subject,$htmlbody,$textbody,$attach_file_path='',$logemail=0){
	$script_url 		= $_SERVER["SCRIPT_NAME"]; 
	$script_path		= $_SERVER["SCRIPT_FILENAME"];
	$script_realpath	= str_replace($script_url,"",$script_path);
	//echo $script_realpath;
	require($script_realpath."/phpmailer/phpmailer.inc.php");
	
	$mail = new phpmailer;

	$mail->IsSMTP();
	//$mail->SMTPAuth   = true;                  // enable SMTP authentication
	//$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
	//$mail->Host       = "smtp.hungama.com,smtpout.hungama.com";
	//$mail->Host     = "203.199.134.145";
	$mail->Host   	  = "202.52.134.145";
	$mail->SMTPAuth   = false;

	$mail->Port       = 25;

	$mail->Username   = "";  // GMAIL username
	$mail->Password   = "";  // GMAIL password
	
	$mail->From       = "noreply@hoonur.com";
	$mail->FromName   = "Hoonur.com Support";
	$mail->Subject    = $subject;
	$mail->Body       = $htmlbody;                      //HTML Body
	$mail->AltBody    = $textbody; //Text Body

	$mail->WordWrap   = 50; // set word wrap

	$mail->AddAddress($useremail,$userfullname);
	//$mail->AddReplyTo("replyto@yourdomain.com","Webmaster");
	//$mail->AddAttachment("/path/to/file.zip");             // attachment
	//$mail->AddAttachment("/path/to/image.jpg", "new.jpg"); // attachment
	
	if($attach_file_path!=''){
		$mail->AddAttachment($attach_file_path);
	}
	
	$mail->IsHTML(true); // send as HTML 10.0.0.16
	//if($logemail){$comm_log	=	communicationLog("E","noreply@gotcubed.com",$useremail,$subject,$htmlbody,$textbody,"","");}
	if(!$mail->Send()) {
	  //$mailpop = new POP3();
		  //$mailpop->Authorise('10.0.0.16', 110, 30, 'no-reply', 'vir123', 1);
	  //if(!$mail->Send()) {
		  return 0;
	  //}else{
	//    return 1;
	  //}
	} else {
	  return 1;
	}
}

//to generate unique numeric random number
function randomPrefix($length=12,$code_type=2){
	if($code_type==2){
		//Generate Numeric String
		$time 		= microtime(); //gets microtime
		$strTime 	= (string) $time; //convert to string 
		$val 		= substr(str_replace(" ",'',str_replace(".",'',$strTime)),0,$length); //remove spaces and dots(.)
		$val 		= str_shuffle($val); //shuffle 
		$val		= substr($val,0,8); //take first 8 numbers
		$newval 	= substr(preg_replace('/[a-z]+/','',uniqid(rand(), true)),0,13); // create uniqid and replace the alphabets with '' and take first 13 numbers
		$final_val 	= str_shuffle($val.$newval);// concatinate the 8 and 13 numbers and shuffle them
		$final_val 	= str_replace(".",'',$final_val); //replace dots if any;
		
		for($z=0;$z<strlen($final_val);$z++){
			if($final_val[0]==0){
				$final_val	=	substr($final_val, 1);
			}else{
				break;
			}
		}
		$final_val 	= substr($final_val,0,$length); // finally take 16 numbers from the concatinated value
		return $final_val;
	}elseif($code_type==1){
		$numAlpha=rand(1, ($length-1));
		$numNonAlpha=$length-$numAlpha;
		$pwd 	   	  = '';
		$listAlpha 	  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$listNonAlpha = '123456789';
		
		$a	=	1;
		$b	=	strlen($listAlpha)-$numAlpha;
		if($a > $b){
			$tmp = $a;
			$a 	 = $b;
			$b   = $tmp;
	    }
		$start 	 = mt_rand($a, $b);
		$string  = str_shuffle($listAlpha);
		$pwd 	.= substr($string,$start,$numAlpha);
		
		$a	=	1;
		$b	=	strlen($listNonAlpha)-$numNonAlpha;
		if($a > $b){
			$tmp = $a;
			$a 	 = $b;
			$b   = $tmp;
	    }
		$start 	= mt_rand($a, $b);
		$string = str_shuffle($listNonAlpha);
		$pwd   .= substr($string,$start,$numNonAlpha);
		$final_val = str_shuffle($pwd);
		return $final_val;
	}
}//end function

function random_num($n=5){
    return rand(0, pow(10, $n));
}

function age_from_dob($dob) {
    list($y,$m,$d) = explode('-', $dob);
    if (($m = (date('m') - $m)) < 0) {
        $y++;
    } elseif ($m == 0 && date('d') - $d < 0) {
        $y++;
    }
    return date('Y') - $y;
}

function get_filename(){
    $php_self = $_SERVER['PHP_SELF'];
    $filename = explode("/", $php_self);
    $filename = array_reverse($filename);
    return $filename[0];
}

function get_days($date1, $date2){
	$numberDays	=	0;
	if($date1 && $date2){
		$startTimeStamp = strtotime($date1);
		$endTimeStamp 	= strtotime($date2);
		$timeDiff = abs($endTimeStamp - $startTimeStamp);
		$numberDays = $timeDiff/86400;  // 86400 seconds in one day
		// and you might want to convert to integer
		$numberDays = intval($numberDays);
	}
	return $numberDays; 
}

function get_months($date1, $date2) { 
   $time1  = strtotime($date1); 
   $time2  = strtotime($date2); 
   $my     = date('n-Y', $time2); 
   $mesi = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec'); 
    
   //$months = array(date('F', $time1)); 
   $months = array(); 
   $f      = ''; 

   while($time1 < $time2) { 
      if(date('n-Y', $time1) != $f) { 
         $f = date('n-Y', $time1); 
         if(date('n-Y', $time1) != $my && ($time1 < $time2)) { 
             $str_mese=$mesi[(date('n', $time1)-1)]; 
            $months[] = $str_mese." ".date('Y', $time1); 
         } 
      } 
      $time1 = strtotime((date('Y-n-d', $time1).' +15days')); 
   } 

   $str_mese=$mesi[(date('n', $time2)-1)]; 
   $months[] = $str_mese." ".date('Y', $time2); 
   return $months; 
}
function truncate($text, $chars){
		if(strlen($text) <= $chars){
			return $text;
		}
		$text = $text." ";
        $text = substr($text,0,$chars);
        $text = substr($text,0,strrpos($text,' '));
        $text = $text."...";
        return $text;
}
function stripExtension($filename = '') {
    if (!empty($filename)) {
        $str=explode('/',$filename);
		$len=count($str);
		$str2=explode('.',$str[($len-1)]);
		$len2=count($str2);
		$ext=$str2[($len2-1)];
		return $ext;
    } else {
        return false;
    }
}
function output_file($Source_File, $Download_Name, $mime_type='')
{
/*
$Source_File = path to a file to output
$Download_Name = filename that the browser will see
$mime_type = MIME type of the file (Optional)
*/
if(!is_readable($Source_File)) die('File not found or inaccessible!');
 
$size = filesize($Source_File);
$Download_Name = rawurldecode($Download_Name);
 
/* Figure out the MIME type (if not specified) */
$known_mime_types=array(
    "pdf" => "application/pdf",
    "csv" => "application/csv",
    "txt" => "text/plain",
    "html" => "text/html",
    "htm" => "text/html",
    "exe" => "application/octet-stream",
    "zip" => "application/zip",
    "doc" => "application/msword",
    "xls" => "application/vnd.ms-excel",
    "ppt" => "application/vnd.ms-powerpoint",
    "gif" => "image/gif",
    "png" => "image/png",
    "jpeg"=> "image/jpg",
    "jpg" =>  "image/jpg",
    "php" => "text/plain"
);
 
if($mime_type==''){
     $file_extension = strtolower(substr(strrchr($Source_File,"."),1));
     if(array_key_exists($file_extension, $known_mime_types)){
        $mime_type=$known_mime_types[$file_extension];
     } else {
        $mime_type="application/force-download";
     };
};
 
@ob_end_clean(); //off output buffering to decrease Server usage
 
// if IE, otherwise Content-Disposition ignored
if(ini_get('zlib.output_compression'))
  ini_set('zlib.output_compression', 'Off');
 
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="'.$Download_Name.'"');
header("Content-Transfer-Encoding: binary");
header('Accept-Ranges: bytes');
 
header("Cache-control: private");
header('Pragma: private');
header("Expires: Thu, 26 Jul 2012 05:00:00 GMT");
 
// multipart-download and download resuming support
if(isset($_SERVER['HTTP_RANGE']))
{
    list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
    list($range) = explode(",",$range,2);
    list($range, $range_end) = explode("-", $range);
    $range=intval($range);
    if(!$range_end) {
        $range_end=$size-1;
    } else {
        $range_end=intval($range_end);
    }
 
    $new_length = $range_end-$range+1;
    header("HTTP/1.1 206 Partial Content");
    header("Content-Length: $new_length");
    header("Content-Range: bytes $range-$range_end/$size");
} else {
    $new_length=$size;
    header("Content-Length: ".$size);
}
 
/* output the file itself */
$chunksize = 1*(1024*1024); //you may want to change this
$bytes_send = 0;
if ($Source_File = fopen($Source_File, 'r'))
{
    if(isset($_SERVER['HTTP_RANGE']))
    fseek($Source_File, $range);
 
    while(!feof($Source_File) &&
        (!connection_aborted()) &&
        ($bytes_send<$new_length)
          )
    {
        $buffer = fread($Source_File, $chunksize);
        print($buffer); //echo($buffer); // is also possible
        flush();
        $bytes_send += strlen($buffer);
    }
fclose($Source_File);
} else die('Error - can not open file.');
 
die();
}

function validatecvv2($ccNum,$ccCVV){
	if (preg_match("/^3[47][0-9]{13}$/", $ccNum)){
		if(strlen($ccCVV)==4){ return true;	}
	}else{
		if(strlen($ccCVV)==3){	return true; }
	}
	return false;
}

function validateIp($input){
	if (preg_match( "/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $input)){
        return true;
    }
	return false;
}
function clean_no ($cc_no){
	// Remove non-numeric characters from $cc_no   
	return preg_replace ('/[^0-9]+/', '', $cc_no); 
} 
function identifycc ($cc_no){ 
	$cc_no = clean_no ($cc_no); 
	// Get card type based on prefix and length of card number   
	if (preg_match ('/^4(.{12}|.{15})$/', $cc_no)) 
		return 'visa';   
	if (preg_match ('/^5[1-5].{14}$/', $cc_no)) 
		return 'mastercard'; 
	if (preg_match ('/^3[47].{13}$/', $cc_no)) 
		return 'amex'; 
	if (preg_match ('/^3(0[0-5].{11}|[68].{12})$/', $cc_no)) 
		return 'dinersclub'; 
	if (preg_match ('/^6011.{12}$/', $cc_no)) 
		return 'discover'; 
	if (preg_match ('/^(3.{15}|(2131|1800).{11})$/', $cc_no)) 
		return 'jcb'; 
	if (preg_match ('/^2(014|149).{11})$/', $cc_no)) 
		return 'enroute'; 

	return 'unknown'; 
}

function cleanHex($input){
	$clean = preg_replace("![\][xX]([A-Fa-f0-9]{1,3})!", "",$input);
	return $clean;
}

function genUserPassword($input){
	$output	=	'';
	if($input){
		$theKey = "TYLON";
		$output	=	base64_encode(aes256Encrypt($theKey,$input));
	}
	return $output;
}

function writeLog($filepath, $event ,$data){
    $filename = $filepath.'_'.date('Ymd').'.log';
    $somecontent = date('Y-m-d H.i.s').':'.$_SERVER['REMOTE_ADDR'].':'.$_SERVER['HTTP_REFERER'].':'.$_SERVER['REQUEST_URI'].':'.$_SERVER['REQUEST_METHOD'].' ::::EVENT: '.$event.' ::::DATA: '.$data."\n";
    $handle = fopen($filename, 'a');
    fwrite($handle, $somecontent);
    fclose($handle);
}function array_sanitizer($array){
    //$array_count=count($array);
    if(!isset($array[0])){
        $newarray[0] = $array;
    }else{
        $newarray = $array;
    }
    return $newarray;
}
?>