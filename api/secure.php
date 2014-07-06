<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sany
 * Date: 6/30/14
 * Time: 5:30 PM
 * To change this template use File | Settings | File Templates.
 */
$imei = '';
if (isset($_POST['imei'])) {
    $imei = trim($_POST['imei']);
}
if (!$apierror && !$imei) {
    $apierror = true;
    $apimsg = "Imei is not valid";
    $statuscode = 150;
}
if (!$apierror) {
    $imeifalg = check_unique("tbl_user", "imei", $imei);
    if ($imeifalg && !$apierror) {
        $apierror = true;
        $apimsg = "imei number is not valid";
        $statuscode = 150;
    }
    $code = "SELECT * FROM tbl_user a WHERE a.imei=? and a.status=1";
    $SqlChk = $db->query($code, $imei);
    $nrC = $db->num_rows;
    if (!$nrC) {
        $apierror = true;
        $apimsg = "Invalid user.";
        $statuscode = 150;
    } else {
        $uid = $SqlChk[0]['uid'];
    }
}
