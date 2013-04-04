<?php
// Sometimes, there will be a 'League' key, sometimes not
if (array_key_exists ('League', $league)) {
	$league = array_merge ($league, $league['League']);
	unset ($league['League']);
}
$id = "leagues_league_{$league['id']}";

if (isset($options)) {
	$options = array_merge (array('id' => $id, 'class' => 'trigger'), $options);
} else {
	$options = array('id' => $id, 'class' => 'trigger');
}
if (isset($max_length)) {
	$options['max_length'] = $max_length;
}
if (!isset($field)) {
	$field = 'full_name';
}
echo $this->ZuluruHtml->link($league[$field], array('controller' => 'leagues', 'action' => 'view', 'league' => $league['id']), $options);

echo $this->element('tooltips');
?>
