<?php
/*
   copyright Xiaofeng(Daniel) Ling<lingxf@gmail.com>, 2016, Aug.
 */
set_include_path("../:../report:../report/myphp");
include 'debug.php';
include_once 'db_connect.php';
include_once 'common.php';

global $user_table;
if(!$user_table)
	$user_table = 'user.user';
function check_passwd2($login_id, $login_passwd){

	$sql1="SELECT * FROM user.user WHERE user_id = '$login_id';";
	$res1=mysql_query($sql1) or die("Query Error:" . mysql_error());
	$row1=mysql_fetch_array($res1);
	if(!$row1)
		return 1;
	if($row1['password'] == "")
		return 0;
    if($row1['password'] == $login_passwd)
        return 0;
	$sql1="SELECT * FROM user.user WHERE user_id = '$login_id' and password=ENCRYPT('$login_passwd', 'ab');";
	$res1=mysql_query($sql1) or die("Query Error:" . mysql_error());
	$row1=mysql_fetch_array($res1);
	if(!$row1)
		return 2;
//	$passwd = crypt($login_passwd);
	return 0;
}

function check_passwd($login_id, $login_passwd, &$permit)
{
	global $user_table;
	if(!$user_table)
		$user_table = 'user.user';
	$permit = 0;
	$sql="SELECT * FROM $user_table WHERE user_id = '$login_id';";
	$res=read_mysql_query($sql);
	$row=mysql_fetch_array($res);
	if(!$row)
		return 1;
	if($row['password'] == "" || $row['password'] == $login_passwd){
		if(isset($row['permission']))
			$permit = $row['permission'];
		return 0;
	}
	$hash = $row['password'];
	if(!password_verify($login_passwd, $hash))
		return 2;
	if(isset($row['permission']))
		$permit = $row['permission'];
	return 0;
}

function show_login($page)
{
	print(" <html>
			<Title>Login</Title>
			");
	print("
			<form enctype=\"multipart/form-data\" action=\"$page\" method=\"POST\">
			Login Name: <input name=\"user\" value=\"\" /><br>
			<input id='id_url' name=\"url\" type='hidden' value=\"$page\" />
			Password:&nbsp;&nbsp;&nbsp;   <input name=\"password\" type=\"password\"/><br>
			<input type=\"submit\" name=\"login\" value=\"Login\" />
			<input type=\"submit\" name=\"show_register\" value=\"Register\" />
			<input type=\"submit\" name=\"show_forget\" value=\"Forget\" />
			</form>
			For China CE team, account already setup, default Login Name is Windows ID and password is your employee number
			");
}

function print_js_changepwd()
{
	global $login_id;
	print("
	<script type='text/javascript'>
	function do_changepwd()
	{
		old_password = document.getElementById('id_old_password').value;
		password1 = document.getElementById('id_new_password1').value;
		password2 = document.getElementById('id_new_password2').value;
		if(password1 != password2){
			alert('两次密码不一致辞');
			return false;
		}
		url = 'myphp/login_lib.php?action=do_changepwd&user_id=$login_id'+'&password='+old_password+'&new_password='+password1;
		load_url_reload(url, '', '');
	}
	</script>
	");
	include 'common_js.php';
}

function show_changepwd_ui()
{
	global $login_id;
	print_js_changepwd();
	print("
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<meta http-equiv='Content-Language' content='zh-CN' /> 
	");
	print_input("用户名 ", 120, 'id_user', "$login_id");
	print("<br>");
	print("    旧密码 <input id='id_old_password' style='width:120px; padding: 2px; border: 1px solid black' name=\"password\" type=\"password\"/>");
	print("<br>");
	print("    新密码 <input id='id_new_password1' style='width:120px; padding: 2px; border: 1px solid black' name=\"password\" type=\"password\"/>");
	print("<br>");
	print("重复新密码 <input id='id_new_password2' style='width:120px; padding: 2px; border: 1px solid black' name=\"password\" type=\"password\"/>");
	print("<br>");
	print_button("更改", 'bt_change', 'do_changepwd()');
}

function show_register($page='login_lib.php',$readonly = 'readonly')
{
	print("<form enctype=\"multipart/form-data\" action=\"$page\" method=\"POST\">
			ID: <input name=\"user\" value=\"\" onkeyup=\"document.getElementById('id_email').value=this.value + '@qti.qualcomm.com';\"><br>
			Name: <input name=\"name\" value=\"\" /><br>
			Email: <input id=\"id_email\"  $readonly name=\"email\" value=\"\" /><br>
			<input id='id_url' name=\"url\" type='hidden' value=\"$page\" />
			Password:&nbsp;&nbsp;&nbsp;   <input name=\"password1\" type=\"password\"/><br>
			Password Again:&nbsp;&nbsp;&nbsp;   <input name=\"password2\" type=\"password\"/><br>
			<input type=\"submit\" name=\"do_register\" value=\"Register\" />
			<input type=\"submit\" name=\"forget\" value=\"Forget\" />
			</form> ");
//			<input type=\"submit\" name=\"register\" onclick=\"javascript:getElementById('id_url').value = window.location.href\" value=\"Register\" />
}

function show_reset_password($user, $page='login_lib.php')
{
	print("<form enctype=\"multipart/form-data\" action=\"$page\" method=\"POST\">
			ID: <input name=\"user\" value=\"$user\" /><br>
			<input id='id_url' name=\"url\" type='hidden' value=\"\" />
			Password:&nbsp;&nbsp;&nbsp;   <input name=\"password1\" type=\"password\"/><br>
			Password Again:&nbsp;&nbsp;&nbsp;   <input name=\"password2\" type=\"password\"/><br>
			<input type=\"submit\" name=\"reset_password\" onclick=\"javascript:getElementById('id_url').value = \"http://\" + window.location.host+window.location.pathname\" value=\"Reset\" />
			</form> ");
}

function show_forget($page='login_lib.php')
{
	print("<form enctype=\"multipart/form-data\" action=\"$page\" method=\"POST\">
			ID: <input name=\"user\" value=\"\" /><br>
			Email: <input name=\"email\" value=\"\" /><br>
			<input id='id_url' name=\"url\" type='hidden' value=\"\" />
			<input type=\"submit\" name=\"forget\" onclick=\"javascript:getElementById('id_url').value =  'http://' + window.location.host+window.location.pathname\" value=\"Reset Password\" />
			</form> ");
}

function handle_forget()
{
	if(isset($_POST['email']))
		$email = $_POST['email'];
	if(isset($_POST['user']))
		$user = $_POST['user'];
	$url = $_POST['url'];
//	$mail_url = get_cur_root()."/$url";
	$mail_url = $url;
	
	$sql="SELECT * FROM user.user WHERE email = '$email'";
	$res=read_mysql_query($sql);
	while($row=mysql_fetch_array($res)){
		$sid = $row['sid'];
		$suser = $row['user_id'];
		$sid = mt_rand();
		if($user != $suser)
			continue;
		$sql="update user.user set sid=$sid WHERE email = '$email'";
		update_mysql_query($sql);
		$message = "Please click <a href=$mail_url?user=$user&reset=$sid>here</a> to reset your password";
		mail_html($email, '', "$user reset mail", $message);
		print("mail to $email to reset password, please click link in the email");
		print("<script type=\"text/javascript\">setTimeout(\"window.location.href='$url'\",3000);</script>");
		return;
	}
	print("$email is not found!<br>");
	exit();
}

function home_link($url="/")
{
	print("<a href='$url'>Home</a>");	
}

function check_login($session_name, $exit_nologin=false)
{
	global $login_id;

	if(isset($_POST['login'])){
		if(isset($_POST['user'])){
			$login_id=$_POST['user'];
			$url = $_POST['url'];
			if(isset($_POST['password'])) $password=$_POST['password'];
			$permit = 0;
			$ret = check_passwd($login_id, $password, $permit);
			if($ret == 1){
				print("No user $login_id exist");
				unset($_SESSION['user']); 
				show_login($url);
				exit;
			}else if($ret == 2){
				print("wrong password<br>");
				unset($_SESSION['user']);
				show_login($url);
				exit;
			}else{ //login successful
				$_SESSION = array();
				session_destroy();
				session_name($session_name);
				session_start();
				$_SESSION['user'] = $login_id;
			}
	
		}
	}else if(isset($_POST['register'])){
		header("Location: login_lib.php?action=register");
		exit;
	}else if(isset($_SESSION['user'])){
		$user=$_SESSION['user'];
		if($user != '')
			$login_id = $user;
	}else{
		if($exit_nologin){
			header("Location: login_lib.php?action=login");
			exit;
		}
		$login_id = 'guest';
	}
}

function log_out($url)
{
	$_SESSION = array();
	session_destroy();
	print("Logout Successful!");
	print("<script type=\"text/javascript\">setTimeout(\"window.location.href='$url'\",1000);</script>");
//	header("Location: $url");

}

$action = '';
if(isset($_GET['action'])){
	$action = $_GET['action'];
	$web_name = isset($_GET['web']) ? $_GET['web']:$web_name;
	if(session_name() != $web_name){
		session_name($web_name);
		session_start();
	}
}

if($action == 'login')
{
	if(!isset($home_page))
		$home_page = 'home.php';
	$url = isset($_GET['url'])?$_GET['url']:$home_page;
	show_login($url);
	exit();
}else if($action == 'logout') {
	$url = isset($_GET['url'])?$_GET['url']:$home_page;
	log_out($url);
	exit();
}else if($action == 'register') {
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
}else if(isset($_GET['reset'])) {
	$sid= $_GET['reset'];
	$user = $_GET['user'];
	$url = isset($_GET['url'])?$_GET['url']:$home_page;
	$sql = "select * from user.user where user_id = '$user' and sid = $sid";
	$res = read_mysql_query($sql);
	if(mysql_fetch_array($res)){
		print("please reset password $url\n");
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
			print "$user already registered";
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
}
?>
