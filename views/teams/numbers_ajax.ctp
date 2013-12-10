<?php
if (isset($number)) {
	$person['TeamsPerson']['number'] = $number;
	echo $this->element('people/number', compact('person'));
} else {
	__('FAIL');
}
?>
