<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sany
 * Date: 6/30/14
 * Time: 5:30 PM
 * To change this template use File | Settings | File Templates.
 */
$imei='';
if(isset($_POST['imei'])){
    $imei               =   trim($_POST['imei']);
}
if(!$apierror && !$imei){
    $apierror	 =	true;
    $apimsg 	 = 	"Imei is not valid";
}
if(!$apierror){
    $imeifalg       = check_unique("tbl_user","imei",$imei);
    if($imeifalg && !$apierror){
        $apierror	 =	true;
        $apimsg 	 = 	"imei number is not valid";
    }
}
