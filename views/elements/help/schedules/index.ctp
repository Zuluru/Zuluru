<?php
if ($is_coordinator || $is_admin) {
	echo $this->element('help/topics', array(
			'section' => 'schedules',
			'topics' => array(
				'add' => array(
					'title' => 'Add Games',
					'image' => 'schedule_add_32.png',
				),
				'edit' => array(
					'image' => 'edit_32.png',
				),
				'publish',
				'delete',
				'reschedule',
				'playoffs',
			),
	));
}
?>
