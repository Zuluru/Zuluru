//
// Stat-tracking functions
//

function inputChanged(input) {
	var total = 0;
	var table = input.closest('table');
	var cl = input.attr('class');
	var th = table.find('th.' + cl);

	var handler = th.data('handler');
	if (typeof(handler) == 'undefined' || typeof(window[handler]) != 'function') {
		handler = false;
	}
	table.find('input.' + cl).each(function(){
		var val = parseFloat(jQuery(this).val());
		if (!isNaN(val)) {
			if (handler) {
				total = window[handler](total, val);
			} else {
				total += val;
			}
		}
	});
//	if (!handler) {
//		total = Math.round(total*10)/10;
//	}

	var formatter = th.data('formatter');
	if (typeof(formatter) != 'undefined' && typeof(window[formatter]) == 'function') {
		total = window[formatter](total);
	}
	th.html(total);
}

function showUnapplicable() {
	jQuery('.unapplicable').show();
	jQuery('.show_unapplicable').hide();
	jQuery('.show_applicable').show();
}

function showApplicable() {
	jQuery('.unapplicable').hide();
	jQuery('.show_applicable').hide();
	jQuery('.show_unapplicable').show();
}

function showAll() {
	jQuery('[class^=attendance_status]').closest('tr').show();
	jQuery('[class^=attendance_column]').show();
	jQuery('.show_all').hide();
	jQuery('.show_attending').show();
}

function showAttending(attending) {
	jQuery('[class^=attendance_status]').not('.attendance_status_' + attending).closest('tr').hide();
	jQuery('[class^=attendance_column]').hide();
	jQuery('.show_attending').hide();
	jQuery('.show_all').show();
}

// Handler for summing minutes played
function minutes_sum(total, value)
{
	var minutes = Math.floor(total) + Math.floor(value);
	var seconds = Math.round((total + value - minutes) * 100);
	minutes += Math.floor(seconds / 60);
	seconds %= 60;
	minutes += seconds / 100;
	return minutes;
}

// Handler for formatting minutes played
function minutes_format(total)
{
	var minutes = Math.floor(total);
	var seconds = Math.round((total - minutes) * 100);
	var ret = minutes.toString() + ':';
	if (seconds < 10) {
		ret += '0';
	}
	ret += seconds.toString();
	return ret;
}

// Handler for stats that don't logically sum (games played, percentages, etc.)
function null_sum() {
	return '';
}
