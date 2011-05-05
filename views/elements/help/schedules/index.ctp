<?php
if ($is_coordinator || $is_admin) {
	echo $this->element('help/topics', array(
			'section' => 'schedules',
			'topics' => array(
				'add' => 'Add Games',
			),
	));
}
?>
