<?php

/**
 * TraxSelector - 2014
 */
require_once ROOT . DS . 'lib' . DS . 'db' . DS . '__init.php';
require_once ROOT . DS . 'lib' . DS . 'db' . DS . 'collection.php';
require_once ROOT . DS . 'lib' . DS . 'common' . DS . '__init.php';
require_once ROOT . DS . 'lib' . DS . 'auth'. DS . '__init.php';
require_once ROOT . DS . 'lib' . DS . 'session' . DS . '__init.php';
require_once ROOT . DS . 'lib' . DS . 'logging' . DS . '__init.php';

require_once ROOT . DS . 'lib' . DS . 'codes.php';

define('debug', 'develop');
define('uploadDir', __DIR__.'/..'.'/files/');
define('verbose', false);
$confArray = array();
$confArray['db'] = array();
$confArray['db']['user'] = 'traxselector';
$confArray['db']['password'] = 'fcUP6jHSLYK2xd3X';
$confArray['db']['host'] = 'localhost';
$confArray['db']['name'] = 'TraxSelector';

$db = new db($confArray['db']);
$common = new common();
$session = new session('TraxSelector');
$logging = new logging();
$auth = new authentication();
?>
