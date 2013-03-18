//
// Baseball-specific functions
//

function maxLength() { return 145; }
function defaultLength() { return 120; }
function minLength() { return 80; }
function maxWidth() { return 30; }
function defaultWidth() { return maxWidth(); }
function minWidth() { return 15; }

function layoutText(id)
{
	if (fields[id].length == 0) {
		return null;
	}
	return '<p>Base paths: ' + fields[id].width * 3 + ' feet' +
			'<br>Outfield: ' + fields[id].length * 3 + ' feet';
}

function mound(width)
{
	return width * 60.5 / 90;
}

function outlinePositions(id)
{
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	var home = makePosition(position, mound(fields[id].width), 270 - fields[id].angle);
	bb[0] = home;
	bb[1] = makePosition(bb[0], fields[id].length, 45 - fields[id].angle);
	bb = bb.concat(makeArc(home, 45 - fields[id].angle, 135 - fields[id].angle, fields[id].length));
	bb.push(home);
	return bb;
}

function inlinePositions(id)
{
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	var home = makePosition(position, mound(fields[id].width), 270 - fields[id].angle);

	bb[0] = new Array;
	bb[0][0] = makePosition(home, fields[id].width, 45 - fields[id].angle);
	bb[0][1] = makePosition(bb[0][0], fields[id].width, 135 - fields[id].angle);
	bb[0][2] = makePosition(bb[0][1], fields[id].width, 225 - fields[id].angle);

	bb[1] = makeArc(makePosition(home, mound(fields[id].width), 90 - fields[id].angle), 19 - fields[id].angle, 161 - fields[id].angle, fields[id].width * 95 / 90);

	return bb;
}

function updateForm()
{
	jQuery('#show_angle').html(fields[current].angle);
	jQuery('#show_width').html(fields[current].width);
	jQuery('#show_length').html(fields[current].length);
}

function saveField()
{
	if (current != 0) {
		fields[current].angle = parseInt (jQuery('#show_angle').html());
		fields[current].width = parseInt (jQuery('#show_width').html());
		fields[current].length = parseInt (jQuery('#show_length').html());
	}
}

// Handler for summing innings pitched on the stats entry page
function innings_sum(total, value)
{
	var innings = Math.floor(total) + Math.floor(value);
	var outs = Math.round((total + value - innings) * 10);
	innings += Math.floor(outs / 3);
	outs %= 3;
	innings += outs / 10;
	return innings;
}