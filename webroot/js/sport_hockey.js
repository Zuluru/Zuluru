//
// Hockey-specific functions
//

function maxLength() { return 67; }
function defaultLength() { return maxLength(); }
function minLength() { return 33; }
function maxWidth() { return 33; }
function defaultWidth() { return 28; }
function minWidth() { return 14; }

function cornerRadius(width)
{
	return width / 3;
}

function blueLine(length)
{
	return length * 0.3 / 2;
}

function goalLine(length)
{
	return length * 0.9 / 2;
}

function layoutText(id)
{
	return null;
}

function outlinePositions(id)
{
	var position = fields[id].marker.getPosition();
	var corner_radius = cornerRadius(fields[id].width);

	var bb = new Array;
	var side = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);
	bb[0] = makePosition(side, fields[id].length / 2 - corner_radius, 90 - fields[id].angle);
	bb[1] = makePosition(side, fields[id].length / 2 - corner_radius, 270 - fields[id].angle);
	corner = makePosition(bb[1], corner_radius, 0 - fields[id].angle);
	bb = bb.concat(makeArc(corner, 180 - fields[id].angle, 270 - fields[id].angle, corner_radius));
	bb.push(makePosition(bb[bb.length-1], fields[id].width - corner_radius * 2, 0 - fields[id].angle));
	corner = makePosition(bb[bb.length-1], corner_radius, 90 - fields[id].angle);
	bb = bb.concat(makeArc(corner, 270 - fields[id].angle, 360 - fields[id].angle, corner_radius));
	bb.push(makePosition(bb[bb.length-1], fields[id].length - corner_radius * 2, 90 - fields[id].angle));
	corner = makePosition(bb[bb.length-1], corner_radius, 180 - fields[id].angle);
	bb = bb.concat(makeArc(corner, 0 - fields[id].angle, 90 - fields[id].angle, corner_radius));
	bb.push(makePosition(bb[bb.length-1], fields[id].width - corner_radius * 2, 180 - fields[id].angle));
	corner = makePosition(bb[bb.length-1], corner_radius, 270 - fields[id].angle);
	bb = bb.concat(makeArc(corner, 90 - fields[id].angle, 180 - fields[id].angle, corner_radius));
	return bb;
}

function inlinePositions(id)
{
	var blue_line = blueLine(fields[id].length);
	var goal_line = goalLine(fields[id].length);
	var position = fields[id].marker.getPosition();

	// Some fancy trigonometry to figure out where along the corner curve the goal line goes
	var corner_radius = cornerRadius(fields[id].width);
	var behind_goal = fields[id].length / 2 - goal_line;
	var chord_length = Math.sqrt(2 * corner_radius * behind_goal - behind_goal * behind_goal);
	var goal_line_offset = corner_radius - chord_length;

	var bb = new Array;
	var side = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);

	bb[0] = new Array;
	var goal_side = makePosition(side, goal_line, 270 - fields[id].angle);
	bb[0][0] = makePosition(goal_side, goal_line_offset, 0 - fields[id].angle);
	bb[0][1] = makePosition(bb[0][0], fields[id].width - 2 * goal_line_offset, 0 - fields[id].angle);

	bb[1] = new Array;
	bb[1][0] = makePosition(side, blue_line, 270 - fields[id].angle);
	bb[1][1] = makePosition(bb[1][0], fields[id].width, 0 - fields[id].angle);

	bb[2] = new Array;
	bb[2][0] = side;
	bb[2][1] = makePosition(bb[2][0], fields[id].width, 0 - fields[id].angle);

	bb[3] = new Array;
	bb[3][0] = makePosition(side, blue_line, 90 - fields[id].angle);
	bb[3][1] = makePosition(bb[3][0], fields[id].width, 0 - fields[id].angle);

	bb[4] = new Array;
	var goal_side = makePosition(side, goal_line, 90 - fields[id].angle);
	bb[4][0] = makePosition(goal_side, goal_line_offset, 0 - fields[id].angle);
	bb[4][1] = makePosition(bb[4][0], fields[id].width - 2 * goal_line_offset, 0 - fields[id].angle);

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
