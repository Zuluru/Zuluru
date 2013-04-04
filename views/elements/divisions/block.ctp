<?php
// Sometimes, there will be a 'Division' key, sometimes not
if (array_key_exists ('Division', $division)) {
	$division = array_merge ($division, $division['Division']);
	unset ($division['Division']);
}
$id = "divisions_division_{$division['id']}";

if (isset($options)) {
	$options = array_merge (array('id' => $id, 'class' => 'trigger'), $options);
} else {
	$options = array('id' => $id, 'class' => 'trigger');
}
if (isset($max_length)) {
	$options['max_length'] = $max_length;
}
if (!isset($link_text)) {
	if (!isset($field)) {
		$field = 'name';
	}
	$link_text = $division[$field];
}
if (!isset($url)) {
	$division_count = $this->requestAction(array('controller' => 'leagues', 'action' => 'division_count'),
			array('named' => array('league' => $division['league_id'])));
	if ($division_count == 1) {
		$url = array('controller' => 'leagues', 'action' => 'view', 'league' => $division['league_id']);
	} else {
		$url = array('controller' => 'divisions', 'action' => 'view', 'division' => $division['id']);
	}
}
echo $this->ZuluruHtml->link($link_text, $url, $options);

echo $this->element('tooltips');
?>
