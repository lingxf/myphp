<?php 
include_once 'myphp/common.php';
include_once 'myphp/disp_lib.php';
function get_all_subordinate($uid)
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
			$acond .= " or author = '$muid' ";
			$user[] = $muid;
			$nomore = false;
		}
		if($nomore)
			break;
	}
	return $acond;
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
			dprintf("%x<br>", $row['pa_id']);
			return $row['pa_id'];
		}
		dprint("not found for $pa1<br>");
	}else if($pa3 == ""){
		$sql = "select * from cnsf.pa where text = '$pa1' and (`pa_id` & 0xffff = 0)  or text = '$pa2'  and (`pa_id` & 0xff = 0) order by pa_id asc"; 
		$id_array = array();
		$res=mysql_query($sql) or die("Invalid query:" .$sql."<br>".mysql_error());
		while($row = mysql_fetch_array($res)){
			$id_array[] = $row['pa_id'];
		}
		if(count($id_array) == 2 && ($id_array[0] == ($id_array[1] & 0xff0000))) {
			dprintf("%x:%x<br>", $id_array[0], $id_array[1]);
			return $id_array[1];
		}
		dprint("not found for $pa2<br>");
	}else{	
		$sql = "select * from cnsf.pa where text = '$pa1' or text = '$pa2' or text = '$pa3' order by pa_id asc"; 
		$res=mysql_query($sql) or die("Invalid query:" .$sql."<br>".mysql_error());
		$pa_array = array();
		$id_array = array();
		dprint("$pa1|$pa2|$pa3");
		while($row = mysql_fetch_array($res)){
			if($row['text'] == $pa1)
				$id1 = $row['pa_id'];
			if($row['text'] == $pa2)
				$id2 = $row['pa_id'];
			if($row['text'] == $pa3)
				$id_array[] = $row['pa_id'];
		}
		if(count($id_array) >= 1)
			foreach($id_array as $id3){
				if($id1 == ($id3 & 0xff0000) && $id2 = ($id3 & 0xffff00)){
					dprintf("id:%x:%x:%x<br>", $id1, $id2, $id3);
					return $id3;
				}
			}
		dprint("not found for $pa3<br>");
		if(isset($id2))
			return $id2;
		if(isset($id1))
			return $id1;

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
	while($row = mysql_fetch_array($res)){
		$id = $row['pa_id'];
		if($id == ($pa_id & 0xff0000))
			$pas[0] = $row['text'];
		if($id == ($pa_id & 0xffff00))
			$pas[1] = $row['text'];
		if($id == $pa_id)
			$pas[2] = $row['text'];
	}
	return $pas;
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

function get_cond_by_author(&$author, $scope)
{
		$cond = '';
		if($scope == 1){
			$team = get_my_team($author);
			if($team != '')
				$author = "$team";
		}
		if($author != ''){
			$authors = explode(',', $author);
			$cond = " 0 ";
			foreach($authors as $au){
				if($scope == 0){
					$cond .= " or author = '$au' ";
				}else if($scope == 1 || $scope == 2){
					$cond .= " or author = '$au' or ";
					$cond .= get_all_subordinate($au);
				}
			}
		}
		return $cond;
}

function show_myteam_menu($login_id, $action)
{
	print("<input class='btn' tabindex=0 type='submit' onclick='window.location.href=\"?action=$action&scope=2&author=\";' value='All'>");
	print("<input class='btn' tabindex=0 type='submit' onclick='window.location.href=\"?action=$action&scope=0&author=$login_id\";' value='My'>");
	print("<input class='btn' tabindex=0 type='submit' onclick='window.location.href=\"?action=$action&scope=1&author=$login_id\";' value='Myteam'>");
	/*
	print("&nbsp;&nbsp;<a href='easykba.php?action=$action&author=&scope=2'>All</a>");
	print("&nbsp;&nbsp;<a href='easykba.php?action=$action&author=$login_id&scope=0'>MyMap</a>");
	print("&nbsp;&nbsp;<a href='easykba.php?action=$action&author=$login_id&scope=1'>Myteam</a>");
	*/
	print("
			<input id='id_input_author' name='author' value=''>
			<input class='btn' tabindex=0 type='submit' onclick='javascript:val = document.getElementById(\"id_input_author\").value; window.location.href=\"easykba.php?action=$action&scope=2&author=\"+val; return false;' value='Show'>
			");
	print("<br>");
}

?>
