<?php require_once('vars.php'); ?>
<form id="SignIn" name="login" method="POST">
	<input id="User" class="inputA" style="background: white;" name="username" type="text" placeholder="الاسم أو رقم الموبايل" value="<?php echo $user; ?>" pattern=".{4,16}" required />
	<input id="Pass" class="inputA" style="background: white;" class="pass" name="password" type="password" autocomplete="off" placeholder="كلمة السر" pattern=".{8,32}" required />
	<input class="inputA" type="submit" value="تسجيل دخول"  />
</form>
<form name="signup" action="Signup.php">
	<input class="inputA" type="submit" value="حساب جديد" style="background: #00bcd4" />
</form>
<script type="text/javascript">
	<!--
		document.login.username.focus();
	//-->	        
</script>