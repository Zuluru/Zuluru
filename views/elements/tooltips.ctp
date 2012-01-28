<?php
// If this is the first block added, add in the supporting JS
if (!Configure::read('tooltips_added')) {
	Configure::write('tooltips_added', true);
	$this->ZuluruHtml->buffer ('<div id="tooltip" class="tooltip" style="display: none;"></div>');

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

$(".trigger").tooltip({
	effect: "dynamic",
	cancelDefault: false,
	delay: 500,
	predelay: 500,
	relative: true,
	onBeforeShow: function() { loadTooltip("' . $this->Html->url('/') . '", this); },
	tip: "#tooltip"
});
');
}
?>