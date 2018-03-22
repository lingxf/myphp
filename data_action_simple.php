<?php
include_once 'myphp/common.php';
include_once 'myphp/data_lib.php';
include_once 'db_connect.php';
$action = get_url_var('action', '');
$table_name = get_url_var('table_name', '');
switch($action){
	case 'page':
		list_data_simple($table_name);
		break;
}
?>
