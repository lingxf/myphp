<?php
/*
   copyright Xiaofeng(Daniel) Ling<lingxf@gmail.com>, 2016, Aug.
 */
$db=1;
include_once 'db_connect.php';
include_once 'myphp/common.php';

function check_passwd($login_id, $login_passwd){

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

function show_register($page='login_lib.php',$readonly = 'readonly')
{
	print("<form enctype=\"multipart/form-data\" action=\"$page\" method=\"POST\">
			ID: <input name=\"user\" value=\"\" onkeyup=\"document.getElementById('id_email').value=this.value + '@qti.qualcomm.com';\"><br>
			Name: <input name=\"name\" value=\"\" /><br>
			Email: <input id=\"id_email\"  $readonly name=\"email\" value=\"\" /><br>
			<input id='id_url' name=\"url\" type='hidden' value=\"$page\" />
			Password:&nbsp;&nbsp;&nbsp;   <input name=\"password1\" type=\"password\"/><br>
			Password Again:&nbsp;&nbsp;&nbsp;   <input name=\"password2\" type=\"password\"/><br>
			<input type=\"submit\" name=\"register\" value=\"Register\" />
			<input type=\"submit\" name=\"forget\" value=\"Forget\" />
			</form> ");
//			<input type=\"submit\" name=\"register\" onclick=\"javascript:getElementById('id_url').value = window.location.href\" value=\"Register\" />
}

function show_reset_password($user)
{
	print("<form enctype=\"multipart/form-data\" action=\"login_lib.php\" method=\"POST\">
			ID: <input name=\"user\" value=\"$user\" /><br>
			<input id='id_url' name=\"url\" type='hidden' value=\"\" />
			Password:&nbsp;&nbsp;&nbsp;   <input name=\"password1\" type=\"password\"/><br>
			Password Again:&nbsp;&nbsp;&nbsp;   <input name=\"password2\" type=\"password\"/><br>
			<input type=\"submit\" name=\"reset_password\" onclick=\"javascript:getElementById('id_url').value = window.location.href\" value=\"Reset\" />
			</form> ");
}

function show_forget()
{
	print("<form enctype=\"multipart/form-data\" action=\"login_lib.php\" method=\"POST\">
			ID: <input name=\"user\" value=\"\" /><br>
			Email: <input name=\"email\" value=\"\" /><br>
			<input id='id_url' name=\"url\" type='hidden' value=\"\" />
			<input type=\"submit\" name=\"forget\" onclick=\"javascript:getElementById('id_url').value = window.location.href\" value=\"Reset Password\" />
			</form> ");
}

function handle_forget()
{
	if(isset($_POST['email']))
		$email = $_POST['email'];
	$url = $_POST['url'];
	
	$sql="SELECT * FROM user.user WHERE email = '$email'";
	$res=read_mysql_query($sql);
	$row=mysql_fetch_array($res);
	if(!$row){
		print("$email is not found!<br>");
		exit();
	}
	$sid = $row['sid'];
	$user = $row['user_id'];
	$sid = mt_rand();
	$sql="update user.user set sid=$sid WHERE email = '$email'";
	update_mysql_query($sql);
	$message = "Please click <a href=$url?user=$user&reset=$sid>here</a> to reset your password";
	mail_html($email, '', "$user reset mail", $message);
	print("mail to $email to reset password, please click link in the email");
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
			$ret = check_passwd($login_id, $password);
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
		$login_id=$_SESSION['user'];
	}else{
		if($exit_nologin){
			header("Location: login_lib.php?action=login");
			exit;
		}
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
}else if(isset($_GET['reset'])) {
	$sid= $_GET['reset'];
	$user = $_GET['user'];
	$sql = "select * from user.user where user_id = '$user' and sid = $sid";
	$res = read_mysql_query($sql);
	if(mysql_fetch_array($res)){
		print("please reset password\n");
		show_reset_password($user);
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
	}else
		print "$user activate fail!";
	print("<script type=\"text/javascript\">setTimeout(\"window.location.href='$url'\",1000);</script>");
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
		$sql ="update user.user set password = ENCRYPT('$ps1', 'ab') where user_id = '$user'";
		$row = update_mysql_query($sql);
		if($row > 0)
			print("update new password sucessful");
		else
			print("update password sucessful");
	}else{
		print("password miss match<br>");
		show_reset_password();
	}
	exit();
}else if(isset($_POST['show_forget'])) {
	show_forget();
	exit();
}else if(isset($_POST['show_register'])) {
	show_register();
	exit();
}else if(isset($_POST['forget'])) {
	handle_forget();
	exit();
}else if(isset($_POST['register'])) {
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
			print("<script type=\"text/javascript\">setTimeout(\"window.location.href='$url'\",1000);</script>");
		}
	}
	exit();
}
?>
