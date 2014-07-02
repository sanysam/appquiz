<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sany
 * Date: 6/30/14
 * Time: 8:10 PM
 * To change this template use File | Settings | File Templates.
 */
include_once('../include/function.php');

$apierror	 	 	=	false;
$apimsg	 		 	=	'';
$statuscode		 	=	'';
$statusmsg			=	'';
$apicode			=	'';
$previd 			=	'';
$imei    			=	'';
$today              =   time();

$qid                =   trim($_POST['qid']);
$rid                =   trim($_POST['rid']);

if(!$apierror && (!$qid|| $qid=='')){
    $apierror	    = 	true;
    $apimsg		    = 	"Please select a question.";
}if(!$apierror && (!$rid|| $rid=='')){
    $apierror	    = 	true;
    $apimsg		    = 	"Please choose an answer.";
}
if(!$apierror){
    include '../common.php';
    $mobilefalg     = check_unique("tbl_user","qid",$qid);
    $imeifalg       = check_unique("tbl_user","rid",$rid);
}