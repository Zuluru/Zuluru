<?php
// Sometimes, there will be a 'Person' key, sometimes not
if (array_key_exists ('Person', $person)) {
	$person = array_merge ($person, $person['Person']);
	unset ($person['Person']);
}
$id = "person{$person['id']}";

if (isset ($options)) {
	$options = array_merge (array('class' => $id), $options);
} else {
	$options = array('class' => $id);
}
if (!isset($display_field)) {
	$display_field = 'full_name';
}
echo $this->ZuluruHtml->link($person[$display_field],
	array('controller' => 'people', 'action' => 'view', 'person' => $person['id']),
	$options);

// Global variable. Ew.
global $person_blocks_shown;
if (!isset($person_blocks_shown)) {
	$person_blocks_shown = array();
}
if (!in_array($person['id'], $person_blocks_shown)) {
	$person_blocks_shown[] = $person['id'];
?>
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
<?php
	$this->Js->buffer("
$('.$id').tooltip({
	cancelDefault: false,
	delay: 1,
	predelay: 500,
	relative: true,
	tip: '#$id'
});
");
}
?>
