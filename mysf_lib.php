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

?>
