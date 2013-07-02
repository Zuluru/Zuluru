function badge_handle_comment(url) {
	jQuery('#badge_comment_div').dialog({
		buttons: {
			'Cancel': function() {
				jQuery('#badge_comment_div').dialog('close');
			},
			'Save': function() {
				jQuery.ajax({
					dataType: 'html',
					type: 'POST',
					data: {
						'data[Badge][comment]': jQuery('#comment').val(),
					},
					success: function (data, textStatus) {
						jQuery('#temp_update').html(data);
					},
					url: url
				});
				jQuery('#badge_comment_div').dialog('close');
			}
		},
		modal: true,
		resizable: false,
		width: 480
	});
	jQuery('#comment').focus();
}