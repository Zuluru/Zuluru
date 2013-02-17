/**
 * Functions for doing in-place attendance updates
 **/

function attendance_status(url, options, container_div, dedicated, future, comment) {
	var div = jQuery('#attendance_options');

	// Find the new position, relative to the clicked button
	var offset = container_div.offset();
	offset.top += 3;
	offset.left += 6;

	var func_map = {
		0:			'attendance_prepare_player',	// unknown
		1:			'attendance_prepare_player',	// attending
		2:			'attendance_prepare_player',	// absent
		3:			'attendance_prepare_sub',		// invited
		4:			'attendance_prepare_player',	// available
		5:			'attendance_prepare_player',	// no show
		'comment':	'attendance_prepare_comment'
	};

	// Hide the invalid options, add onclick handlers to valid ones
	div.children('div').each(function(item) {
		var id = jQuery(this).attr('id');
		var pos = id.lastIndexOf('_');
		var status = id.substr(pos+1);
		window[func_map[status]](url, status, options, container_div, jQuery(this), dedicated, future, comment);
	});

	// Show the div and move it. Seems it has to be in that order. :-(
	div.css('display', 'block');
	div.offset(offset);
	// IE won't show it correctly on the first click unless we do this twice!
	div.offset(offset);

	var now = new Date();
	var start_time = now.getTime();
	jQuery('body').bind('click', function (event) {
		var now = new Date();
		if (now.getTime() > start_time + 25) {
			attendance_close_status();
		}
	});
	jQuery('body').bind('keyup', function(event) {
		if (event.keyCode == 27) {
			attendance_close_status();
		}
	});

	// Return false, so the link isn't followed.
	return false;
}

function attendance_prepare_player(url, status, options, container_div, option_div, dedicated, future, comment) {
	var opt = options[status];
	if (opt == undefined) {
		 option_div.hide();
	} else {
		option_div.show();
		option_div.bind('click', function (event) {
			jQuery.ajax({
				dataType: 'html',
				type: 'POST',
				data: {
					'data[Person][status]': status,
					'data[dedicated]': dedicated
				},
				context: container_div,
				success: function (data, textStatus) {
					jQuery(this).replaceWith(data);
				},
				url: url
			});
			container_div.html('...');
			attendance_close_status();
			return false;
		});
	}
}

function attendance_prepare_sub(url, status, options, container_div, option_div, dedicated, future, comment) {
	var opt = options[status];
	if (opt == undefined) {
		 option_div.hide();
	} else {
		option_div.show();
		option_div.bind('click', function (event) {
			attendance_close_status();
			attendance_handle_sub(url, status, container_div, dedicated);
			return false;
		});
	}
}

function attendance_handle_sub(url, status, container_div, dedicated) {
	jQuery('#comment').val('');
	jQuery('#comment_to_captain').hide();
	jQuery('#comment_to_player').show();

	jQuery('#attendance_comment_div').dialog({
		buttons: {
			'Cancel': function() {
				jQuery('#attendance_comment_div').dialog('close');
			},
			'Save': function() {
				jQuery.ajax({
					dataType: 'html',
					type: 'POST',
					data: {
						'data[Person][status]': status,
						'data[Person][note]': jQuery('#comment').val(),
						'data[dedicated]': dedicated
					},
					context: container_div,
					success: function (data, textStatus) {
						jQuery(this).replaceWith(data);
					},
					url: url
				});
				container_div.html('...');
				jQuery('#attendance_comment_div').dialog('close');
			}
		},
		modal: true,
		resizable: false,
		width: 480
	});
	jQuery('#comment').focus();
}

function attendance_prepare_comment(url, status, options, container_div, option_div, dedicated, future, comment) {
	if (future) {
		option_div.show();
		option_div.bind('click', function (event) {
			attendance_close_status();
			attendance_handle_comment(url, container_div, dedicated, comment);
			return false;
		});
	} else {
		option_div.hide();
	}
}

function attendance_handle_comment(url, container_div, dedicated, comment) {
	jQuery('#comment').val(comment);
	jQuery('#comment_to_captain').show();
	jQuery('#comment_to_player').hide();

	jQuery('#attendance_comment_div').dialog({
		buttons: {
			'Cancel': function() {
				jQuery('#attendance_comment_div').dialog('close');
			},
			'Save': function() {
				jQuery.ajax({
					dataType: 'html',
					type: 'POST',
					data: {
						'data[Person][comment]': jQuery('#comment').val(),
						'data[dedicated]': dedicated
					},
					context: container_div,
					success: function (data, textStatus) {
						jQuery(this).replaceWith(data);
					},
					url: url
				});
				container_div.html('...');
				jQuery('#attendance_comment_div').dialog('close');
			}
		},
		modal: true,
		resizable: false,
		width: 480
	});
	jQuery('#comment').focus();
}

function attendance_close_status() {
	var div = jQuery('#attendance_options');
	div.children('div').each(function(item) {
		jQuery(this).unbind('click');
	});
	div.css('display', 'none');
	jQuery('body').unbind('click');
	jQuery('body').unbind('keyup');
}
