<?php
// If this is the first block added, add in the supporting JS
if (!Configure::read('tooltips_added')) {
	Configure::write('tooltips_added', true);

	// Add the div we'll use to load tooltip content into
	$this->ZuluruHtml->buffer ('<div id="tooltip" class="tooltip" style="display: none;"><div id="tooltip_content"></div></div>');

	// Add the "on demand" tooltip effect
	$this->Js->buffer('
jQuery.tools.tooltip.addEffect("on_demand",
	// show function
	function(done) {
		// The tooltip will not actually be shown until the load completes
		tooltip_cancelled = false;
		done.call();
	},

	// hide function
	function(done) {
		tooltip_cancelled = true;
		this.getTip().hide();
		done.call();
	}
);
	');

	if ($is_mobile) {
		// Mobile devices don't have "hover" semantics, so instead
		// we'll add a bunch of separate icons to toggle tooltips.
		$this->Js->buffer("
jQuery('.trigger').before('" . $this->ZuluruHtml->icon('popup_16.png', array('class' => 'popup_toggle')) . " ');
jQuery('.popup_toggle').tooltip({
	effect: 'on_demand',
	relative: true,
	tip: '#tooltip',
	events:{
		def: 'click, blur'
	},
	onBeforeShow: function() {
		loadTooltip('" . $this->Html->url('/') . "', this.getTrigger().next());
		// Bind a click event to the trigger to hide the tooltip again
		this.getTrigger().bind('click.hide', {tt:this}, function(event) {
			if(event.data.tt.getTip().is(':visible')) {
				event.data.tt.hide();
			} else {
				event.data.tt.getTrigger().unbind('click.hide');
			}
		});
	},
	onHide: function() {
		this.getTrigger().unbind('click.hide');
	}
});
		");
	} else {
		// Add the standard tooltip handler
		$this->Js->buffer("
jQuery('.trigger').not('.has_tooltip').tooltip({
	effect: 'on_demand',
	relative: true,
	tip: '#tooltip',
	cancelDefault: false,
	delay: 500,
	predelay: 1000,
	onBeforeShow: function() {
		loadTooltip('" . $this->Html->url('/') . "', this.getTrigger());
	},
	onShow: function() {
		if (tooltip_loaded) {
			jQuery('#tooltip').show();
		}
	}
}).dynamic({ left: { offset: [ 0, -50 ] }, right: { offset: [ 0, 50 ] } });
jQuery('.trigger').not('.has_tooltip').each(function() {
	jQuery(this).addClass('has_tooltip');
});
		");
	}
}
?>
