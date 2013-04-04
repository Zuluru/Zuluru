<?php
foreach ($fields as $field => $options) {
	// TODOCSS: Better CSS to eliminate the need for this?
	if (array_key_exists ('after', $options)) {
		$options['after'] = $this->Html->para(null, $options['after']);
	}
	echo $this->ZuluruForm->input("Division.$field", $options);
}
?>
