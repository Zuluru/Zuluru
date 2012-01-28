<h2><?php
echo $person['Person']['full_name'];
if ($is_logged_in && !empty ($person['Upload']) && $person['Upload'][0]['approved']) {
	echo $this->element('people/player_photo', array('person' => $person['Person'], 'upload' => $person['Upload'][0]));
}
?></h2>
<?php
$view_contact = $is_me || $is_admin || $is_coordinator || $is_captain || $is_my_captain || $is_division_captain;

if (!empty($person['Person']['email']) &&
	($view_contact || ($is_logged_in && $person['Person']['publish_email'])))
{
	echo $this->Html->link ($person['Person']['email'], "mailto:{$person['Person']['email']}") . $this->Html->tag('br');
}
if (!empty($person['Person']['home_phone']) &&
	($view_contact || ($is_logged_in && $person['Person']['publish_home_phone'])))
{
	echo $person['Person']['home_phone'] . ' (' . __('home', true) . ')' . $this->Html->tag('br');
}
if (!empty($person['Person']['work_phone']) &&
	($view_contact || ($is_logged_in && $person['Person']['publish_work_phone'])))
{
	echo $person['Person']['work_phone'];
	if (!empty($person['Person']['work_ext'])) {
		echo ' x' . $person['Person']['work_ext'];
	}
	echo ' (' . __('work', true) . ')' . $this->Html->tag('br');
}
if (!empty($person['Person']['mobile_phone']) &&
	($view_contact || ($is_logged_in && $person['Person']['publish_mobile_phone'])))
{
	echo $person['Person']['mobile_phone'] . ' (' . __('mobile', true) . ')' . $this->Html->tag('br');
}
?>