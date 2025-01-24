<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
set_time_limit(0);
define('MYACTIVERECORD_CONNECTION_STR', 'mysql://root:password@localhost/test');
define('APP_ROOT',dirname(__FILE__));
define('FILES_BASE',dirname(dirname(__FILE__)) . '/files');
//define('MYACTIVERECORD_LOG_SQL_TO',"./sql.log");
//define('MYACTIVERECORD_CACHE_SQL', false);
//define('PAGER_LIMIT',10);
//define('CACHE_DIRECTORY','/path/to/folder');
error_reporting(E_ALL);
ini_set('display_errors',1);
mb_internal_encoding("utf-8");
require_once('MyActiveRecord.0.4.php');
require_once('helpers.inc.php');
ActiveRecord::Query('SET CHARACTER SET utf8');
$flash = $out = '';
$self = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
