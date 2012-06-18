//
// Ultimate-specific functions
//

function maxLength() { return 120; }
function minLength() { return 50; }
function maxWidth() { return 40; }
function minWidth() { return 25; }

function fieldLength(length)
{
	return length - endzoneLength(length) * 2;
}

function endzoneLength(length)
{
	return Math.floor(length * 5 / 12 / 2);
}

function layoutText(id)
{
	return '<p>Field width: ' + fields[id].width + ' yards' +
			'<br>Field length: ' + fieldLength(fields[id].length) + ' yards' +
			'<br>End zone length: ' + endzoneLength(fields[id].length) + ' yards';
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
	bb[0] = makePosition(side, length / 2, 270 - fields[id].angle);
	bb[1] = makePosition(bb[0], fields[id].width, 0 - fields[id].angle);
	bb[2] = makePosition(bb[1], length, 90 - fields[id].angle);
	bb[3] = makePosition(bb[2], fields[id].width, 180 - fields[id].angle);
	return bb;
}

function updateForm()
{
	$('#show_angle').html(fields[current].angle);
	$('#show_width').html(fields[current].width);
	$('#show_length').html(fields[current].length);
	$('#show_field').html(fieldLength(fields[current].length));
	$('#show_endzone').html(endzoneLength(fields[current].length));
}

function saveField()
{
	if (current != 0) {
		fields[current].angle = parseInt ($('#show_angle').html());
		fields[current].width = parseInt ($('#show_width').html());
		fields[current].length = parseInt ($('#show_length').html());
	}
}
