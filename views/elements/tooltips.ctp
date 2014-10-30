<?php
// If this is the first block added, add in the supporting JS
if (!Configure::read('tooltips_added')) {
	Configure::write('tooltips_added', true);

	if ($is_mobile) {
		// Mobile devices don't have "hover" semantics, so instead
		// we'll add a bunch of separate icons to toggle tooltips.
		$this->Js->buffer("
jQuery('.trigger').before('" . $this->ZuluruHtml->icon('popup_16.png', array('class' => 'tooltip_toggle')) . " ');
jQuery('.tooltip_toggle').tooltip({
	items: '.tooltip_toggle',
	position: { my: 'center bottom', at: 'center top-5' },
	content: function(callback) {
		jQuery(this).next().data('wait_for_tooltip', true);
		loadTooltip('" . $this->Html->url('/') . "', jQuery(this).next(), callback);
	}
})
// Handle clicks to open/close tooltips
.on('click', function() {
	var visible = jQuery(this).next().data('tooltip_displayed');
	// Close all other visible tooltips
	jQuery('.tooltip_toggle').each(function(){
		jQuery(this).next().data('wait_for_tooltip', false);
		if (jQuery(this).next().data('tooltip_displayed')) {
			jQuery(this).next().data('tooltip_displayed', false);
			jQuery(this).tooltip('close');
		}
	});
	if (!visible) {
		jQuery(this).tooltip('open');
	}
	return false;
});
// Turn off the default hover mechanic
jQuery('.tooltip_toggle').tooltip('disable');
		");
	} else {
		// Add the standard tooltip handler
		$this->Js->buffer("
jQuery('#zuluru').on({
	mouseleave: function() {
		jQuery(this).data('wait_for_tooltip', false);
	},
	focusout: function() {
		jQuery(this).data('wait_for_tooltip', false);
	}
},'.trigger');
jQuery('#zuluru').tooltip({
	items: '.trigger',
	show: { delay: 500 },
	hide: { delay: 500 },
	position: { my: 'center bottom', at: 'center top-5' },
	content: function(callback) {
		jQuery(this).data('wait_for_tooltip', true);
		loadTooltip('" . $this->Html->url('/') . "', jQuery(this), callback);
	},
	// Adapted from http://stackoverflow.com/a/15014759
	close: function(event, ui) {
		ui.tooltip.hover(
			function () {
				jQuery(this).stop(true).fadeTo(500, 1);
			},
			function () {
				jQuery(this).fadeOut('500', function(){ jQuery(this).remove(); })
			}
		);
	}
});
		");
	}
}
?>
