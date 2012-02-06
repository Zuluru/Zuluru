<?php
if ((!isset($entry) || $entry === null) && (!isset($value) || $value === null)) {
	return;
}

if (!isset ($question)) {
	$question = null;
}
$max = $spirit_obj->max($question);

if (!isset($value) || $value === null) {
	if ($question === null) {
		if ($entry['entered_sotg'] === null || !$league['numeric_sotg']) {
			$value = $spirit_obj->calculate($entry);
		} else {
			$value = $entry['entered_sotg'];
		}
	} else {
		$value = $entry[$question];
	}
}
$ratio = $value / $max;
$file = $spirit_obj->symbol ($ratio);
echo $this->ZuluruHtml->icon("spirit_$file.png");

switch ($league['display_sotg']) {
	case 'symbols_only':
		if ($is_admin) {
			printf(' (%.2f)', $value);
		}
		break;

	case 'coordinator_only':
		if ($is_admin || $is_coordinator) {
			printf(' (%.2f)', $value);
		}
		break;

	case 'numeric':
	case 'all':
		printf(' (%.2f)', $value);
		break;
}
?>
