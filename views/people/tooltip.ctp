<h2><?php
echo $person['full_name'];
echo $this->element('people/player_photo', array('person' => $person, 'photo' => $photo));
?></h2>
<?php
$view_contact = $is_me || $is_admin || $is_manager || $is_coordinator || $is_captain || $is_my_captain || $is_my_coordinator || $is_division_captain;

if (!empty($person['email']) &&
	($view_contact || ($is_logged_in && $person['publish_email'])))
{
	echo $this->Html->link ($person['email'], "mailto:{$person['email']}");
}
if (!empty($person['home_phone']) &&
	($view_contact || ($is_logged_in && $person['publish_home_phone'])))
{
	echo $this->Html->tag('br') . $person['home_phone'] . ' (' . __('home', true) . ')';
}
if (!empty($person['work_phone']) &&
	($view_contact || ($is_logged_in && $person['publish_work_phone'])))
{
	echo $this->Html->tag('br') . $person['work_phone'];
	if (!empty($person['work_ext'])) {
		echo ' x' . $person['work_ext'];
	}
	echo ' (' . __('work', true) . ')';
}
if (!empty($person['mobile_phone']) &&
	($view_contact || ($is_logged_in && $person['publish_mobile_phone'])))
{
	echo $this->Html->tag('br') . $person['mobile_phone'] . ' (' . __('mobile', true) . ')';
}

if ($is_logged_in) {
	echo $this->Html->tag('br');
	echo $this->Html->link(__('VCF', true), array('action' => 'vcf', 'person' => $person['id']));

	if (Configure::read('feature.annotations')) {
		if (!empty($note)) {
			// Extra paragraph tags screw up the display...
			$note = $note['Note']['note'];
			if (substr($note, 0, 3) == '<p>') {
				$note = substr($note, 3);
			}
			if (substr($note, -4) == '</p>') {
				$note = substr($note, 0, -4);
			}
		}
		if (!empty($note)) {
			echo __('Private Note', true) . ': ' . $note . $this->Html->tag('br');
			echo $this->Html->link(__('Delete Note', true), array('action' => 'delete_note', 'person' => $person['id'])) . ' / ';
			$link = __('Edit Note', true);
		} else {
			$link = __('Add Note', true);
		}
		echo ' ' . $this->Html->link($link, array('action' => 'note', 'person' => $person['id']));
	}
}

if ($is_logged_in && Configure::read('feature.badges') && !empty($badges['Badge'])) {
	echo $this->Html->tag('br');
	foreach ($badges['Badge'] as $badge) {
		echo $this->ZuluruHtml->iconLink("{$badge['icon']}_48.png", array('controller' => 'badges', 'action' => 'view', 'badge' => $badge['id']),
			array('alt' => $badge['name'], 'title' => $badge['description']));
	}
}
?>
