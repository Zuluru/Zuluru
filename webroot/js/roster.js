/**
 * Functions for doing in-place roster changes
 **/

/**
 * Functions for doing in-place roster changes
 **/

function roster_position(url, options, container_div, position) {
	var div = jQuery('#roster_position_options');

	// Find the new position, relative to the clicked button
	var offset = container_div.offset();
	offset.top += 16;
	offset.left += 10;

	// Hide the invalid options, add onclick handlers to valid ones
	div.children('div').each(function(item) {
		var id = jQuery(this).attr('id');
		var pos = id.lastIndexOf('_');
		var position = id.substr(pos+1);
		var opt = options[position];
		if (opt == undefined) {
			 jQuery(this).hide();
		} else {
			jQuery(this).show();
			jQuery(this).bind('click', function (event) {
				roster_close_position();
				jQuery.ajax({
					dataType: 'html',
					type: 'POST',
					data: {
						'data[Person][position]': position,
					},
					context: container_div,
					success: function (data, textStatus) {
						jQuery(this).replaceWith(data);
					},
					url: url
				});
				container_div.html('...');
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
	jQuery('body').bind('click', function (event) {
		var now = new Date();
		if (now.getTime() > start_time + 25) {
			roster_close_position();
		}
	});
	jQuery('body').bind('keyup', function(event) {
		if (event.keyCode == 27) {
			roster_close_position();
		}
	});

	// Return false, so the link isn't followed.
	return false;
}

function roster_close_position() {
	var div = jQuery('#roster_position_options');
	div.children('div').each(function(item) {
		jQuery(this).unbind('click');
	});
	div.css('display', 'none');
	jQuery('body').unbind('click');
	jQuery('body').unbind('keyup');
}

function roster_role(url, options, container_div, role) {
	var div = jQuery('#roster_role_options');

	// Find the new role, relative to the clicked button
	var offset = container_div.offset();
	offset.top += 16;
	offset.left += 10;

	// Hide the invalid options, add onclick handlers to valid ones
	div.children('div').each(function(item) {
		var id = jQuery(this).attr('id');
		var pos = id.lastIndexOf('_');
		var role = id.substr(pos+1);
		var opt = options[role];
		if (opt == undefined) {
			 jQuery(this).hide();
		} else {
			jQuery(this).show();
			jQuery(this).bind('click', function (event) {
				roster_close_role();
				if (!confirm('Are you sure? Not all roster changes are reversible.')) {
					return false;
				}
				jQuery.ajax({
					dataType: 'html',
					type: 'POST',
					data: {
						'data[Person][role]': role,
					},
					context: container_div,
					success: function (data, textStatus) {
						jQuery(this).replaceWith(data);
					},
					url: url
				});
				container_div.html('...');
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
	jQuery('body').bind('click', function (event) {
		var now = new Date();
		if (now.getTime() > start_time + 25) {
			roster_close_role();
		}
	});
	jQuery('body').bind('keyup', function(event) {
		if (event.keyCode == 27) {
			roster_close_role();
		}
	});

	// Return false, so the link isn't followed.
	return false;
}

function roster_close_role() {
	var div = jQuery('#roster_role_options');
	div.children('div').each(function(item) {
		jQuery(this).unbind('click');
	});
	div.css('display', 'none');
	jQuery('body').unbind('click');
	jQuery('body').unbind('keyup');
}
