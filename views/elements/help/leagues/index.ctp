<?php
if ($is_admin || $is_coordinator) {
	echo $this->element('help/topics', array(
			'section' => 'leagues',
			'topics' => array(
				'edit' => array(
					'image' => 'edit_32.png',
				),
			),
	));
}
?>
