<?php require_once('vars.php'); ?>
<br>
<form name="phone" method="POST">
	<input placeholder="رقم الموبايل الجديد" value="<?php echo (isset($_POST['phone'])) ? $_POST['phone'] : ''; ?>" class="inputA" style="background: white;" name="phone" type="number" required />
	<input class="inputA" type="submit" value="حــفـظ" />
</form>
<script type="text/javascript">
<!--
  document.phone.phone.focus();
//-->
</script>