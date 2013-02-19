<?php
echo $this->element('help/topics', array(
		'section' => 'teams',
		'topics' => array(
			'joining_teams',
			'my_teams',
			'edit' => array(
				'image' => 'edit_32.png',
			),
			'roster_add' => array(
				'title' => 'Add Player',
				'image' => 'roster_add_32.png',
			),
			'roster_role',
		),
));
?>
