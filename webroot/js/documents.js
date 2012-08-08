function document_handle_comment(url) {
	$('#document_comment_div').dialog({
		buttons: {
			'Cancel': function() {
				$('#document_comment_div').dialog('close');
			},
			'Save': function() {
				$.ajax({
					dataType: 'html',
					type: 'POST',
					data: {
						'data[Document][comment]': $('#comment').val(),
					},
					success: function (data, textStatus) {
						$('#temp_update').html(data);
					},
					url: url
				});
				$('#document_comment_div').dialog('close');
			}
		},
		modal: true,
		resizable: false,
		width: 480
	});
	$('#comment').focus();
}