//
// Rugby-specific functions
//

function maxLength() { return 134; }
function defaultLength() { return maxLength(); }
function minLength() { return 60; }
function maxWidth() { return 74; }
function defaultWidth() { return maxWidth(); }
function minWidth() { return 35; }

function fieldLength(length)
{
	return length - inGoalLength(length) * 2;
}

function inGoalLength(length)
{
	if (length >= 122) {
		return Math.floor((length - 110) / 2);
	}
	return 6;
}

function layoutText(id)
{
	if (fields[id].length == 0) {
		return null;
	}
	return '<p>Field width: ' + fields[id].width + ' yards' +
			'<br>Field of Play length: ' + fieldLength(fields[id].length) + ' yards' +
			'<br>In Goal Area length: ' + inGoalLength(fields[id].length) + ' yards';
}

function outlinePositions(id)
{
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	var side = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);
	bb[0] = makePosition(side, fields[id].length / 2, 270 - fields[id].angle);
	bb[1] = makePosition(bb[0], fields[id].width, 0 - fields[id].angle);
	bb[2] = makePosition(bb[1], fields[id].length, 90 - fields[id].angle);
	bb[3] = makePosition(bb[2], fields[id].width, 180 - fields[id].angle);
	return bb;
}

function inlinePositions(id)
{
	var length = fieldLength(fields[id].length);
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	var side = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);

	bb[0] = new Array;
	bb[0][0] = makePosition(side, length / 2, 270 - fields[id].angle);
	bb[0][1] = makePosition(bb[0][0], fields[id].width, 0 - fields[id].angle);

	bb[1] = new Array;
	bb[1][0] = makePosition(side, length / 2, 90 - fields[id].angle);
	bb[1][1] = makePosition(bb[1][0], fields[id].width, 0 - fields[id].angle);

	return bb;
}

function updateForm()
{
	jQuery('#show_angle').html(fields[current].angle);
	jQuery('#show_width').html(fields[current].width);
	jQuery('#show_length').html(fields[current].length);
	jQuery('#show_field').html(fieldLength(fields[current].length));
	jQuery('#show_ingoal').html(inGoalLength(fields[current].length));
}

function saveField()
{
	if (current != 0) {
		fields[current].angle = parseInt (jQuery('#show_angle').html());
		fields[current].width = parseInt (jQuery('#show_width').html());
		fields[current].length = parseInt (jQuery('#show_length').html());
	}
}
