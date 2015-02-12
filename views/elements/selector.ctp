<?php if (count($options) > 1): ?>
	<?php if (!isset($include_form) || $include_form): ?>
<form class="selector">
	<?php endif; ?>
<span class="selector">
<?php
	$id = low(Inflector::slug($title));
	$new_options = array();
	foreach ($options as $option) {
		$new_options[low(Inflector::slug($option))] = $option;
	}

	$input_options = array(
			'id' => $id,
			'label' => __($title, true) . ':',
			'options' => $new_options,
	);
	if (!isset($include_empty) || $include_empty) {
		$input_options['empty'] = __('Show All', true);
	}
	echo $this->Form->input($id, $input_options);
?>
</span>
	<?php if (!isset($include_form) || $include_form): ?>
</form>
	<?php endif; ?>
<?php
	// If this is the first selector added, add in the supporting JS
	if (!Configure::read('selector_added')) {
		Configure::write('selector_added', true);
		echo $this->Html->scriptBlock("
function selector_changed() {
	var hide_selector = '';
	var show_selector = '';
	jQuery('span.selector select').each(function() {
		var id = jQuery(this).attr('id');
		var setting = jQuery(this).val();
		if (setting != '') {
			show_selector += '.' + id + '_' + setting;
		}
	});
	if (show_selector == '') {
		jQuery('[class*=\"selector_\"]').css('display', '');
		jQuery('[class*=\"selector_\"]').filter(':input').removeAttr('disabled');
	} else {
		jQuery('[class*=\"selector_\"]').css('display', 'none');
		jQuery(show_selector).css('display', '');
		jQuery('[class*=\"selector_\"]').filter(':input').attr('disabled', 'disabled');
		jQuery(show_selector).filter(':input').removeAttr('disabled');
	}
}
");
		$this->Js->buffer('selector_changed();');
	}
	$this->Js->get("#$id")->event('change', 'selector_changed();');
endif;
?>