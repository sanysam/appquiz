<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sany
 * Date: 6/30/14
 * Time: 10:37 AM
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
        $code = "SELECT * FROM tbl_question a LEFT JOIN tbl_question_response b  ON a.qid=b.qid WHERE a.validfrom<=? and a.validto>=? and a.status=1";
        $SqlChk = $db->query($code, $today, $today);
        $nrC = $db->num_rows;
        if ($nrC) {
            $i = 0;
            foreach ($SqlChk AS $role_result) {
                if ($previd != $role_result["qid"]) {
                    $i++;
                    $jsdata['data'][$i]['question'] = $role_result["question"];
                    $jsdata['data'][$i]['qid'] = $role_result["qid"];
                    $jsdata['data'][$i]['point'] = $role_result["point"];
                    $jsdata['data'][$i]['depoint'] = $role_result["depoint"];
                    $previd = $role_result["qid"];
                }

                $jsdata['data'][$i]['ans']['rid'][] = $role_result["rid"];
                $jsdata['data'][$i]['ans']['response'][] = $role_result["response"];
            }
            $statuscode = 200;
            $apimsg = "successfull";
        } else {
            $apierror = true;
            $statuscode = 150;
            $apimsg = "No records";
        }
    }
}
$jsdata['statuscode'] = $statuscode;
$jsdata['status'] = $apimsg;
echo json_encode($jsdata);