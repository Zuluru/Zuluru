// Functions to deal with showing and hiding option divs

function select_dropdown(container, div) {
	// Find the new role, relative to the clicked button
	var offset = container.offset();
	offset.top += 16;
	offset.left += 10;

	// Show the div and move it. Seems it has to be in that order. :-(
	div.css('display', '');
	div.offset(offset);
	// IE won't show it correctly on the first click unless we do this twice!
	div.offset(offset);

	var now = new Date();
	var start_time = now.getTime();
	jQuery('body').bind('click', function (event) {
		var now = new Date();
		if (now.getTime() > start_time + 25) {
			close_dropdown(div);
		}
	});
	jQuery('body').bind('keyup', function(event) {
		if (event.keyCode == 27) {
			close_dropdown(div);
		}
	});

	// Return false, so any link isn't followed.
	return false;
}

function close_dropdown(div) {
	div.css('display', 'none');
	jQuery('body').unbind('click');
	jQuery('body').unbind('keyup');
}
