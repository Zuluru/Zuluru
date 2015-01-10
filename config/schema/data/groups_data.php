<?php
class GroupsData {

	public $table = 'groups';

	public $records = array(
		array(
			'name' => 'Player',
			'active' => 1,
			'level' => 0,
			'description' => 'You will be participating as a player.',
		),
		array(
			'name' => 'Parent/Guardian',
			'active' => 1,
			'level' => 0,
			'description' => 'You have one or more children who will be participating as players.',
		),
		array(
			'name' => 'Coach',
			'active' => 1,
			'level' => 0,
			'description' => 'You will be coaching a team that you are not a player on.',
		),
		array(
			'name' => 'Volunteer',
			'active' => 1,
			'level' => 1,
			'description' => 'You plan to volunteer to help organize or run things.',
		),
		array(
			'name' => 'Official',
			'active' => 1,
			'level' => 3,
			'description' => 'You will be acting as an in-game official.',
		),
		array(
			'name' => 'Manager',
			'active' => 1,
			'level' => 5,
			'description' => 'You are an organizational manager with some admin privileges.',
		),
		array(
			'name' => 'Administrator',
			'active' => 1,
			'level' => 10,
			'description' => 'You are an organizational administrator with absolute privileges.',
		),
	);

}
?>
