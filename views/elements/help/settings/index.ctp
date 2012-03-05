<?php
if ($is_admin) {
	echo $this->element('help/topics', array(
			'section' => 'settings/email',
			'topics' => array(
				'emogrifier',
			),
	));

	echo $this->element('help/topics', array(
			'section' => 'settings/feature',
			'topics' => array(
				'tiny_mce' => 'TinyMCE',
			),
	));
}
?>
