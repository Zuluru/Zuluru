<?php

if (!isset ($options)) {
	$options = array();
}

// Temporarily store an ID which is unlikely to ever be used in the
// configuration. This allows us to create multiple records.
$unused_id = fake_id();

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

if (isset($affiliate) && $affiliate && $options['type'] != 'textarea') {
	$default = array_shift(Set::extract("/Setting[category=$category][name=$name]/value", $defaults));
	if ($options['type'] == 'date') {
		$default = date('F j', strtotime($default));
	} else if ($options['type'] == 'radio') {
		$default = $options['options'][$default];
		$options['options'][MIN_FAKE_ID] = 'Use default';
		$options['default'] = MIN_FAKE_ID;
	} else if ($options['type'] == 'select') {
		$default = $options['options'][$default];
	}
	$default = '(' . __('Default', true) . ": $default)";
	if (array_key_exists ('after', $options)) {
		$options['after'] .= " $default";
	} else {
		$options['after'] = $default;
	}
}

$help_file = VIEWS . 'elements' . DS . 'help' . DS . 'settings' . DS . $category . DS . $name . '.ctp';
if (file_exists($help_file)) {
	$help = ' ' . $this->ZuluruHtml->help(array('action' => 'settings', $category, $name));
	if (array_key_exists ('after', $options)) {
		$options['after'] = $options['after'] . $help;
	} else {
		$options['after'] = $help;
	}
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
