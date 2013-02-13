/**
 * Functions for doing in-place shirt number updates
 **/

function change_number(url, container_div, number) {
	jQuery('#number').val(number);

	jQuery('#number_entry_div').dialog({
		buttons: {
			'Cancel': function() {
				jQuery('#number_entry_div').dialog('close');
			},
			'Save': function() {
				jQuery.ajax({
					dataType: 'html',
					type: 'POST',
					data: {
						'data[TeamsPerson][0][number]': jQuery('#number').val(),
					},
					context: container_div,
					success: function (data, textStatus) {
						jQuery(this).replaceWith(data);
					},
					url: url
				});
				container_div.html('...');
				jQuery('#number_entry_div').dialog('close');
			}
		},
		modal: true,
		resizable: false,
		width: 480
	});
	jQuery('#number').focus();

	// Return false, so the link isn't followed.
	return false;
}
