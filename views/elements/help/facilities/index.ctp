<?php
if ($is_admin) {
	echo $this->element('help/topics', array(
			'section' => 'facilities',
			'topics' => array(
				'edit' => array(
					'image' => 'edit_32.png',
				),
			),
	));
}
?>
