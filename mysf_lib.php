<?php 
include_once 'myphp/common.php';
include_once 'myphp/disp_lib.php';

/* 0 - all
   1 - direct
   2 - direct with sub
   3 - direct without sub
*/
function get_subordinates($uid, $type=0)
{
	$user = array();
	if($type == 0)
	$sql = " select * from user.user where supervisor != user_id and team_leads = '$uid'";
	else if($type == 1)
	$sql = " select * from user.user where supervisor != user_id and supervisor = '$uid'";

	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$muid = $row['user_id'];
		$user[] = $muid;
	}
	return $user;
}

function get_all_subordinate($uid, $field_name='author')
{
	$user[] = array();
	$user[] = $uid;
	$ucond = "supervisor = '$uid'";
	$acond = " 0  ";
	while(true){
		$sql = " select * from user.user where supervisor != user_id and $ucond";
		$res = read_mysql_query($sql);
		$ucond = " 0 ";
		$nomore = true;
		while($row = mysql_fetch_array($res)){
			$muid = $row['user_id'];
			$ucond .= " or supervisor = '$muid' ";
			$acond .= " or $field_name = '$muid' ";
			$user[] = $muid;
			$nomore = false;
		}
		if($nomore)
			break;
	}
	return $acond;
}

function get_team_id($lead)
{
		$sql = " select * from user.leads where user_id = '$lead'";
		$res = read_mysql_query($sql);
		while($row = mysql_fetch_array($res)){
			$team_id = $row['team_id'];
			return $team_id;
		}
		return 0;
}


function get_my_team($login_id)
{
		$sql = " select * from user.user where user_id = '$login_id'";
		$res = read_mysql_query($sql);
		while($row = mysql_fetch_array($res)){
			$team_leads= $row['team_leads'];
			return $team_leads;
		}
		return '';
}

function show_3pa_select($pa_id=0x10101, $mode=0)
{
	print("<select id='sel_pa1' name='sel_pa1' onchange='change_pa(this.value)'>");
	$sel = ($pa_id & 0xff0000);
	show_pa_option(0, $sel, $mode);
	print("</select>");
	print("<select id='sel_pa2' name='sel_pa2' onchange='change_pa(this.value)'>");
	$sel = ($pa_id & 0xffff00);
	show_pa_option($pa_id & 0xff0000, $sel, $mode);
	print("</select>");
	print("<select id='sel_pa3' name='sel_pa3' onchange='change_pa(this.value)'>");
	$sel = ($pa_id & 0xffffff);
	show_pa_option($pa_id & 0xffff00, $sel, $mode);
	print("</select>");
}


function show_pa_option($pa_id, $sel, $mode=0)
{
	$patb = "cnsf.pa";
	if(($pa_id & 0xffffff) == 0)
		$sql = " select * from $patb where (`pa_id` & 0xffff) = 0";
	else if(($pa_id & 0xffff) == 0)
		$sql = " select * from $patb where (`pa_id` & 0xff0000) = $pa_id and (`pa_id` & 0xff) = 0 and (`pa_id` & 0xff00) !=0  ";
	else if(($pa_id & 0xff) == 0)
		$sql = " select * from $patb where (`pa_id` & 0xffff00) = $pa_id and (`pa_id` & 0xff) != 0 ";
	else{
		print("error pa_id:$pa_id");
		return;
	}
//	printf("pa_option:%x $sel $mode", $pa_id) ;
	if($mode == 1){
		if(($pa_id & 0xffffff) == 0 ){
			$pa_id = (($pa_id & 0xff0000) | 0xff00);
			print("<option value='$pa_id' >All</option>");
		}else if(($pa_id & 0xffff) == 0 ){
			$pa_id = (($pa_id & 0xff0000) | 0xff00);
			print("<option value='$pa_id' >All</option>");
		}else if(($pa_id & 0xff) == 0 ){
			$pa_id = (($pa_id & 0xffff00) | 0xff);
			print("<option value='$pa_id' >All</option>");
		}
	}
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$sub_id = $row['pa_id'];
		$text = $row['text'];
		$type = $row['type'];
		$sel_txt = '';
		if($sel == $sub_id)
			$sel_txt = "selected";
		if($type != 0){
//			print("<option value='$sub_id' $sel_txt><span type='color=#ff0000;'>$text</span></option>");
			continue;
		}
		print("<option value='$sub_id' $sel_txt>$text</option>");
	}

	if($mode == 0){
		if(($pa_id & 0xffffff) == 0 ){
		}else if(($pa_id & 0xffff) == 0 ){
			$pa_id = (($pa_id & 0xff0000) | 0xfe00 );
			print("<option value='$pa_id'>Common</option>");
			$pa_id = (($pa_id & 0xff0000) | 0xffff);
			print("<option value='$pa_id' >All</option>");
		}else if(($pa_id & 0xff) == 0 ){
			if(($pa_id & 0xff00) == 0xfe00){
				$pa_id = (($pa_id & 0xffff00) | 0xfe);
				print("<option value='$pa_id' >Common</option>");
			}else if(($pa_id & 0xffff) == 0xffff){
				$pa_id = (($pa_id & 0xffff00) | 0xff);
				print("<option value='$pa_id' >All</option>");
			}else if(($pa_id & 0xff00) != 0){
				$pa_id = (($pa_id & 0xffff00) | 0xff);
				print("<option value='$pa_id' >All</option>");
				$pa_id = (($pa_id & 0xffff00) | 0xfe);
				print("<option value='$pa_id' >Common</option>");
			}
		}
	}
}

function get_pa_id($pa1, $pa2 = "", $pa3 = "")
{
	if($pa2 == ""){
		$sql = "select * from cnsf.pa where text = '$pa1' and (`pa_id` & 0xffff = 0) "; 
		$res=mysql_query($sql) or die("Invalid query:" .$sql."<br>".mysql_error());
		while($row = mysql_fetch_array($res)){
			return $row['pa_id'];
		}
		//dprint("not found for $pa1<br>");
	}else if($pa3 == ""){
		$sql = "select * from cnsf.pa where text = '$pa1' and (`pa_id` & 0xffff = 0)  or text = '$pa2'  and (`pa_id` & 0xff = 0) order by pa_id asc"; 
		$id_array = array();
		$id1 = '';
		$res=mysql_query($sql) or die("Invalid query:" .$sql."<br>".mysql_error());
		while($row = mysql_fetch_array($res)){
			if($row['text'] == $pa1)
				$id1 = $row['pa_id'];
			if($row['text'] == $pa2)
				$id_array[] = $row['pa_id'];
		}
		if($id1 != '' && count($id_array) >= 2){
			foreach($id_array as $id2){
				if($id1 == ($id2 & 0xff0000)) {
					dprintf("%x:%x<br>", $id_array[0], $id_array[1]);
					return $id_array[1];
				}
			}
		}
		//dprint("not found for PA2:$pa2<br>");
	}else{	
		$sql = "select * from cnsf.pa where text = '$pa1' or text = '$pa2' or text = '$pa3' order by pa_id asc"; 
		$res=mysql_query($sql) or die("Invalid query:" .$sql."<br>".mysql_error());
		$pa_array = array();
		$id_array = array();
		$id2_array = array();
		//dprint("$pa1|$pa2|$pa3");
		while($row = mysql_fetch_array($res)){
			if($row['text'] == $pa1)
				$id1 = $row['pa_id'];
			if($row['text'] == $pa2 )
				if(($row['pa_id'] & 0xff) == 0)	
					$id2_array[] = $row['pa_id'];
			if($row['text'] == $pa3 ){
				if(($row['pa_id'] & 0xff) != 0)	
					$id_array[] = $row['pa_id'];
			}
		}
		foreach($id2_array as $id2_){
				if($id1 == ($id2_ & 0xff0000))
					$id2 = $id2_;
		}
		if(count($id_array) >= 1)
			foreach($id_array as $id3){
				if($id1 == ($id3 & 0xff0000) && $id2 = ($id3 & 0xffff00)){
					dprintf("id:%x:%x:%x<br>", $id1, $id2, $id3);
					return $id3;
				}
			}
		//dprint("not found for PA3:$pa3<br>");

	}
	return 0;
}

function get_patext_by_id($pa_id, $type=0)
{
	$sql = "select * from pa where `pa_id` = $pa_id";
	$res=read_mysql_query($sql);
	while(($row=mysql_fetch_array($res))){
		return $row['text'];
	}
	return '';
}	

function get_3pa_by_id($pa_id)
{
	$sql = "select * from cnsf.pa where `pa_id` = $pa_id or `pa_id` = ( $pa_id & 0xff0000) or `pa_id` = ( $pa_id & 0xffff00) ";
	$res=read_mysql_query($sql);
	$text = '';
	$pas = array('', '', '');
	while($row = mysql_fetch_array($res)){
		$id = $row['pa_id'];
		if($id == ($pa_id & 0xff0000))
			$pas[0] = $row['text'];
		else if($id == ($pa_id & 0xffff00))
			$pas[1] = $row['text'];
		else if($id == $pa_id)
			$pas[2] = $row['text'];
	}
	return $pas;
}	

function get_paline_by_id($pa_id)
{
	$sql = "select * from pa where `pa_id` = $pa_id or `pa_id` = ( $pa_id & 0xff0000) or `pa_id` = ( $pa_id & 0xffff00) ";
	$res=read_mysql_query($sql);
	$text = '';
	$pa3 = '';
	$pa2 = '';
	$pa1 = '';
	while($row = mysql_fetch_array($res)){
		$id = $row['pa_id'];
		if($id == ($pa_id & 0xff0000))
			$pa1 =  $row['text'];
		if($id == ($pa_id & 0xffff00))
			$pa2 =  $row['text'];
		if($id == $pa_id)
			$pa3 =  $row['text'];
	}
	if($pa3 == '')
		return '';
	if( ($pa_id & 0xff) == 0)
		return "$pa1|$pa2|";
	if( ($pa_id & 0xff) == 0xfe)
		return "$pa1|$pa2|";
	if( ($pa_id & 0xff00) == 0xfe00)
		return "$pa1||";
	return "$pa1|$pa2|$pa3";
}

function get_pa_by_id($pa_id, $type=0)
{
	$sql = "select * from pa where `pa_id` = $pa_id or `pa_id` = ( $pa_id & 0xff0000) or `pa_id` = ( $pa_id & 0xffff00) ";
	$res=read_mysql_query($sql);
	$text = '';
	while($row = mysql_fetch_array($res)){
		$id = $row['pa_id'];
		if($id == ($pa_id & 0xff0000))
			$text .= '"pa1":"' . $row['text'] . '",';
		if($id == ($pa_id & 0xffff00))
			$text .= '"pa2":"' . $row['text'] . '",';
		if($id == $pa_id)
			$text .= '"pa3":"' . strip($row['text']) . '",';
	}
	return $text;
}

function get_prev_pa_id($pa_id)
{
	if(($pa_id & 0xff) > 1)
		$pa_id--;
	else if(($pa_id & 0xff00) > 0x100){
		$pa_id = ($pa_id & 0xffff00) - 0x100;
		$pa_id += next_new_pa_id($pa_id >> 16 , ($pa_id & 0xff00) >> 8) -1 ;
	}
	else if(($pa_id & 0xff0000) > 0x10000){
		$pa_id = ($pa_id & 0xff0000) - 0x10000;
		$pa_id += (next_new_pa_id($pa_id >> 16) -1) << 8 ;
	}else
		$pa_id = 0x10101;
	return $pa_id;
}

function get_next_pa_id($pa_id)
{
	$old_id = $pa_id;
	$pa_id++;
	$sql = "select * from pa where `pa_id` = '$pa_id' ";
	$res=read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		return $row['pa_id'];
	}
	$pa_id = ($pa_id &0xff0000) + ($pa_id & 0xff00) + 0x101;
	$sql = "select * from pa where `pa_id` = '$pa_id' ";
	$res=read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		return $row['pa_id'];
	}

	$pa_id = ($pa_id &0xff0000) + 0x10101;
	$sql = "select * from pa where `pa_id` = '$pa_id' ";
	$res=read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		return $row['pa_id'];
	}

	return $old_id;
}

function update_next_pa_id($id1, $id2, $value){ 
	$id = ($id1 << 8) + $id2;
	$sql = "replace param set `next_id` = $value , pa_id = $id";
	$res=mysql_query($sql) or die("Invalid query:" .$sql."<br>".mysql_error());
	if(mysql_affected_rows() >= 1){
		return true;
	}
	return false;
}

function set_leader($user_id, $overwrite=True)
{
	$cond = get_cond_by_author($user_id, 2, 'user_id');
	$sql = "update user.user set team_leads = '$user_id' where $cond ";
	if(!$overwrite)
		$sql .= " and team_leads = '' ";
	$res = update_mysql_query($sql);
	$rows = mysql_affected_rows();
	print("Update $rows rows for $user_id <br>");
	return $rows;
}
/*
scope=
0 author list itself
1 direct layer report to autho
2 all layer report to author
3 team team_leads to author
4 team author belong to
5 manual sub_leads to author
*/
function get_cond_by_author(&$author, $scope, $field_name='author')
{
		$cond = '';
		if($author != ''){
			$authors = explode(',', $author);
			$cond = " 0 ";
			foreach($authors as $au){
				if($scope == 0){
					$cond .= " or $field_name = '$au' ";
				}else if($scope == 1){
					$cond .= " or $field_name = '$au' ";
					$team = get_subordinates($au, 1);
					foreach($team as $own){
						$cond .= " or $field_name = '$own' ";
					}
				}else if($scope == 2){
					$cond .= " or $field_name = '$au' or ";
					$cond .= get_all_subordinate($au, $field_name);
				}else if($scope == 3){
					$cond .= " or team_leads = '$au' ";
				}else if($scope == 4){
					$team_lead = get_user_prop($au, 'team_leads');
					$cond .= " or $field_name = '$team_lead' or ";
					$cond .= get_all_subordinate($team_lead, $field_name);
				}
			}
		}
		return $cond;
}

function show_myteam_menu($login_id, $action)
{
	print("<input class='btn' tabindex=0 type='submit' onclick='window.location.href=\"?action=$action&scope=2&author=\";' value='All'>");
	print("<input class='btn' tabindex=0 type='submit' onclick='window.location.href=\"?action=$action&scope=0&author=$login_id\";' value='My'>");
	print("<input class='btn' tabindex=0 type='submit' onclick='window.location.href=\"?action=$action&scope=1&author=$login_id\";' value='Subteam'>");
	/*
	print("&nbsp;&nbsp;<a href='easykba.php?action=$action&author=&scope=2'>All</a>");
	print("&nbsp;&nbsp;<a href='easykba.php?action=$action&author=$login_id&scope=0'>MyMap</a>");
	print("&nbsp;&nbsp;<a href='easykba.php?action=$action&author=$login_id&scope=1'>Myteam</a>");
	*/
	print("
			<input id='id_input_author' name='author' value=''>
			<input class='btn' tabindex=0 type='submit' onclick='javascript:val = document.getElementById(\"id_input_author\").value; window.location.href=\"?action=$action&scope=2&author=\"+val; return false;' value='Show'>
			");
}

function get_owner_alias(&$name)
{
	$sql = "select `Case Owner Alias` from mysf.clonecase where `Case Owner` = '$name' and `Case Owner Alias` is not NULL and `Case Owner Alias` != ''";
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		return $row['Case Owner Alias'];
	}
	$name_a = explode(' ', $name);
	$n = count($name_a);
	$name = '';
	if($n > 1){
		$name = $name_a[1];
		for($i = 2; $i < $n; $i++){
			$name .= " ${name_a[$i]}";
		}
	}
	$name .= " ${name_a[0]}";
	print("Switch name:$name ");
	$sql = "select `Case Owner Alias` from mysf.clonecase where `Case Owner` = '$name' and `Case Owner Alias` is not NULL and `Case Owner Alias` != ''";
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		return $row['Case Owner Alias'];
	}
	return '';
}

function show_week_select($week){
	print("Week:<select  id=\"sel_week\" name=\"rweek\" onchange=\"change_week('rweek', this.value)\">");
	if($week == '')
		$week = 0;
	for($i = 0; $i <= 54; $i++){
		if($week == $i)
			$selected = 'selected';
		else
			$selected = '';
		if($i == 0)
			print("<option value=\"$i\" $selected >all</option>");
		else
			print("<option value=\"$i\" $selected >$i</option>");
	}
	print("</select>");
}


function show_month_select($month){
	print("Month:<select  id=\"sel_month\" name=\"rmonth\" onchange=\"change_month('rmonth', this.value)\">");
	if($month == '')
		$month = 0;
	for($i = 0; $i <= 12; $i++){
		if($month == $i)
			$selected = 'selected';
		else
			$selected = '';
		if($i == 0)
			print("<option value=\"$i\" $selected >all</option>");
		else
			print("<option value=\"$i\" $selected >$i</option>");
	}
	print("</select>");
}

function show_year_select($year){
	print("Year:<select  id=\"sel_year\" name=\"ryear\" onchange=\"change_year('ryear', this.value)\">");
	for($i = 2016; $i <= 2018; $i++){
		if($year == $i)
			$selected = 'selected';
		else
			$selected = '';
		print("<option value=\"$i\" $selected >$i</option>");
	}
	print("</select>");
}

function import_kba_excel($file)
{
	global $login_id;
	$trans = array(
	'Name'=>'title',
	'DCN'=>'kba_id',
	'Rev'=>'rev',
	'Related DCN'=>'related',
	'Status'=>'status',
	'Created By'=>'author',
	'Modified By'=>'modified',
	'Approval Group'=>'approval_group',
	'Modified On'=>'modified_date',
	'Short Name'=>'skip',
	);

	$itm = date("Y-m-d H:i:s", filemtime($file));
	$more = " importer = '$login_id' ";
	$time = strftime("%Y-%m-%d %H:%M:%S", time());
	$lines = import_excel_file($file, 'cnsf', 'kba_stock','kba_id', $trans, $more, '','', "modified_date" ); 
	add_import_log("import", "kba", $lines, $time, "Insert $lines kba from $itm\n"); 
	print("Import $lines");
}


?>
