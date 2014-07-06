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
if (!$apierror) {
    include '../common.php';
    include 'secure.php';
    if (!$apierror) {
        $code = "SELECT * FROM tbl_points where uid=?";
        $SqlChk = $db->query($code, $uid);
        $nrC = $db->num_rows;
        if ($nrC) {
            $jsdata['data']['points'] = $SqlChk[0]["points"];
        } else {
            $jsdata['data']['points'] = 0;
        }
        $statuscode = 200;
        $apimsg = "successfull";
    }
}
$jsdata['statuscode'] = $statuscode;
$jsdata['status'] = $apimsg;
echo json_encode($jsdata);