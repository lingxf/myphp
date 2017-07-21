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

function print_sql_table_head($id, $width, $field_name=array(), $field_width=array(), $callback)
{
	$background = '#DCE6F1';

    //print("<table id='$id' class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 width=1384 style='width:$width.0pt;margin-left:20.5pt;border-collapse:collapse'>");
	print("<table id='$id' class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$width.0pt;margin-left:20.5pt;border-collapse:collapse'>");
    //print("<tr style='height:33.0pt'>");


	//$table = mysql_field_table($result, $i);
	$wn = count($field_width);
	$fn = count($field_name);
	if($fn == 0)
		return;
	print("<tr style='height:15.0pt;background:$background;'>");
	$i = 0;
	foreach($field_name as $field){
		$td_attr = '';

		if($i < $wn && $field_width[$i] != 0){
			$width = $field_width[$i];
		}else{
			$width = 0;
		}

		if(is_callable($callback))
			$field = $callback($field, $field, $field_name, $td_attr, $width);

		$attr="width=$width";
		print("<td $td_attr $attr nowrap valign=bottom style='width:$width.0pt;border:solid windowtext 1.0pt;background:#DCE6F1;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal><b>$field</b><o:p></o:p></p></td>");
		//print("<th $attr>$field</th>"); 
		$i++;
	}
    print("</tr>");
}

/*
$format 
 0 - summary count
 1 - no summary count
 2 - no wrap 
 4 - show total
*/
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
	if(($format & 4) != 0){
		$rows = mysql_num_rows($result);
		print("Total:$rows");
	}
	$noempty = false;
	print_sql_table_head($id, $width, $field_name, $field_width, $callback);
	$fields_num = mysql_num_fields($result);
	while($row=mysql_fetch_array($result)){
        print("<tr style='height:15.0pt'>");
		$noempty = true;
		for ($i = 0; $i < $fields_num; ++$i) {
			$field = mysql_field_name($result, $i);
			$value = $row[$i];
			if(is_numeric($value) && !strstr($value, '.'))
				$sum[$field] = isset($sum[$field]) ?$sum[$field]+$value:$value;
			$td_attr = '';
			$width = 10;
			if(is_callable($callback))
				$value = $callback($field, $value, $row, $td_attr, $width);
			$td = "<td $td_attr "; 
			if( ($format & 2) != 0)
				$td .= " nowrap ";
			$td .= " valign=bottom style='border:solid windowtext 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal>$value<o:p></o:p></p></td>";
			print($td);
			//print("<td>$value</td>"); 
		}
		print("</tr>");
	}
	if(is_callable($callback))
		$sum = $callback('sum', $sum, $row, $td_attr, $width);
	if(($format & 1) == 0 ){
		print("<tr style='height:15.0pt;background:#DCE6F1;'>");
		for ($i = 0; $i < $fields_num; ++$i) {
			$field = mysql_field_name($result, $i);
			$tt = isset($sum[$field])?$sum[$field]:'';
			if($i == 0){
				print("<td nowrap valign=bottom style='border:solid windowtext 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal>Total<o:p></o:p></p></td>");
				continue;
			}
			if($tt == 0|| $tt > 1000000)
				$tt = "";

			print("<td nowrap valign=bottom style='border:solid windowtext 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal>$tt<o:p></o:p></p></td>");
		}
		print("</tr>");
	}
	print("</table>");
}

/*
$format 
 1 - summary count
 2 - no wrap 
 4 - show total

function callback($index, $field, $value, $row, &$td_attr, &$width)
 title tr - index = -1  $field = ((title))
 title td -   $field = ((title)) $value = $field
 tr - index = -1
 td - index, field
 sum tr - index = -1, $field = ((sum))
 sum td - index >=1000

*/

function show_table_by_sql2($id, $sql, $width, $callback='', $format=0)
{

	$result = read_mysql_query($sql);
	$fields_num = mysql_num_fields($result);

	for ($i = 0; $i < $fields_num; ++$i) {
		$field = mysql_field_name($result, $i);
		$field_name[] = $field;
	}
	$sum = array();
	if(($format & 4) != 0){
		$rows = mysql_num_rows($result);
		print("Total:$rows");
	}
	$noempty = false;

	/*print table head*/
	print("<table id='$id' class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$width.0pt;margin-left:20.5pt;border-collapse:collapse'>");

	$background = '#DCE6F1';
	$tr_attr = "style='height:15.0pt;background:$background;'";
	if(is_callable($callback))
		$callback(-1, '((title))', '', $field_name, $tr_attr, $width);
	print("<tr $tr_attr '>");
	$i = 0;
	foreach($field_name as $field){
		$attr = '';
		$width = 0;
		$td_attr = '';
		if(is_callable($callback))
			$value = $callback($i, '((title))', $field, $field_name, $td_attr, $width);

		if($width != 0)
			$attr="width=$width";
		if($width != -1)
			print("<td $td_attr $attr nowrap valign=bottom style='width:$width.0pt;border:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;'><p class=MsoNormal><b>$value</b><o:p></o:p></p></td>");
		$i++;
	}
    print("</tr>");

	/*print table line*/
	while($row=mysql_fetch_array($result)){
		$tr_attr = "style='height:15.0pt'";
		$width = 0;
		if(is_callable($callback))
			$row = $callback(-1, '', '', $row, $tr_attr, $width);
        print("<tr $tr_attr>");
		$noempty = true;
		for ($i = 0; $i < $fields_num; ++$i) {
			$field = $field_name[$i];
			$value = $row[$i];
			if(is_numeric($value) && !strstr($value, '.'))
				$sum[$field] = isset($sum[$field]) ?$sum[$field]+$value:$value;
			$td_attr = '';
			$width = 0;
			if(is_callable($callback))
				$value = $callback($i, $field, $value, $row, $td_attr, $width);
			$td = "<td $td_attr "; 
			if($width != 0)
				$td .= " width=$width "; 
			if( ($format & 2) != 0)
				$td .= " nowrap ";
			$td .= " valign=bottom style='border:solid windowtext 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal>$value<o:p></o:p></p></td>";
			if($width != -1)
				print($td);
		}
		print("</tr>");
	}

	/*print summary line*/
	if(($format & 1)){
		$background = '#DCE6F1';
		$tr_attr = "style='height:15.0pt;background:$background;'";
		$width = 0;
		if(is_callable($callback))
			$sum = $callback(-1, "((sum))", $value, $sum, $tr_attr, $width);
		print("<tr $tr_attr '>");
		
		$sum[$field_name[0]] = 'Total';
		for ($i = 0; $i < $fields_num; ++$i) {
			$field = $field_name[$i];
			$value = isset($sum[$field])?$sum[$field]:'';
			$width = 0;
			if(is_callable($callback))
				$value = $callback(1000+$i, $field, $value, $sum, $td_attr, $width);

			if($width != -1)
				print("<td nowrap valign=bottom style='border:solid windowtext 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal>$value<o:p></o:p></p></td>");
		}
		print("</tr>");
	}
	print("</table>");
}


function show_table_by_sql_vertical($id, $db, $width, $sql, $field_name=array(), $field_width=array(), $callback='', $format=0)
{
	$ret=mysql_select_db($db);

	$result = read_mysql_query($sql);
	$columns = count($field_name);

	$sum = array();
	if($format == 1){
		$rows = mysql_num_rows($result);
		print("Total:$rows");
	}

	print_sql_table_head($id, $width, $field_name, $field_width, $callback);

	$field_name = array();
	for ($i = 0; $i < mysql_num_fields($result); ++$i) {
		$field = mysql_field_name($result, $i);
		$field_name[] = $field;
	}

	$fields_num = mysql_num_fields($result);
	$line = 0;
	while($row=mysql_fetch_array($result)){
		for ($i = 0; $i < $fields_num; ++$i) {
			$data[$line][] = $row[$i];
		}
		$line += 1;
	}
	for ($i = 0; $i < $fields_num; ++$i) {
        print("<tr style='height:15.0pt'>");
		$field = $field_name[$i];
		print("<td nowrap valign=bottom style='border:solid windowtext 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal>$field<o:p></o:p></p></td>");
		for($j = 0; $j < $line; $j++){
			$value = $data[$j][$i];
			print("<td nowrap valign=bottom style='border:solid windowtext 1.0pt;border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal>$value<o:p></o:p></p></td>");
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

/* mode bit
& 1 - show array[0] as first column, otherwise, key as first column
& 2 - do not do sum
*/

function show_table_by_array($table, $count_array, $colname, $col_count=0, $mode=0){
	$background = '#DCE6F1';
	$tr_width = 432;

	$table_name = '';
	if($colname)
		$table_name = $colname[0];
	print("<table id='$table_name' width=600 class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;margin-left:20.5pt;border-collapse:collapse'>");
	if($colname){

		print("<tr style='height:15.0pt'>");
		if($col_count == 0)
			$col_count = count($colname)/2;
		for($col = 0; $col < $col_count*2; $col+=2){
			$w = $colname[$col+1];
			$name = $colname[$col];
		//	print("<td>$name</td>");
			print("<td width=$w nowrap valign=bottom style='width:20.0pt;border:solid windowtext 1.0pt;                   background:#DCE6F1;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal><b>$name</b><o:p></o:p></p></td>");
			//print("<td width=$w nowrap valign=bottom style='width:20.0pt;border:solid windowtext 1.0pt;border-left:none;background:#DCE6F1;padding:0cm 5.4pt 0cm 5.4pt;height:15.0pt'><p class=MsoNormal><b>$name</b><o:p></o:p></p></td>");
		}
		print("</tr>");
	}

	if(array_key_exists('Ground Total', $count_array))
		$sum = $count_array["Ground Total"];
	else
		$sum = Array("Ground Total");
	foreach ($count_array as $key => $data){
		if($key == 'Ground Total' && !($mode & 2)){
			continue;
		}
		if($key == 'Ground Total')
			print("<tr style='height:15.0pt;background:$background;'>");
		else
			print("<tr style='height:15.0pt;'>");
		$col = 0;
		$width = $colname[$col+1];
		if($mode & 1){
			print_td_pa($width,43.8,$background,$data[0]);
			$col = 1;
			$col_max = $col_count;
		}else{
			print_td_pa($width,43.8,$background,$key);
			$col_max = $col_count - 1;
		}
		for(;$col < $col_max; $col++){
			$value = isset($data[$col])?$data[$col]:0;
			$width = isset($colname[$col*2+1])?$colname[$col*2+1]:0;
			print_td_pa($width,43.8,$background,$value);

			if($mode & 1)
				$sum[$col] = isset($sum[$col]) ? $sum[$col] + $value: $value;
			else
				$sum[$col + 1] = isset($sum[$col+1]) ? $sum[$col+1] + $value: $value;
		}
		print("</tr>");
	}
	if(!($mode & 2))
		print_tr_array($background, $sum);
	print("</table>");
	print("<br/>");
	return; 
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
