<?php
if ($is_admin) {
	echo $this->element('help/topics', array(
			'section' => 'rules',
			'topics' => array(
				'rules' => 'Rule Definitions',
			),
	));
}
?>
