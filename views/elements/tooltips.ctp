<?php
// If this is the first block added, add in the supporting JS
if (!Configure::read('tooltips_added')) {
	Configure::write('tooltips_added', true);

	// Add the div we'll use to load tooltip content into
	$this->ZuluruHtml->buffer ('<div id="tooltip" class="tooltip" style="display: none;"></div>');

	// Add the "dynamic" tooltip effect
	$this->Js->buffer('
$.tools.tooltip.addEffect("dynamic",
	// show function
	function(done) {
		// The tooltip will not actually be shown until the load completes
		done.call();
	},

	// hide function
	function(done) {
		this.getTip().hide();
		done.call();
	}
);
	');

	if ($is_mobile) {
		// Mobile devices don't have "hover" semantics, so instead
		// we'll add a bunch of separate icons to toggle tooltips.
		$this->Js->buffer("
$('.trigger').before('" . $this->ZuluruHtml->icon('popup_16.png', array('class' => 'popup_toggle')) . " ');
$('.popup_toggle').tooltip({
	effect: 'dynamic',
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
$('.trigger').tooltip({
	effect: 'dynamic',
	relative: true,
	tip: '#tooltip',
	cancelDefault: false,
	delay: 500,
	predelay: 500,
	onBeforeShow: function() {
		loadTooltip('" . $this->Html->url('/') . "', this.getTrigger());
	}
});
		");
	}
}
?>