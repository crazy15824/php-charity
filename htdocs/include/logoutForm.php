<?php require_once('vars.php'); ?>
<form name="yes" method="POST">
	<h1>هل تريد الخروج؟</h1>
	<input name="out" type="hidden" value="out" />
	<input name="yes" type="hidden" value="yes" />
	<input class="inputA" type="submit" value="نعم" style="background: #bd1919"/>
</form>
<form name="no" method="POST">
	<input name="no" type="hidden" value="no" />
	<input class="inputA" type="submit" value="لا"/>
</form>