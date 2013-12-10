<?php if (count($options) > 1): ?>
<form class="selector">
<?php
	$id = low(str_replace(' ', '_', $title));
	$new_options = array();
	foreach ($options as $option) {
		$new_options[low(str_replace(' ', '_', $option))] = $option;
	}

	echo $this->Form->input('select', array(
			'id' => $id,
			'label' => __($title, true) . ':',
			'empty' => __('Show All', true),
			'options' => $new_options,
	));
?>
</form>
<?php
	// If this is the first selector added, add in the supporting JS
	if (!Configure::read('selector_added')) {
		Configure::write('selector_added', true);
		echo $this->Html->scriptBlock("
function selector_changed() {
	var hide_selector = '';
	var show_selector = '';
	jQuery('form.selector select').each(function() {
		var id = jQuery(this).attr('id');
		var setting = jQuery(this).val();
		if (setting != '') {
			show_selector += '.' + id + '_' + setting;
		}
	});
	if (show_selector == '') {
		jQuery('[class^=\"selector_\"]').css('display', '');
	} else {
		jQuery('[class^=\"selector_\"]').css('display', 'none');
		jQuery(show_selector).css('display', '');
	}
}
");
	}
	$this->Js->get("#$id")->event('change', 'selector_changed();');
endif;
?>