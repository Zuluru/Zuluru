<?php
if ($is_admin) {
	echo $this->element('help/topics', array(
			'section' => 'events',
			'topics' => array(
				'connections' => array(
					'image' => 'connections_32.png',
				),
			),
	));
}
?>
