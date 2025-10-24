.12<?php 
include('includes/config.php'); 
if(isset($_SESSION['login'])){
	$model->redirect('index.php');
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to Directory Search</title>
	<link href="http://192.168.1.154/callrecord/css/login.css" rel="stylesheet" type="text/css" />
</head>
<body id="login">
<div id="login_container">
<?php if(isset($_SESSION['invalid'])) { ?>
<h4 style="color:red;">Invalid Credential</h4>
<?php unset($_SESSION['invalid']); } ?>
	<div id="login_box">
		<h2>Call Recording Login</h2>
		
		<form action="process.php" method="post" accept-charset="utf-8">		<label for="username">Username</label>	
		<input type="text" name="username" value=""  />	
		<label for="password">Password</label>	
		<input type="password" name="password" value=""/>		
		<input type="submit" name="submit" value="Login"/>	
		<input type="hidden" name="action" value="Login">
		</form>		<!--p class="footer">Page rendered in <strong>0.0506</strong> seconds</p-->
	</div>
	<div id="login_footer">Solidus Call Recording Suite.</div>
</div>

</body>
</html>