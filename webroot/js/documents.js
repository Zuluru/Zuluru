function document_handle_comment(url) {
	jQuery('#document_comment_div').dialog({
		buttons: {
			'Cancel': function() {
				jQuery('#document_comment_div').dialog('close');
			},
			'Save': function() {
				jQuery.ajax({
					dataType: 'html',
					type: 'POST',
					data: {
						'data[Document][comment]': jQuery('#comment').val(),
					},
					success: function (data, textStatus) {
						jQuery('#temp_update').html(data);
					},
					url: url
				});
				jQuery('#document_comment_div').dialog('close');
			}
		},
		modal: true,
		resizable: false,
		width: 480
	});
	jQuery('#comment').focus();
}