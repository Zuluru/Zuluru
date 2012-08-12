<?php
if ($is_admin) {
	echo $this->element('help/topics', array(
			'section' => 'waivers',
			'topics' => array(
				'text',
			),
	));
}
?>
