<?php
if (!isset ($question)) {
	$question = null;
}
$max = $spirit_obj->max($question);

if ($value === null) {
	return;
} else {
	$ratio = $value / $max;
	$file = $spirit_obj->symbol ($ratio);
	echo $this->ZuluruHtml->icon("spirit_$file.png");

	switch ($type) {
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
}
?>
