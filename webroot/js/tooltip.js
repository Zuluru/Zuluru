// Functions for dealing with tooltips

var tooltip_text = new Array();
var tooltip_cancelled = false;

function loadTooltip(base, trigger) {
	var id = trigger.attr('id');
	if (tooltip_text[id] == undefined) {
		var params = id.split('_');
		$.ajax({
			type: 'GET',
			url: base + params[0] + '/tooltip/' + params[1] + ':' + params[2],
			success: function(data){
				tooltip_text[id] = data;
				if (!tooltip_cancelled) {
					$('#tooltip').html(data);
					$('#tooltip').show();
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
	} else {
		$('#tooltip').html(tooltip_text[id]);
		$('#tooltip').show();
	}
}
