<?php
include_once 'db_connect.php';
include_once 'common.php';
/*
	daily report and manual tracking system
	copyright Xiaofeng(Daniel) Ling<xling@qualcomm.com>, 2012, Aug.
*/
function print_td($text, $width='', $color='', $background='', $script='')
{
    $td = "<td width=$width style='width:$width pt;".
		"background:$background;" .
		"color:$color;" .
		"padding:0cm 5.4pt 0cm 5.4pt;height:33.0pt' $script>";
	$td .= "$text";
	$td .= "</td>";
	print $td;
}

function print_th($width1, $width2, $name){
	print("<td width=$width1 valign=top style='width:$width2 pt;" . 
		"border-top:windowtext 1.5pt;border-left:windowtext 1.5pt;" . 
		"border-bottom:silver 1.0pt;border-right:silver 1.0pt;border-style:solid;" .
		"background:#CCFFFF;padding:0cm 5.4pt 0cm 5.4pt;height:33.0pt'>" .
		"<p class=MsoNormal align=center style='text-align:center'>" .
		"<b><span style='font-size:8.0pt;font-family:\"Arial\",\"sans-serif\";color:black'>" .
		"$name<o:p></o:p></span></b></p></td>");
}

function print_tdlist($tdlist)
{
	foreach($tdlist as $tdc)
	{
		print("<td>$tdc</td>"); 
	}
}

function print_thlist($tdlist)
{
	foreach($tdlist as $tdc)
	{
		print("<th>$tdc</th>"); 
	}
}

function print_tbline($tdlist)
{
	print("<tr>");
	print_tdlist($tdlist);
	print("</tr>");
}

function print_tditem($text, $width='', $color='', $background='', $script='')
{
    $td = "<td width=$width style='width:$width pt;".
		"background:$background;" .
		"color:$color;" .
		"padding:0cm 5.4pt 0cm 5.4pt;height:33.0pt' $script>";
	$td .= "$text";
	$td .= "</td>";
	print $td;
}


function print_table_head($table_name='', $tr_width=800, $background=0xffffff)
{
	$table_head = "<table id='$table_name' width=600 class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>";
	print($table_head);
}

function print_sql_table_head($id, $width, $field_name, $field_width)
{
    print("<table id='$id' class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 width=1384 style='width:$width.0pt;margin-left:20.5pt;border-collapse:collapse'>");
    print("<tr style='height:33.0pt'>");

	//$table = mysql_field_table($result, $i);
	$wn = count($field_width);
	$i = 0;
	foreach($field_name as $field){
		if($i < $wn && $field_width[$i] != 0){
			$width = $field_width[$i];
			$attr="width=$width";
		}else
			$attr = '';
		print("<th $attr>$field</th>"); 
		$i++;
	}
    print("</tr>");
}

function show_table_by_sql($id, $db, $width, $sql, $field_name=array(), $field_width=array(), $callback='', $format=0)
{
	$ret=mysql_select_db($db);

	$result = read_mysql_query($sql);
	$columns = count($field_name);
	if( $columns == 0){
		for ($i = 0; $i < mysql_num_fields($result); ++$i) {
			$field = mysql_field_name($result, $i);
			$field_name[] = $field;
		}
	}
	$sum = array();
	if($format == 1){
		$rows = mysql_num_rows($result);
		print("Total:$rows");
	}

	print_sql_table_head($id, $width, $field_name, $field_width);
	$fields_num = mysql_num_fields($result);
	while($row=mysql_fetch_array($result)){
        print("<tr style='height:33.0pt'>");
		for ($i = 0; $i < $fields_num; ++$i) {
			$field = mysql_field_name($result, $i);
			$value = $row[$i];
			$sum[$field] = isset($sum[$field]) ?$sum[$field]+$value:$value;
			if($callback != '')
				$value = call_user_func($callback, $field, $value, $row);
			print("<td>$value</td>"); 
		}
		print("</tr>");
	}
	if($callback != '')
		$sum = call_user_func($callback, 'sum', $sum);
	if($format == 0){
		print("<tr style='height:33.0pt'>");
		for ($i = 0; $i < $fields_num; ++$i) {
			$field = mysql_field_name($result, $i);
			$tt = $sum[$field];
			if($i == 0){
				print("<th>Total</th>"); 
				continue;
			}
			if($tt == 0)
				$tt = "";
			print("<th>$tt</th>"); 
		}
		print("</tr>");
	}
	print("</table>");
}

function print_table_head_case($id, $width, $field_name, $field_width, $callback='')
{
    print("<table id='$id' class=MsoNormalTable border=1 cellspacing=0 cellpadding=1 width=1384 style='width:$width.0pt;margin-left:20.5pt;border-collapse:collapse'>");
    print("<tr style='height:33.0pt'>");

	//$table = mysql_field_table($result, $i);
	$wn = count($field_width);
	$i = 0;
	$value = true;
	if($callback != '')
		$field_name = call_user_func($callback, $field_name, '', 0);
	foreach($field_name as $field){
		if($i < $wn && $field_width[$i] != 0){
			$width = $field_width[$i];
		}else
			$width = '';
		if($value != false)
			print_th($width, $width, $field); 
		$i++;
	}
    print("</tr>");
}

function show_table_by_month($id, $db, $width, $sql, $name='Field\Month', $col=2,  $mstart=1, $nm=12, $callback='', $format=0, $kpi='count') {

	$count_array = Array();
	$count_array2 = Array();
	$ret=mysql_select_db($db);
	$result = read_mysql_query($sql);
	while($row=mysql_fetch_array($result)){
		$key = $row[0];
		$count_array[$key][0] = $key;
		$month = $row[1];
		$value = $row[$col];
		if($month)
			$count_array[$key][$month] = $value;
	}

	$colnames = Array($name, 20);
	for($m = $mstart; $m < $mstart + $nm; $m++){
		$month = $m % 12 == 0 ? 12: $m % 12;
		$colnames[] = $month;
		$colnames[] = 20;
	}

	foreach($count_array as $key => $data){
		$count_array2[$key][0] = $key;
		for($m = $mstart; $m < $mstart + $nm; $m++){
			$month = $m % 12 == 0 ? 12: $m % 12;
			$count_array2[$key][] = $data[$month]; 
		}
	}

	if($kpi == 'count')
		show_table_by_array($table, $count_array2, $colnames, $nm + 1, 1);
	else
		show_table_by_array($table, $count_array2, $colnames, $nm + 1, 3);
}


function print_td_case($value, $width, $background='white', $color='black', $script='', $span=true){

	$column = '';
	if($width != '')
		$wstr = "width:$width"."pt;";
	else
		$wstr = '';
    $td = "<td align='left' style='" .$wstr."border-top:none;border-left:solid windowtext 1.5pt;" .
		"border-bottom:solid silver 1.0pt;border-right:solid silver 1.0pt;" .
		"background:$background;color:$color;" .
		"padding:0cm 5.4pt 0cm 5.4pt;height:33.0pt'i $script>";
	if($column != 'Action'){
		$td .= "<p class=MsoNormal align=left style='text-align:left'>";
		$td .= "<span style='font-size:8.0pt;font-family:\"Arial\",\"sans-serif\";color:$color'>"; 
	}
	$td .= "$value";
	if($span)
		$td .= "<o:p></o:p></span></p>";
	$td .= "</td>";
	print $td;
}


function show_table_by_sql_case($id, $db, $width, $sql, $field_name=array(), $field_width=array(), $callback_row='', $callback='' )
{

	$bkcolor = array('#B8CCE4', '#DCE6F1');
	$ret=mysql_select_db($db);

	$result = read_mysql_query($sql);
	$columns = count($field_name);
	if( $columns == 0){
		for ($i = 0; $i < mysql_num_fields($result); ++$i) {
			$field = mysql_field_name($result, $i);
			$field_name[] = $field;
		}
	}
	$sum = array();
	print_table_head_case($id, $width, $field_name, $field_width, $callback_row);
	$fields_num = mysql_num_fields($result);
	$count = 0;
	$fcline = 'black';
	while($row=mysql_fetch_array($result)){
        print("<tr style='height:33.0pt'>");
		$bkline = $bkcolor[$count % 2 ];
		if($callback_row != ''){
			$color = call_user_func($callback_row, $field_name, $row, 1);
			$bkline = $color[0];
			$fcline = $color[1];
		}
		if($callback_row != '')
			$rowp = call_user_func($callback_row, $field_name, $row, 3);
		else{
			foreach($field_name as $field){
				$rowp[$field] = $row[$field];
			}
		}
		foreach($rowp as $field => $value){
			$bk = $bkline;
			$fc = $fcline;
			if($callback != ''){
				$rback = call_user_func_array($callback,array($field, &$value, &$fc, &$bk));
				/*
				if($rback){
					$bk = $rback[0];
					$fc = $rback[1];
					$value = $rback[2];
				}
				*/
			}
			print_td_case($value, '', $bk, $fc); 
		}
		print("</tr>");
		$count++;
	}
	print("</table>");
}

?>
