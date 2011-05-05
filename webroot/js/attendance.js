/**
 * Functions for doing in-place attendance updates
 **/

function attendance_status(url, status, options, ths) {
	var div = $('#attendance_options');

	// Find the new position, relative to the clicked button
	var offset = ths.offset();
	offset.top += 3;
	offset.left += 6;

	// Hide the invalid options, add onclick handlers to valid ones
	div.children('div').each(function(item) {
		var id = $(this).attr('id');
		var pos = id.lastIndexOf('_');
		id = id.substr(pos+1);
		var opt = options[id];
		if (opt == undefined) {
			$(this).hide();
		} else {
			$(this).show();
			$(this).bind('click', function (event) {
				$.ajax({
					dataType: 'html',
					type: 'POST',
					data: {
						'data[Person][status]': id
					},
					context: ths,
					success: function (data, textStatus) {
						$(this).replaceWith(data);
					},
					url: url
				});
				ths.html('...');
				close_attendance_status();
				return false;
			});
		}
	});

	// Show the div and move it. Seems it has to be in that order. :-(
	div.css('display', 'block');
	div.offset(offset);
	// IE won't show it correctly on the first click unless we do this twice!
	div.offset(offset);

	var now = new Date();
	var start_time = now.getTime();
	$('body').bind('click', function (event) {
		var now = new Date();
		if (now.getTime() > start_time + 25) {
			close_attendance_status();
		}
	});
	$('body').bind('keyup', function(event) {
		if (event.keyCode == 27) {
			close_attendance_status();
		}
	});

	// Return false, so the link isn't followed.
	return false;
}

function close_attendance_status() {
	var div = $('#attendance_options');
	div.children('div').each(function(item) {
		$(this).unbind('click');
	});
	div.css('display', 'none');
	$('body').unbind('click');
	$('body').unbind('keyup');
}