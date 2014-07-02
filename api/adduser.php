<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sany
 * Date: 6/30/14
 * Time: 8:31 AM
 * To change this template use File | Settings | File Templates.
 */
$uname=$addr=$photo=$mobile=$addedon=$imei='';
include_once('../include/function.php');

$apierror	 	 	=	false;
$apimsg	 		 	=	'';
$statuscode		 	=	'';
$statusmsg			=	'';
$apicode			=	'';
$uname              =   trim($_POST['name']);
$addr               =   trim($_POST['addr']);
$mobile             =   trim($_POST['mobile']);
$imei               =   trim($_POST['imei']);

if(!$apierror && (!$uname|| $uname=='')){
    $apierror	    = 	true;
    $apimsg		    = 	"Please enter a Name.";
}if(!$apierror && (!$addr|| $addr=='')){
    $apierror	    = 	true;
    $apimsg		    = 	"Please enter an Address.";
}if(!$apierror && (!$mobile|| $mobile=='')){
    $apierror	    = 	true;
    $apimsg		    = 	"Please enter a Mobile no.";
}if(!$apierror && (!is_numeric($mobile) || strlen($mobile)<10 || strlen($mobile)>15)){
    $apierror	    = 	true;
    $apimsg		    = 	"Please enter a valid Mobile no.";
}if(!$apierror && (!$imei|| $imei=='')){
    $apierror	    = 	true;
    $apimsg		    = 	"Please enter a valid imei no.";
}
if(!$apierror){
    include '../common.php';
    $mobilefalg     = check_unique("tbl_user","mobile",$mobile);
    $imeifalg       = check_unique("tbl_user","imei",$imei);
    if(!$mobilefalg && !$apierror){
        $apierror	 =	true;
        $apimsg 	 = 	"Duplicate mobile number";
    }if(!$imeifalg && !$apierror){
        $apierror	 =	true;
        $apimsg 	 = 	"Duplicate imei number";
    }
    $valid_formats  = array("jpg", "png", "gif");
    if(!isset($_POST) || $_SERVER['REQUEST_METHOD'] != "POST"){
        echo "Invalid request";
        exit;
    }
    $uname          =   validateInput($uname);
    $addr           =   validateInput($addr);
    $mobile         =   validateInput($mobile);
    $name           =   $_FILES['photo']['name'];
    $size           =   $_FILES['photo']['size'];
    $tmp            =   $_FILES['photo']['tmp_name'];
    $today          =   time();
    if($tmp){
        if(!strlen($name)){
            $apierror	 =	true;
            $apimsg 	 = 	"Invalid file name";
        }
        list($txt, $ext) = explode(".", $name);
        if(!in_array(strtolower($ext),$valid_formats))
        {
            $apierror	 =	true;
            $apimsg 	 = 	"Invalid file format";
        }
        if(!($size<(1024*1024)))
        {
            $apierror	 =	true;
            $apimsg 	 = "Invalid file size";
        }
        if(!$apierror){
            $actual_file_name = "appqui".'_P'.$today.".".$ext;
            $path       =   $_SERVER['DOCUMENT_ROOT']."/uplds/photo/";
            $url        =   $_FILES['photo'];
            $info 	    =   pathinfo($url['name']);
            $filextn    =   $info['extension'];
            $filename   =   $url['name'];
            $folder     =   $path.$actual_file_name;
            if(move_uploaded_file($url["tmp_name"],$folder)){
                chmod($folder, 0666);
            }else{
                $apierror	 =	true;
                $apimsg 	 = 	"Photo could not be uploaded please contact the administrator.";
            }
        }
    }else{
        $apierror	 =	true;
        $apimsg 	 = 	"Photo not uploaded";
    }
    if(!$apierror){
        $today		 =	time();
        do {
            $apiusername	=	"AI".randomPrefix(7,2);
        } while (check_unique("tbl_user","username",$apiusername)==false);
        $password       =   randomPrefix(7,2);
        $apipassword	=	passwordEncrypter($password);
        $aUniqueRes	    = 	$db->query('INSERT INTO `tbl_user` (`name`, `address`, `photo`, `mobile`, `imei`, `addedon`, `updatedon`) VALUES (?,?,?,?,?,?,?)',$uname,$addr,$name,$mobile,$imei,$today,$today);
        $uid		    =	$db->insert_id;
        if($uid){
            $apimsg     =   'User added successfully.';
            $statuscode =   200;
            $data['userid']     =   $uid;
        }else{
            $apimsg     =   'User could not be added.';
            $statuscode =   150;
        }
    }
}
$jsdata['statuscode']   =   $statuscode;
$jsdata['status']       =   $apimsg;
$jsdata['data']         =   $data;
echo json_encode($jsdata);

?>