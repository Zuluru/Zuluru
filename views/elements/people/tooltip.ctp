<div id="<?php echo $id; ?>" class="tooltip">
<h2><?php
echo $person['full_name'];
if ($is_logged_in && !empty ($person['Upload']) && $person['Upload'][0]['approved']) {
	echo $this->element('people/player_photo', array('person' => $person, 'upload' => $person['Upload'][0]));
}
?></h2>
<?php
// Some of these might not be set, depending on where we're coming from
$view_contact = ((isset($my_id) && $my_id == $person['id'])
	|| $is_admin
	|| (isset($is_coordinator) && $is_coordinator)
	|| (isset($is_captain) && $is_captain)
	|| (isset($is_league_captain) && $is_league_captain && in_array($person['TeamsPerson']['position'], Configure::read('privileged_roster_positions')))
);
if (!empty($person['email']) &&
	($view_contact || ($is_logged_in && $person['publish_email'])))
{
	echo $this->Html->link ($person['email'], "mailto:{$person['email']}") . $this->Html->tag('br');
}
if (!empty($person['home_phone']) &&
	($view_contact || ($is_logged_in && $person['publish_home_phone'])))
{
	echo $person['home_phone'] . ' (' . __('home', true) . ')' . $this->Html->tag('br');
}
if (!empty($person['work_phone']) &&
	($view_contact || ($is_logged_in && $person['publish_work_phone'])))
{
	echo $person['work_phone'];
	if (!empty($person['work_ext'])) {
		echo ' x' . $person['work_ext'];
	}
	echo ' (' . __('work', true) . ')' . $this->Html->tag('br');
}
if (!empty($person['mobile_phone']) &&
	($view_contact || ($is_logged_in && $person['publish_mobile_phone'])))
{
	echo $person['mobile_phone'] . ' (' . __('mobile', true) . ')' . $this->Html->tag('br');
}
?>
</div>