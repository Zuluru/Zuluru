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

	switch ($type) {
		case 'coordinator_only':
			if ($is_admin || $is_coordinator) {
				echo $this->Html->image (Configure::read('urls.zuluru_base') . "/img/$file.png");
				printf(' (%.2f)', $value);
			}

		case 'symbols_only':
			echo $this->Html->image (Configure::read('urls.zuluru_base') . "/img/$file.png");
			break;

		case 'numeric':
		case 'all':
			echo $this->Html->image (Configure::read('urls.zuluru_base') . "/img/$file.png");
			printf(' (%.2f)', $value);
			break;
	}
}
?>
