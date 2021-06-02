<?php require_once('vars.php'); ?>
<br>
<form id="SignUp" name="signup" method="POST">
	<label>اختار اسم لا يقل عن 4 حروف انجليزي</label>
	<input placeholder="الاســـــم" value="<?php echo (isset($_POST['user'])) ? $_POST['user'] : ''; ?>" class="inputA" style="background: white;" name="user" type="text" pattern=".{3,16}" required />
	<label>وكلمة السر لا تقل عن 8 حروف</label>
	<input placeholder="كلمة السر" value="<?php echo (isset($_POST['pas1'])) ? $_POST['pas1'] : ''; ?>" class="inputA" style="background: white;" name="pas1" type="Password" pattern=".{8,32}" required />
	<label>سجل كلمة السر كمان مرة للتأكيد</label>
	<input placeholder="تأكيد كلمة السر" value="<?php echo (isset($_POST['pas2'])) ? $_POST['pas2'] : ''; ?>" class="inputA" style="background: white;" name="pas2" type="Password" pattern=".{8,32}" required />
	<label>سجل رقم موبايلك</label>
	<input placeholder="رقم الموبايل" value="<?php echo (isset($_POST['phone'])) ? $_POST['phone'] : ''; ?>" class="inputA" style="background: white;" name="phone" type="number" required />
	<input class="inputA" type="submit" value="انشاء الحساب" style="background: #00bcd4" />
</form>
<script type="text/javascript">
<!--
  document.signup.user.focus();
//-->
</script>