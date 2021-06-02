<?php require_once('vars.php'); ?>
<form name="change" method="POST">
	<input value="<?php echo (isset($_POST['OldPass'])) ? $_POST['OldPass'] : ''; ?>" class="inputA" style="background: white;" name="OldPass" type="password" autocomplete="off" placeholder="كلمة السر الحالية" pattern=".{8,32}" required />
	<input value="<?php echo (isset($_POST['NewPass1'])) ? $_POST['NewPass1'] : ''; ?>"class="inputA" style="background: white;" name="NewPass1" type="password" autocomplete="off" placeholder="كلمة السر الجديدة" pattern=".{8,32}" required />
	<input value="<?php echo (isset($_POST['NewPass2'])) ? $_POST['NewPass2'] : ''; ?>"class="inputA" style="background: white;" name="NewPass2" type="password" autocomplete="off" placeholder="كلمة السر الجديدة تاني" pattern=".{8,32}" required />
	<input class="inputA" type="submit" value="حـفـظ" />
</form>

<script type="text/javascript">
	<!--
		document.change.OldPass.focus();
	//-->	        
</script>