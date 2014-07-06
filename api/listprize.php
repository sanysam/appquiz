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
        $code = "SELECT * FROM tbl_prize where status=?";
        $SqlChk = $db->query($code, 1);
        $nrC = $db->num_rows;
        if ($nrC) {
            $i = 0;
            foreach ($SqlChk AS $role_result) {
                $i++;
                $jsdata['data'][$i]['prizeid'] = $role_result["prizeid"];
                $jsdata['data'][$i]['prize'] = $role_result["prize"];
                $jsdata['data'][$i]['points'] = $role_result["points"];
            }
            $statuscode = 200;
            $apimsg = "successfull";
        } else {
            $statuscode = 150;
            $apimsg = "No prize left";
        }

    }
}
$jsdata['statuscode'] = $statuscode;
$jsdata['status'] = $apimsg;
echo json_encode($jsdata);