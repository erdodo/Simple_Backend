<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['default_controller'] = 'Document';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


$route['api/login'] = 'Login/login';
$route['api/register'] = 'Login/register';

$route['api/profile'] = 'Login/profile';
$route['api/auths'] = 'Login/auths';
$route['api/token-control'] = 'Tables/token_control';

$route['api/new-password'] = 'Login/newPassword';
$route['api/forget'] = 'Login/forget';
$route['api/forget-password'] = 'Login/forgetPassword';

$route['api/dogrulama'] = 'Tables/getDogrulama';
$route['api/set-dogrulama/(:any)'] = 'Tables/setDogrulama/$1';

$route['api/md5'] = 'Login/md5';

$route['api/get-auths/(:any)/(:any)'] = 'Admin/getAuthsData/$1/$2';
$route['api/set-auths/(:any)/(:any)'] = 'Admin/setAuthsData/$1/$2';


//Tables
$route['api/tables/(:any)'] = 'Tables/index/$1';
$route['api/tables/(:any)/first'] = 'Tables/first/$1';
$route['api/tables/(:any)/last'] = 'Tables/last/$1';
$route['api/tables/(:any)/(:any)/get'] = 'Tables/get/$1/$2';

$route['api/tables/(:any)/count'] = 'Tables/count/$1';

$route['api/tables/(:any)/create'] = 'Tables/create/$1';
$route['api/tables/(:any)/store'] = 'Tables/store/$1';

$route['api/tables/(:any)/(:any)/edit'] = 'Tables/edit/$1/$2';
$route['api/tables/(:any)/(:any)/update'] = 'Tables/update/$1/$2';

$route['api/tables/(:any)/(:any)/delete'] = 'Tables/delete/$1/$2';
