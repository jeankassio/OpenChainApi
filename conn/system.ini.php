<?php
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . "/PDO.class.php");

$DB = new DB(DB_HOST_LOCAL, DB_NAME_LOCAL, DB_USER_LOCAL, DB_PASS_LOCAL, DB_PORT_LOCAL);
$conn = $DB->Connect();
