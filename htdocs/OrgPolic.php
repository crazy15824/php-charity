<?php

include('include/header.php');
require('include/vars.php');


echo '<div style="direction: rtl;text-align:right">';
echo '<br><h1 style="text-align:center">الشروط والأحكام</h1>';

echo '<h2>* إمكانية الإقتراض :-</h2>';

echo '
<ul style="font-size: 17px;">
	<li>الحد الأقصى للقرض جرامين ذهب للفرد شهرياً.</li>
	<li>لا يمكن إضافة مقترض جديد حتى سداد نصيبه من ديون والديه.</li>
	<li>تورث الديون بالتساوي على الأبناء وان لم يكن للمتوفي أبناء فعلى الوالدين ومن ثم مناصفة على الإخوة من الأب والإخوة من الأم.</li>
	<li>سداد القروض غير مطالب به قانونياً.</li>
</ul>';

echo '<h2>* أولوية الإقتراض :-</h2>';

echo '
<ul>
	<li>تكون للأعلى في الرصيد الدائن ،</li>
	<li>ثم للأقل في الرصيد المدين ،</li>
	<li>ثم للأكثر مساهمة بالجمعية  ،</li>
	<li>ثم للأقدم في تاريخ آخر قرض ،</li>
	<li>ثم للأقدم في تاريخ طلب القرض.</li>
</ul>';

echo '</div>';

echo'<form action="Org.php">
			<input class="inputA" type="submit" value="الرجوع لصفحة الجمعية" />
		</form>';
		
echo'<form action="'.$path.'">
			<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" style="background: #4caf50" />
		</form><br>';

include('include/footer.php');