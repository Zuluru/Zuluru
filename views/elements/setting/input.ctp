<?php

if (!isset ($options)) {
	$options = array();
}

// Temporarily store an ID which is unlikely to ever be used in the
// configuration. This allows us to create multiple records.
$unused_id = Configure::read ('unused_id');
if (! $unused_id) {
	$unused_id = 1000000;
} else {
	++ $unused_id;
}
Configure::write ('unused_id', $unused_id);

$id = false;
if (isset ($this->data) && is_array ($this->data)) {
	foreach ($this->data as $setting) {
		if ($setting['Setting']['category'] == $category && $setting['Setting']['name'] == $name) {
			$id = $setting['Setting']['id'];
			$options['value'] = @unserialize($setting['Setting']['value']);
			if ($options['value'] === false) {
				$options['value'] = $setting['Setting']['value'];
			}
			break;
		}
	}
}

if ($id !== false) {
	echo $this->Form->hidden("Setting.$id.id", array('value' => $id));
} else {
	$id = $unused_id;
	echo $this->Form->hidden("Setting.$id.category", array('value' => $category));
	echo $this->Form->hidden("Setting.$id.name", array('value' => $name));
}
if (isset ($person_id)) {
	echo $this->Form->hidden("Setting.$id.person_id", array('value' => $person_id));
}

if (array_key_exists ('label', $options)) {
	$options['label'] = __($options['label'], true);
} else {
	$options['label'] = __(Inflector::humanize ($name), true);
}
if (!array_key_exists ('type', $options)) {
	$options['type'] = 'text';
}

if ($options['type'] == 'radio') {
	$options['legend'] = false;
}
if (array_key_exists ('after', $options)) {
	$options['after'] = $this->Html->para(null, __($options['after'], true));
}

if ($options['type'] == 'textarea') {
	$options = array_merge (array('cols' => 70, 'rows' => 10), $options);
} else if ($options['type'] == 'text' && !array_key_exists ('size', $options)) {
	$options['size'] = 70;
}

$out = $this->ZuluruForm->input("Setting.$id.value", $options);
if ($options['type'] == 'radio') {
	$out = sprintf(
		$this->Html->tags['fieldset'], '',
		sprintf($this->Html->tags['legend'], $options['label']) . $out
	);
}

echo "$out\n";

?>
