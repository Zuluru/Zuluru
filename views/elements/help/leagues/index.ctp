<?php
if ($is_admin || $is_coordinator) {
	echo $this->element('help/topics', array(
			'section' => 'leagues',
			'topics' => array(
				'edit' => array(
					'image' => 'edit_32.png',
				),
				'fields' => 'Field Distribution Report',
				'approve_scores' => array(
					'image' => 'score_approve_32.png',
				),
			),
	));
}
?>
