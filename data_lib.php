<?php

/*
database_field, column_name, width, edit_attr
width = -1, hide in table
0 - not in form
1 - hide in form
2 - read only 
3 - editable one line
4 - editable multple line
5 - write only

0x100 - key
0x200 - search %key%
0x400 - search key%
0x800 - exact match
0x1000 - order by desc
0x2000 - order by asc
0x4000 - sum field

$list_width = 800;
$edit_width = 600;
$update_table = 'customer';
$query_table = 'customer';
$def_perpage = 10;
$title = '客户管理';
$action_script = 'sell_customer.php';
$script = 'sell_customer.php';
$cust_callback = 'my_callback';
$cust_add_button = true;
$all_fields = array(
['单位编号','', -1, 0],
['customer_id', '编号',40, 0x900+2],  
['单位名称','', 160, 0x200+3],
['拼音码','', 80, 0x400+3],
['contract_id', '合同序号', 50, 3], 
['联系人','', 30, 3], 
['电话','', 40, 3], 
['地址','', 40, 3], 
['传真','', 40, 3], 
['银行账号','', 50, 3], 
['税号','', 50, 3], 
['备注','', 50, 4], 
['customer_id', '操作', 80, 0] 
);

*/

$word_add = '增加';
$word_save = '保存';
$word_edit = '修改';
$word_del = '删除';
$word_begin = '首页';
$word_prev = '前页';
$word_next = '下页';
$word_end = '末页';
$word_search = '查找';
$word_export = '导出';
$word_asearch = '精确查找';
$word_reset = '重置';
$word_recover = '恢复';
$word_recycle = '回收站';
$word_copy = '复制';
$word_archieve = '归档';
$word_go_recycle = '进入回收站';
$word_out_recycle = '离开回收站';

function db_field($key)
{
	$newk = strstr($key, '.');	
	if($newk === false)
		return $key;
	$newk = substr($newk, 1);
	return $newk;
}

function strip_db_table($table)
{
	$p = strpos($table, 'left join');	
	if($p !== False){
		$table = substr($table, 0, $p);
	}else
		return $table;
	$p = strpos($table, ' a ');	
	if($p > 0){
		$table = substr($table, 0, $p);
	}
	return $table;
}

function strip_field_name($name)
{
	if(strpos($name, '.') !== false){
		$db = substr($name, 0, strpos($name, '.'));
		$fd = strstr($name, '.');
		$fd = substr($fd, 1, strlen($fd)-1);
		if(strpos($fd, ')') || strpos($fd, ' ') | strpos($fd, ',')){
			$dbf_name = "$name";
		}else{
			$name = $fd;
			$dbf_name = "$db.$fd";
		}
	}else
		$dbf_name = "$name";
	return $dbf_name;
}


function get_recycle_table($table)
{
	$tb = strip_db_table($table);
	$rtb = "$tb"."_recycle";
	$p = strstr($table, 'join');	
	if($p === False)
		return $rtb;
	$tb = "$tb ";
	$rtb = "$rtb ";
	$ntable = str_replace($tb, $rtb, $table);
	return $ntable;
}

function get_list_range($dir, $start, $perpage, $total)
{
	switch($dir){
		case 'next':
			$start += $perpage;
			break;
		case 'prev':
			$start -= $perpage;
			break;
		case 'begin':
			$start = 0;
			break;
		case 'end':
			$start = $total - $perpage;
			break;
		case 'keep':
			break;
	}
	if($start >= $total)
		$start = $total - $perpage;
	if($start < 0)
		$start = 0;
	$_SESSION['start'] = $start;
	return $start;
}

function alloc_id($name)
{
	global $alloc_tb;
	if(!$alloc_tb)
		$alloc_tb = "param";
	$sql = "select next_id from $alloc_tb where id_name = '$name'";	
	$res = read_mysql_query($sql);
	if(($row = mysql_fetch_array($res))){
		$id = $row[0];
		$sql = "update param set next_id = next_id + 1 where id_name = '$name'";	
	}else{
		$id = 1;
		$sql = "insert into param set next_id = 2, id_name = '$name'";	
	}
	$res = update_mysql_query($sql);
	return $id;
}


function data_callback($col, &$value, $rows, &$fc, &$bk)
{
	global $login_id, $word_edit, $word_del, $word_copy, $word_recover, $word_archieve, $cust_callback;
	global $enable_copy, $enable_recycle, $enable_archieve;
	$orig_value = $value;
	if($col == $value)
		return $value;
	if($col == 'action' || $col == '操作'){
		if(strstr($rows['table_id'], 'recycle') !== false){
			if($enable_recycle){
				$link = "<input type='button' onclick='recover_line(this,$value)' value='$word_recover'></a>";
				return $link;
			}
			return '';
		}
		$link = '';
		$edit_status = -1;
		if(isset($rows['estatus']))
			$edit_status = $rows['estatus'];
		if($edit_status != 1){
			$link = "<input type='button' onclick='edit_line(this,$value)' value='$word_edit'></a>";
			$link .= "<input type='button' onclick='del_line(this,$value)' value='$word_del'></a>";
		}
		if($enable_archieve && $edit_status == 0){
			$link .= "<input type='button' onclick='change_edit_status(this,$value, 1)' value='$word_archieve'></a>";
		}
		if($enable_copy)
			$link .= "<input type='button' onclick='copy_line(this,$value)' value='$word_copy'></a>";
		$value = $link;
	}
	if($col == '日期'){
		if($value != '日期')
			$value = substr($value, 0, 10);
	}
	if(isset($cust_callback) && is_callable($cust_callback)){
		$rows['orig_value'] = $orig_value;
		$value = $cust_callback($col, $value, $rows, $fc, $bk);
	}
	return $value;

}

function get_list_sql($all_fields, $table, &$width_array, $cond, $start, $perpage)
{
	global $enable_sum;
	$key_name = 'key';
	$order_name = '';
	$sql = "select ";
	$sql .= get_get_sql($all_fields, $width_array, $key_name, $order_name, $start);
	if($order_name == '')
		$order = "$key_name asc";
	else
		$order = "$order_name desc";
	if($start == -1)
		$sql .= " from $table where $cond order by $order ";
	else if($start == -2 )
		$sql .= " from $table where $cond ";
	else
		$sql .= " from $table where $cond order by $order limit $start, $perpage";
	return $sql;
}

function get_data_fields($all_fields)
{
	$i = 0;
	$field_name = array();
	foreach($all_fields as $one){
		$field_name[$i] = $one[0];
		if($one[1] != '')
			$field_name[$i] = $one[1];
		$i += 1;
	}
	return $field_name;
}

function get_get_sql($all_fields, &$width_array, &$key_name, &$order_name, $start = 0)
{
	$i = 0;
	$sql = ' ';
	foreach($all_fields as $one){
		$dbf_name = $one[0];
		
		if($start == -2){
			if(($one[3] & 0x4000)){
				$dbf_name = " format(sum($dbf_name), 2)";
				$width_array[$i] = $one[1];
				if($i != 0)
					$sql .= ",";
				$sql .= " $dbf_name ";
				$i += 1;
			}
			continue;
		}else{
			if($i != 0)
				$sql .= ",";
			$sql .= " $dbf_name ";
		}

		if($one[3] & 0x100)
			$key_name = $dbf_name;
		if($one[3] & 0x1000)
			$order_name = $dbf_name;
		if($start != -2){
			if($one[1] != ''){	
				$name = $one[1];
				$sql .= " as '$name' ";
			}
		}
		$width_array[$i] = $one[2];
		$i += 1;
	}
	return $sql;
}

function get_set_sql($op='save', $text ='')
{
	global $all_fields;
	$i = 0;
	$sql_set = ' ';
	foreach($all_fields as $one){
		$attr = $one[3];
		if(($attr & 0xff) == 0)
			continue;
		$show_name = $one[0];
		if($one[1] != '')
			$show_name = $one[1];

		$dbf_name = strip_field_name($one[0]);
		if(isset($one[4]))
			$dbf_name = $one[4];

		$value = urldecode(get_url_var("$show_name", ''));
		if($value == '')
			$value = urldecode(get_url_var("$dbf_name", ''));
		if($op == 'save'){
			if(($attr & 0xff) == 2)
				continue;
			$sql_set = $sql_set != ' ' ? "$sql_set,":$sql_set;
			$sql_set .= " $dbf_name = '$value' ";
		}else if($op == 'add'){
			if($attr & 0x100){
				continue;
			}else if(($attr & 0xff) == 2){
				continue;
			}else{
				$sql_set = $sql_set != ' ' ? "$sql_set,":$sql_set;
				$sql_set .= " $dbf_name = '$value' ";
			}
		}else if($op == 'search'){
			if($value != ''){
				$sql_set = $sql_set != ' ' ? "$sql_set and " : $sql_set;
				if(is_numeric($value))
					$sql_set .= " $dbf_name = '$value' ";
				else
					$sql_set .= " $dbf_name like '%$value%' ";
			}
		}else if($op == 'qsearch'){
			if($attr & 0x800){
				if(is_numeric($text)){
						$sql_set = $sql_set != ' ' ? "$sql_set or " : $sql_set;
						$sql_set .= " $dbf_name = '$text' ";
				}
			}else if($attr & 0x200){
				$sql_set = $sql_set != ' ' ? "$sql_set or " : $sql_set;
				$sql_set .= " $dbf_name like '%$text%' ";
			}else if($attr & 0x400){
				$sql_set = $sql_set != ' ' ? "$sql_set or " : $sql_set;
				$sql_set .= " $dbf_name like '$text%' ";
			}
		}
		$i += 1;
	}
	return $sql_set;
}

function copy_db_line($stable, $dtable, $key, $value, $new_key=0, $is_item=false)
{
	global $dbname;
	$sql = " show columns from $stable ";
	$result = read_mysql_query($sql);
	if (!$result) {
	    echo 'Could not run query: ' . mysql_error();
		return false;
	}
	if (mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_assoc($result)) {
			$f = $row['Field'];
			if(strpos($f, ' ') !== false)
				$f = "`$f`";
			if(strpos($f, '-') !== false)
				$f = "`$f`";
			$frows[] = array($f,$row['Key']);
		}
	}
	$sql = " insert into $dtable ";
	$sql .= "(";
	$sql_f = '';
	$sql_t = '';
	foreach($frows as $one_field){
		if($stable == $dtable && $new_key == 0)
			continue;
		if($stable == $dtable && $one_field[1] == 'PRI' && $is_item)
			continue;
		$ffield = $one_field[0];
		$tfield = $one_field[0];
		if(isset($one_field[4])){
			$tfield = $one_field[4];
			$ffield = $one_field[4];
		}
		if($new_key != 0 && $tfield == db_field($key)){
			$ffield = $new_key;
		}
		if($sql_f != ''){
			$sql_t .= ", $tfield";
			$sql_f .= ", $ffield";
		}else{
			$sql_t .= " $tfield";
			$sql_f = " $ffield ";
		}
	}
	$sql .= " $sql_t ) ";
	$sql .= " select ";
	$sql .= " $sql_f from $stable where $key = '$value' ";
	update_mysql_query($sql);
	$rows = mysql_affected_rows();
	return $rows;
}

function get_db_fields($sql)
{
	$result = read_mysql_query($sql);
	for ($i = 0; $i < mysql_num_fields($result); ++$i) {
		$field = mysql_field_name($result, $i);
		$field_name[] = $field;
	}
	return $field_name;
}

$permit_array = array(
	'list'=>array(1, '查询', 0),
	'add'=>array(2, '添加',  1),
	'copy'=>array(2, '复制', 1),
	'save'=>array(4, '修改', 1),
	'archieve'=>array(4, '归档', 1),
	'delete'=>array(8, '删除', 1),
	'recycle'=>array(8, '删除', 1),
	'export'=>array(16, '导出', 1),
	'recover'=>array(8, '恢复', 1),
);

function check_permission($action, $permit)
{
	global $permit_array;
	global $cust_permission_check;
	global $update_table;
	foreach($permit_array as $act=>$perm){
		if($action == $act && !($permit & $perm[0])){
			if($perm[2])
				print "你没有$perm[1]权限!";
			return false;
		}
	}
	if(is_callable($cust_permission_check))
		return $cust_permission_check($update_table, $action, $permit);
	return true;
}

function reset_status()
{
	$_SESSION['start'] = 0;
	$_SESSION['text'] = '';
	$_SESSION['search_cond'] = '';
	$_SESSION['cust_sql'] = '';
}

function list_data($start='', $export=false)
{
	global $list_width, $word_begin, $word_prev, $word_next, $word_end, $action, $permit, $login_id;
	global $update_table, $query_table, $all_fields, $cond, $def_perpage, $cust_sql, $doc_title, $enable_show_recycle, $word_recycle; 
	global $enable_sum, $cust_sum_sql, $cust_sum_field, $enable_record_owner, $enable_deny_list; 
	$recycle = false;

	if(!isset($def_perpage))
		$def_perpage = 10;
	$perpage = get_persist_var('perpage', $def_perpage);
	$dir = get_url_var('dir', 'keep');
	if($start == -1)
		$dir = 'end';
	if($start == '')
		$start = get_persist_var('start', 0);
	//print("{$_SESSION['cust_sql']}<br>");
	$cust_sql = get_pre_var('cust_sql', $cust_sql, '');
	$text = get_persist_var('text', '');
	$cond = get_persist_var('search_cond', ' 1 ');

	if($query_table)
		$table = $query_table;
	else
		$table = $update_table;
	if(isset($enable_show_recycle) && $enable_show_recycle){
		$recycle = get_persist_var('recycle', false);
		if($recycle)
			$table = get_recycle_table($table);
	}

	if($cust_sql != '') {
		$sql = $cust_sql;
		$_SESSION['cust_sql'] = $cust_sql;
	}else{
		if($cond == '')
			$cond = ' 1 ';
		if($text != ''){
			$search_cond = get_set_sql('qsearch', $text);
			$cond .= " and ($search_cond) ";
		}
		if($dir == 'search'){
			$search_cond = get_set_sql('search');
			$cond = " ($search_cond) ";
			$_SESSION['search_cond'] = $cond;
		}
		$sql = " select * from $table where $cond";
	}

	$result = read_mysql_query($sql);
	$total = mysql_num_rows($result);
	$start = get_list_range($dir, $start, $perpage, $total);

	$width_array = array();

	
	if(!check_permission($action, $permit)){
		if($enable_record_owner){
			$cond .= " and user_id = '$login_id' ";
		}else if($enable_deny_list){
			print "你没有查询权限";
			return;
		}
	}
	if(isset($cust_sql) && $cust_sql != ''){
		$sql = "$cust_sql limit $start, $perpage";
	}else{
		if(!$export){
			$sql = get_list_sql($all_fields, $table, $width_array, $cond, $start, $perpage);
		}else
			$sql = get_list_sql($all_fields, $table, $width_array, $cond, -1, $perpage);
	}


	$p_disabled = '';
	$n_disabled = '';
	if($start + $perpage >= $total - 1)
		$n_disabled = 'disabled';
	if($start == 0)
		$p_disabled = 'disabled';
	if(!$export){
		print("<input id='button_begin'  name='begin' $p_disabled type='button' onclick='switch_page(\"begin\");' value='$word_begin'></input>");
		print("<input id='button_prev' name='prev'  $p_disabled type='button' onclick='switch_page(\"prev\");' value='$word_prev'></input>");
		print("<input id='button_next'  name='next'  $n_disabled type='button' onclick='switch_page(\"next\");' value='$word_next'></input>");
		print("<input id='button_end' name='end'   $n_disabled type='button' onclick='switch_page(\"end\");' value='$word_end'></input>");
	}
	$end = $start + $perpage - 1;
	if($end > $total -1)
		$end = $total -1;
	$start += 1;
	$end += 1;
	if(!$export){
		if($recycle)
			print("&nbsp;$word_recycle");
		print("&nbsp;$start-$end/$total");
		print("&nbsp;查找条件:$text");
		$table_id = "tb_$table";
		$format = 1;
		show_table_by_sql("$table_id", '', $list_width, $sql, array(), $width_array, 'data_callback', $format);
	}else{
		$dtitle = get_url_var('export_title', 'export');
		if($dtitle == 'export'){
			$dtitle = isset($doc_title)?$doc_title:$dtitle;
		}
		$file_name = get_url_var('export_filename', '');
		$today = get_today();
		if($file_name == "")
			$file_name = "$dtitle-$today.xls";
		$i = 0;
		foreach($width_array as $w){
			$width_array[$i] /= 8;
			$i += 1;
		}
		export_excel_by_sql($sql, $file_name, $dtitle, $width_array);
	}

	if(!$export){
		print("<input id='button_add'  name='begin' $p_disabled type='button' onclick='switch_page(\"begin\");' value='$word_begin'></input>");
		print("<input id='button_save' name='prev'  $p_disabled type='button' onclick='switch_page(\"prev\");' value='$word_prev'></input>");
		print("<input id='button_add'  name='next'  $n_disabled type='button' onclick='switch_page(\"next\");' value='$word_next'></input>");
		print("<input id='button_save' name='end'   $n_disabled type='button' onclick='switch_page(\"end\");' value='$word_end'></input>");
		if($enable_sum){
			if($cust_sum_field)
				$sum_field = $cust_sum_field;
			else
				$sum_field = array();
			if($cust_sum_sql)
				$sum_sql = $cust_sum_sql;
			else
				$sum_sql = get_list_sql($all_fields, $table, $sum_field, $cond, -2, $perpage);
			$res = read_mysql_query($sum_sql);
			$i = 0;
			$rows = mysql_fetch_array($res);
			print("&nbsp;");
			foreach($sum_field as $fld){
				$value = $rows[$i];
				$fld = $sum_field[$i];
				print("&nbsp;$fld:$value");
				$i += 1;
			}
		}
	}

}


?>
