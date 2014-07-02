<?php
$proc 	= TRUE;

require_once "include/config.php";
require_once "include/db_mysqli.class.php"; 
$db 	= new db_mysqli();
include('include/functiondb.php');

$IP			= 	$_SERVER["REMOTE_ADDR"];

?>