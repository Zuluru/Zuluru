<h2><?php
echo $person['Person']['full_name'];
if ($is_logged_in && !empty ($person['Upload']) && $person['Upload'][0]['approved']) {
	echo $this->element('people/player_photo', array('person' => $person['Person'], 'upload' => $person['Upload'][0]));
}
?></h2>
<?php
$view_contact = $is_me || $is_admin || $is_manager || $is_coordinator || $is_captain || $is_my_captain || $is_division_captain;

if (!empty($person['Person']['email']) &&
	($view_contact || ($is_logged_in && $person['Person']['publish_email'])))
{
	echo $this->Html->link ($person['Person']['email'], "mailto:{$person['Person']['email']}");
}
if (!empty($person['Person']['home_phone']) &&
	($view_contact || ($is_logged_in && $person['Person']['publish_home_phone'])))
{
	echo $this->Html->tag('br') . $person['Person']['home_phone'] . ' (' . __('home', true) . ')';
}
if (!empty($person['Person']['work_phone']) &&
	($view_contact || ($is_logged_in && $person['Person']['publish_work_phone'])))
{
	echo $this->Html->tag('br') . $person['Person']['work_phone'];
	if (!empty($person['Person']['work_ext'])) {
		echo ' x' . $person['Person']['work_ext'];
	}
	echo ' (' . __('work', true) . ')';
}
if (!empty($person['Person']['mobile_phone']) &&
	($view_contact || ($is_logged_in && $person['Person']['publish_mobile_phone'])))
{
	echo $this->Html->tag('br') . $person['Person']['mobile_phone'] . ' (' . __('mobile', true) . ')';
}

if ($is_logged_in && Configure::read('feature.annotations')) {
	echo $this->Html->tag('br');
	if (!empty($person['Note'])) {
		echo $this->Html->link(__('Delete Note', true), array('action' => 'delete_note', 'person' => $person['Person']['id'])) . ' / ';
		$link = 'Edit Note';
	} else {
		$link = 'Add Note';
	}
	echo $this->Html->link(__($link, true), array('action' => 'note', 'person' => $person['Person']['id']));
}

if ($is_logged_in && Configure::read('feature.badges') && !empty($person['Badge'])) {
	echo $this->Html->tag('br');
	foreach ($person['Badge'] as $badge) {
		echo $this->ZuluruHtml->iconLink("{$badge['icon']}_48.png", array('controller' => 'badges', 'action' => 'view', 'badge' => $badge['id']),
			array('alt' => $badge['name'], 'title' => $badge['description']));
	}
}
?>