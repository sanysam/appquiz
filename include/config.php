<?php 
if (!isset($proc)) header('Location: index.php');
define('TIEMPO' ,TRUE); // Show page generation time in footer? NOT A PART OF THIS CLASS!
if (TIEMPO == true) $q = time() + microtime();
define('MEMORY' ,TRUE); // Show memory usage in footer? NOT A PART OF THIS CLASS!

/* ************************************************************************** */
/*                        MAIN CONFIGURATION                                  */
/* ************************************************************************** */
define('CHARSET','UTF-8');
define('DB_SHOW_ERRORS',TRUE); // Show DB connection error to users?
define('DB_DATASIZE',FALSE); // NOT recommended for large queries! Haves an significant impact on speed!!
define('DB_LOG_XML',TRUE); // Log all database activity to XML?
define('DB_URL_XML','/var/www/log.xml'); // Location of XML file, recommended place is outside the public_html directory!
define('DB_CACHE_LOCATION','/var/www/cache/'); // Location of cache file(s), with trailing slash
define('DB_CACHE_EXPIRE','120'); // DB cache file expiricy, in seconds


    define('MYSQL_HOST','localhost'); // your db's host
    define('MYSQL_PORT',3306);        // your db's port
    define('MYSQL_USER','root'); // your db's username
    define('MYSQL_PASS', ''); // your db's password
    define('MYSQL_NAME','appquiz');   // your db's database name
    define('DBCHAR','utf8'); // The DB's charset
    define('ACCESSIDKEYPATH','/var/www/paykeys/'); // The DB's charset

/* ************************************************************************** */
/*                        END MAIN CONFIGURATION                              */
/* ************************************************************************** */

define('LOG_PATH_CORE',$_SERVER['DOCUMENT_ROOT'].'/settlement/logs/');
define('CORE_UPLOADPATH',$_SERVER['DOCUMENT_ROOT'].'/settlement/uploads2/');
define('MANUAL_UPLOADPATH',$_SERVER['DOCUMENT_ROOT'].'settlement/uploads/');
define('CORE_UPLOADURL',"http://localhost/settlement/core/core_upload.php");
define('BANKLIST_API',"http://localhost/settlement/core/jsonfile.php");

define('ROLLING_RESERVE_PERCENTAGE', 5); //API ==>CORE
define('ROLLING_RESERVE_DAYS', 30); //API ==>CORE
define('REFUND_DAYS', 30); //API ==>CORE
define('RISK_DAYS', 30); //API ==>CORE
define('CHARGEBACK_DAYS', 30); //API ==>CORE
define('SERVICE_TAX', 12.36); //API ==>CORE

?>