<?php

$NomeLogErro = "/error_general.txt"; 
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . $NomeLogErro);
error_reporting(E_ALL);

/*
used in system.ini.php
*/

define("DB_HOST_LOCAL", "localhost");
define("DB_USER_LOCAL", ""); //database_user
define("DB_PASS_LOCAL", ""); //database_password
define("DB_NAME_LOCAL", ""); //database_name
define("DB_PORT_LOCAL", "3306");


define("USER_BITCOIN", ""); //RPC username
define("PASS_BITCOIN", ""); //RPC password
define("SERVER_RPC", "localhost");
define("PORT_RPC", "8332");
define("PATH_WALLET", "wallet/");
