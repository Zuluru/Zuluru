<?php
// Sometimes, there will be a 'Badge' key, sometimes not
if (array_key_exists ('Badge', $badge)) {
	$badge = array_merge ($badge, $badge['Badge']);
	unset ($badge['Badge']);
}
$id = "badges_badge_{$badge['id']}";

if (isset($options)) {
	$options = array_merge (array('id' => $id, 'class' => 'trigger'), $options);
} else {
	$options = array('id' => $id, 'class' => 'trigger');
}
if (isset($max_length)) {
	$options['max_length'] = $max_length;
}
if (!isset($use_name) || !$use_name) {
	if (!isset($size)) {
		$size = '24';
	}
	$link = $this->ZuluruHtml->icon("{$badge['icon']}_$size.png");
} else {
	$link = $badge['name'];
}

echo $this->ZuluruHtml->link($link,
	array('controller' => 'badges', 'action' => 'view', 'badge' => $badge['id']),
	$options);

echo $this->element('tooltips');
?>
