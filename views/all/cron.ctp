<?php
$empty = true;
foreach ($controllers as $controller) {
	if (!empty(${$controller})) {
		echo ${$controller};
		$empty = false;
	}
}

if ($empty) {
	__('Nothing to report today.');
}
?>
