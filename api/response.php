<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sany
 * Date: 6/30/14
 * Time: 8:10 PM
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

$qid = trim($_POST['qid']);
$rid = trim($_POST['rid']);

if (!$apierror && (!$qid || $qid == '')) {
    $apierror = true;
    $apimsg = "Please select a question.";
}
if (!$apierror && (!$rid || $rid == '')) {
    $apierror = true;
    $apimsg = "Please choose an answer.";
}
if (!$apierror) {
    include '../common.php';
    include 'secure.php';
    $qfalg = check_unique("tbl_question", "qid", $qid);
    $rfalg = check_unique("tbl_question_response", "rid", $rid);
    if (!$apierror && $qfalg) {
        $apierror = true;
        $apimsg = "Please select a valid question.";
    }
    if (!$apierror && $rfalg) {
        $apierror = true;
        $apimsg = "Please select a valid response.";
    }
    if (!$apierror) {
        $code = "SELECT * FROM tbl_question a WHERE a.qid=? and a.validfrom<=? and a.validto>=? and a.status=1";
        $SqlChk = $db->query($code, $qid, $today, $today);
        $nrC = $db->num_rows;
        if (!$nrC) {
            $apierror = true;
            $apimsg = "Please select a valid question(Invalid date).";
        }
    }
    if (!$apierror) {
        $code = "SELECT * FROM tbl_question a WHERE a.qid=?";
        $SqlChk = $db->query($code, $qid);
        $nrC = $db->num_rows;
        if ($nrC) {
            if ($rid == $SqlChk[0]['rid']) {
                $db->query('INSERT INTO tbl_user_response (`uid`, `qid`, `rid`, `status`, `points`, `addedon`) values (?,?,?,?,?,?)', $uid, $qid, $rid, 1, $SqlChk[0]['point'], $today);
                $anspts = $SqlChk[0]['point'];
            } else {
                $db->query('INSERT INTO tbl_user_response (`uid`, `qid`, `rid`, `status`, `points`, `addedon`) values (?,?,?,?,?,?)', $uid, $qid, $rid, 0, $SqlChk[0]['depoint'], $today);
                $anspts = $SqlChk[0]['depoint'] * (-1);
            }
            $subcode = "SELECT * FROM tbl_points a WHERE a.uid=?";
            $subSqlChk = $db->query($subcode, $uid);
            $subnrC = $db->num_rows;
            if ($subnrC) {
                $db->query('UPDATE tbl_points SET `points`=? WHERE uid=?', ($anspts + $subSqlChk[0]['points']), $uid);
            } else {
                $db->query('INSERT INTO tbl_points (`uid`, `points`) values(?,?)', $uid, $anspts);
            }

            $statuscode = 200;
            $apimsg = "successfull";
        } else {
            $statuscode = 150;
            $apimsg = "unsuccessfull";
        }
    }
}
$jsdata['statuscode'] = $statuscode;
$jsdata['status'] = $apimsg;
echo json_encode($jsdata);
