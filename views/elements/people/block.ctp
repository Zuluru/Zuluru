<?php
// Sometimes, there will be a 'Person' key, sometimes not
if (array_key_exists ('Person', $person)) {
	$person = array_merge ($person, $person['Person']);
	unset ($person['Person']);
}
$id = "people_person_{$person['id']}";

if (isset ($options)) {
	$options = array_merge (array('id' => $id, 'class' => 'trigger'), $options);
} else {
	$options = array('id' => $id, 'class' => 'trigger');
}
if (!isset($display_field)) {
	$display_field = 'full_name';
}

if (!isset($link) || $link) {
	echo $this->ZuluruHtml->link($person[$display_field],
		array('controller' => 'people', 'action' => 'view', 'person' => $person['id']),
		$options);
} else {
	echo $this->ZuluruHtml->tag('span', $person[$display_field], $options);
}

echo $this->element('tooltips');
?>
