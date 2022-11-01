<?php
defined('BASEPATH') or exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;
$user_name = "";
$password = "";
$db_name = "";
$host = "localhost";
if ($_SERVER['SERVER_NAME'] == 'simple.fungiturkey.org') {
	$user_name = "fungitu2_root";
	$password = "FungiTurkey112233.";
	$db_name = "fungitu2_api";
	$host = "localhost";
} else {
	$user_name = "root";
	$password = "";
	$db_name = "Simple";
	$host = "localhost";
}
$db['default'] = array(
	'dsn'	=> '',
	'hostname' => $host,
	'username' => $user_name,
	'password' => $password,
	'database' => $db_name, //'fungitu2_api',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

$db['information_schema'] = array(
	'dsn'	=> '',
	'hostname' => $host,
	'username' => $user_name,
	'password' => $password,
	'database' => 'information_schema',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
