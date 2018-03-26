<?php
/*
   copyright Xiaofeng(Daniel) Ling<lingxf@gmail.com>, 2016, Aug.
 */
#set_include_path("../:../report:../report/myphp");
include_once 'common.php';
include_once 'login_lib.php';

$action = get_url_var('action', '');
/*
if(isset($_GET['action'])){
	$action = $_GET['action'];
	$web_name = isset($_GET['web']) ? $_GET['web']:$web_name;
	if(session_name() != $web_name){
		session_name($web_name);
		session_start();
	}
}
*/

if($action == 'login'){
	$user_id = get_url_var('user_id', 'guest');
	$password = get_url_var('password', '*');
	$permit = 0;
	if(check_passwd($user_id, $password, $permit) === 0){
		$_SESSION['login_id'] = $user_id;
		$_SESSION['user_id'] = $user_id;
		$_SESSION['permit'] = $permit;
		print('ok');
	}else{
		print("usre_id or password wrong");
	}
}else if($action == 'logout') {
	$url = isset($_GET['url'])?$_GET['url']:$home_page;
	log_out($url);
	exit();
}else if($action == 'show_register') {
	$url = isset($_GET['url'])?$_GET['url']:$home_page;
	show_register($url, '');
	exit();
}else if($action == 'show_changepwd'){
	show_changepwd_ui();
}else if($action == 'do_changepwd'){
	$user_id = get_url_var('user_id', 'guest');
	$password = get_url_var('password', '*');
	$new_password = get_url_var('new_password', '*');
	$permit = 0;
	if(check_passwd($user_id, $password, $permit) === 0){
		$passwd = password_hash($new_password,PASSWORD_DEFAULT);
		$sql = "update $user_table set password = '$passwd' where user_id = '$user_id' ";
		$res = update_mysql_query($sql);
		print('ok修改密码成功！');
	}else{
		print("旧密码错误");
	}
}else if(isset($_GET['reset_id'])) {
	$sid= $_GET['reset_id'];
	$user = $_GET['user'];
	$url = isset($_GET['url'])?$_GET['url']:$home_page;
	$sql = "select * from user.user where user_id = '$user' and sid = $sid";
	$res = read_mysql_query($sql);
	if(mysql_fetch_array($res)){
		print("please reset password \n");
		show_reset_password($user, $url);
	}else
		print("does not found user $user");
	exit();
}else if(isset($_GET['activate'])) {
	$sid= $_GET['activate'];
	$user = $_GET['user'];
	$url = isset($_GET['url'])?$_GET['url']:$home_page;
	$sql = "update user.user set activate = 1 where user_id = '$user' and sid = $sid";
	$res=mysql_query($sql) or die("Query Error:".$sql . mysql_error());
	print("activing $user $sql<br>"); 
	$rows = mysql_affected_rows();
	if($rows > 0 ){
		print "$user activate successfully!";
		print("<script type=\"text/javascript\">setTimeout(\"window.location.href='$url'\",10000);</script>");
	}else
		print "$user activate fail!";
	exit();
}else if(isset($_POST['reset_password'])) {
	if(isset($_POST['password1']))
		$ps1 = $_POST['password1'];
	if(isset($_POST['password2']))
		$ps2 = $_POST['password2'];
	$user = $_POST['user'];
	$url = $_POST['url'];
	if($ps1 == $ps2)
	{
		$sql ="update user.user set password = ENCRYPT('$ps1', 'ab'), activate=1 where user_id = '$user'";
		$row = update_mysql_query($sql);
		if($row > 0)
			print("update new password sucessful");
		else
			print("update password sucessful");
		print $url;
		print("<script type=\"text/javascript\">setTimeout(\"window.location.href='$url'\",2000);</script>");
	}else{
		print("password miss match<br>");
		show_reset_password($user, $url);
	}
	exit();
}else if(isset($_POST['show_forget'])) {
	$url = isset($_GET['url'])?$_GET['url']:$home_page;
	show_forget($url);
	exit();
}else if(isset($_POST['show_register'])) {
	$url = isset($_GET['url'])?$_GET['url']:$home_page;
	show_register($url);
	exit();
}else if(isset($_POST['forget'])) {
	handle_forget();
	exit();
}else if(isset($_POST['do_register'])) {
	if(isset($_POST['email']))
		$email = $_POST['email'];
	if(isset($_POST['user']))
		$user = $_POST['user'];
	if(isset($_POST['name']))
		$name = $_POST['name'];
	if(isset($_POST['password1']))
		$ps1 = $_POST['password1'];
	if(isset($_POST['password2']))
		$ps2 = $_POST['password2'];
	$url = $_POST['url'];
	$mail_url = get_cur_root()."/$url";
	if($ps1 == $ps2)
	{
		$sql="SELECT * FROM user.user WHERE user_id = '$user'";
		$sid = mt_rand();
		$sql_ins="INSERT into user.user set user_id = '$user', name = '$name', email = '$email', password = ENCRYPT('$ps1', 'ab'), sid = $sid ;";
		$res=mysql_query($sql) or die("Query Error:".$sql . mysql_error());
		$row=mysql_fetch_array($res);
		if($row){
			print "$user already registered, please click Forget if forget password";
		}else{
			$res=mysql_query($sql_ins) or die("Query Error:".$sql_ins . mysql_error());
			$row=mysql_affected_rows($link);
			print "$user is created successful<br>";
			$message = "
				<html>
				<head>
				<title>Activate</title>
				<body>
				";
			$message .= "Please click <a href=$mail_url?user=$user&activate=$sid>here</a> to activate your account";
			$message .= " </body> </html> ";
			mail_html($email, '', "$user activate mail", $message);
			print "mail to $email for activate, please click the link in the mail<br>";
			print("<script type=\"text/javascript\">setTimeout(\"window.location.href='$url'\",5000);</script>");
		}
	}else{
		print("2 Password not match!");
		show_register($url);
	}
	exit();
}else if($action == 'show_login'){
	$url = $_SERVER['PHP_SELF'];
	show_login($url);
	exit();
}else{
	global $login_id;
	$login_id = 'guest';
	if(isset($_SESSION['login_id'])){
		$user_id = $_SESSION['login_id'];
		if($user_id != '')
			$login_id = $user_id;
	}
	$permit = get_session_var('permit', 1);
}
