<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sany
 * Date: 7/6/14
 * Time: 11:49 PM
 * To change this template use File | Settings | File Templates.
 */
include_once('../include/function.php');

$apierror = false;
$apimsg = '';
$statuscode = '';
$statusmsg = '';
$apicode = '';
$previd = '';
$imei = '';
$today = time();
$prizeid = trim($_POST['prizeid']);

if (!$apierror && (!$prizeid || $prizeid == '')) {
    $apierror = true;
    $apimsg = "Please select a valid prize.";
}
if (!$apierror) {
    include '../common.php';
    include 'secure.php';
    $qfalg = check_unique("tbl_prize", "prizeid", $prizeid);
    if (!$apierror && $qfalg) {
        $apierror = true;
        $apimsg = "Please select a valid prize.";
    }
    if (!$apierror) {
        $code = "SELECT * FROM tbl_prize where status=? and prizeid=?";
        $SqlChk = $db->query($code, 1, $prizeid);
        $nrC = $db->num_rows;
        if ($nrC) {
            $prizepoint = $SqlChk[0]['points'];
            $prize = $SqlChk[0]['prize'];
            $subcode = "SELECT * FROM tbl_points a WHERE a.uid=?";
            $subSqlChk = $db->query($subcode, $uid);
            $subnrC = $db->num_rows;
            if ($subnrC) {
                if ($subSqlChk[0]['points'] >= $prizepoint) {
                    $db->query('UPDATE tbl_points SET `points`=? WHERE uid=?', ($subSqlChk[0]['points'] - $prizepoint), $uid);
                    $statuscode = 200;
                    $apimsg = "Prize " . $prize . " redeemed successfully";
                } else {
                    $statuscode = 150;
                    $apimsg = "Insufficient Balance";
                }
            } else {
                $statuscode = 150;
                $apimsg = "Invalid points";
            }
        } else {
            $statuscode = 150;
            $apimsg = "No prize present";
        }

    }
}
$jsdata['statuscode'] = $statuscode;
$jsdata['status'] = $apimsg;
echo json_encode($jsdata);