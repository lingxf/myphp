<?php
include_once 'data_lib.php';

$sql_set = "";
$i = 0;
global $alloc_tb;
if(!$alloc_tb)
	$alloc_tb = "param";
$key = get_url_var('key', '');
$key_name = get_url_var('key_name', 'key_name');
$set_sql = get_set_sql($action);
$reset = get_url_var('reset', '');
if($reset == 'yes')
	reset_status();

if(!check_permission($action, $permit))
	return;

if($action == 'archieve'){
	$table = $update_table;
	$sql = "update $table set edit_status = 1 where $key_name = '$key'";
	update_mysql_query($sql);
	$row = mysql_affected_rows();
	print "ok归档 $row 条记录";
	if(isset($item_table)&&$item_table){
		$sql = "update $item_table set edit_status = 1 where $key_name = '$key'";
		update_mysql_query($sql);
		$row2 = mysql_affected_rows();
		print "$row2 条子记录";
	}
}else if($action == 'archieve'){
	$table = $update_table;
	$sql = "delete from $table where $key_name = '$key'";
	update_mysql_query($sql);
	print "ok删除 $row 条记录";
}else if($action == 'recycle'){
	$table = $update_table;
	$key_name = db_field($key_name);
		
	if(($row = copy_db_line($table, "$table"."_recycle", $key_name, $key))){
		$sql = "delete from $table where $key_name = '$key'";
		update_mysql_query($sql);
		print "ok回收 $row 条记录";
		if(isset($item_table)&&$item_table){
			if(($row = copy_db_line($item_table, "$item_table"."_recycle", $key_name, $key))!== false){
				$sql = "delete from $item_table where $key_name = '$key'";
				update_mysql_query($sql);
				print "\n回收 $row 条子记录";
			}
		}	
	}
}else if($action == 'recover'){
	$table = $update_table;
	$key_name = db_field($key_name);
	$rtb = $table."_recycle";
	if(($row = copy_db_line($rtb, $table, $key_name, $key))){
		$sql = "delete from $rtb where $key_name = '$key'";
		update_mysql_query($sql);
		print("ok恢复 $row 条记录");
		if(isset($item_table)&&$item_table){
			$rtb_item = "$item_table"."_recycle";
			if(($row = copy_db_line($rtb_item, $item_table, $key_name, $key))){
					$sql = "delete from $rtb_item where $key_name = '$key'";
					update_mysql_query($sql);
					print "\n回收 $row 条子记录";
			}
		}	

	}
}else if($action == 'copy'){
	$table = $update_table;
	$key_name = db_field($key_name);
		
	if(isset($cust_pre_copy) && is_callable($cust_pre_copy)){
		if(!$cust_pre_copy($key))
			return;
	}
	$new_key = alloc_id($key_name);
	if(($row = copy_db_line($table, $table, $key_name, $key, $new_key))){
		print "ok拷贝 $row 条记录";
		if(isset($item_table)&&$item_table){
			if(($row = copy_db_line($item_table, $item_table, $key_name, $key, $new_key, true))!== false){
				print "\n拷贝 $row 条子记录";
			}
			$nid = "$table"."_".$new_key;
			$oid = "$table"."_".$key;
			$sql = "insert into $alloc_tb (id_name, next_id) select '$nid', next_id from param where id_name = '$oid' ";
			$res = update_mysql_query($sql);
		}	
		if(isset($cust_copy) && is_callable($cust_copy)){
			$cust_copy($key, $new_key);
		}
		
	}
}else if($action == 'save'){
	if($key == 0 && !isset($allow_zero_key)){
		print("Can not save with id=0<br>");
	}else{
		$sql = "update $update_table set "; 
		$sql .= $set_sql;
		$sql .= "where $key_name = '$key'";
		update_mysql_query($sql);
		print("ok");
		list_data();
	}
}else if($action == 'add'){
	$sql = "insert into $update_table set ";
	$sql .= $set_sql;
	update_mysql_query($sql);
	print('ok');
	list_data(-1);
}else if(preg_match('/page|search/', $action))
	list_data();
else if($action == 'export'){
	$export_check = get_url_var('export_check', false);
	if($export_check)
		print 'ok';
	else
		list_data(0, true);
}

?>
