// Functions for dealing with tooltips

var tooltip_text = new Array();

function loadTooltip(base, trigger, callback) {
	var id = trigger.attr('id');
	if (tooltip_text[id] == undefined) {
		// Put a timer in here to only start loading if the mouse stays for 500 ms.
		// Otherwise, Ajax calls start firing immediately whenever the mouse is
		// passed over such a link, causing server load for no reason.
		setTimeout(function() {
			if (!trigger.data('wait_for_tooltip')) {
				return;
			}
			var params = id.split('_');
			jQuery.ajax({
				type: 'GET',
				url: base + params[0] + '/tooltip/' + params[1] + ':' + params[2],
				success: function(data){
					tooltip_text[id] = data;
					if (trigger.data('wait_for_tooltip')) {
						callback(data);
						trigger.data('tooltip_displayed', true);
						trigger.data('wait_for_tooltip', false);
					}
				},
				error: function(message){
					// If the status is 0, it's probably because the user
					// clicked a link before the tip text loaded
					if (message.status != 0) {
						alert(message.statusText);
					}
				}
			});
		}, 500);
	} else {
		callback(tooltip_text[id]);
		trigger.data('tooltip_displayed', true);
		trigger.data('wait_for_tooltip', false);
	}
}
