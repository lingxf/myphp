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
    print("<table id='$id' class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 width=1384 style='width:$width.0pt;margin-left:-1.5pt;border-collapse:collapse'>");
    print("<tr style='height:33.0pt'>");

	//$table = mysql_field_table($result, $i);
	$wn = count($field_width);
	$i = 0;
	foreach($field_name as $field){
		if($i < $wn){
			$width = $field_width[$i];
			$attr="width=$width";
		}else
			$attr = '';
		print("<th $attr>$field</th>"); 
		$i++;
	}
    print("</tr>");
}

function show_table_by_sql($id, $db, $width, $sql, $field_name=array(), $field_width=array(), $callback='')
{
	$ret=mysql_select_db($db);

	$result = read_mysql_query($sql);
	$columns = count($field_width);
	if( $columns == 0){
		for ($i = 0; $i < mysql_num_fields($result); ++$i) {
			$field = mysql_field_name($result, $i);
			$field_name[] = $field;
		}
	}
	$sum = array();
	print_sql_table_head($id, $width, $field_name, $width);
	$fields_num = mysql_num_fields($result);
	while($row=mysql_fetch_array($result)){
        print("<tr style='height:33.0pt'>");
		for ($i = 0; $i < $fields_num; ++$i) {
			$field = mysql_field_name($result, $i);
			$value = $row[$i];
			$sum[$field] += $value;
			if($callback != '')
				$value = call_user_func($callback, $field, $value);
			print("<td>$value</td>"); 
		}
		print("</tr>");
	}
    print("<tr style='height:33.0pt'>");
	if($callback != '')
		$sum = call_user_func($callback, 'sum', $sum);
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
	print("</table>");
}

?>
