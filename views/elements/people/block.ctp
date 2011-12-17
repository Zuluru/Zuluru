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
	$this->ZuluruHtml->buffer($this->element('people/tooltip', compact('person', 'id')));
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
