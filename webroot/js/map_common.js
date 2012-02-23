var map;

function initialize()
{
	resizeMap();
	map = new google.maps.Map(document.getElementById('map'));
}

// Swiped this from the Google Maps page
function resizeMap()
{
	var offset = 0;
	for (var elem = document.getElementById('map'); elem != null; elem = elem.offsetParent)
	{
		offset += elem.offsetTop;
	}
	var windowHeight = getWindowHeight();

	var height = windowHeight - offset - 10;
	if (height >= 0)
	{
		document.getElementById('map').style.height = height + 'px';
	}
}

// Swiped this from the Google Maps page
function getWindowHeight()
{
	if (window.self && self.innerHeight)
	{
		return self.innerHeight;
	}
	if (document.documentElement && document.documentElement.clientHeight)
	{
		return document.documentElement.clientHeight;
	}
	return 0;
}

function fieldLength(length)
{
	return length - endzoneLength(length) * 2;
}

function endzoneLength(length)
{
	return Math.floor(length * 5 / 12 / 2);
}

function makePosition(start, distance, angle)
{
	distance *= 0.9144;	// convert from yards to metres
	return google.maps.geometry.spherical.computeOffset(start, distance, 90 - angle);
}

// Variables that must be set by the calling page
var name = '';
var address = '';
var full_address = '';
var fields = Array();
var current = 0;

// Variables with defaults that may be overridden by the calling page
var zuluru_path = '/zuluru';

// Parking locations
var parking = Array();

function positions(id)
{
	var length = fieldLength(fields[id].length);
	var endzone = endzoneLength(fields[id].length);
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	bb[1] = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);
	bb[0] = makePosition(bb[1], fields[id].length / 2, 270 - fields[id].angle);
	bb[1] = makePosition(bb[0], fields[id].width, 0 - fields[id].angle);
	bb[2] = makePosition(bb[1], fields[id].length, 90 - fields[id].angle);
	bb[3] = makePosition(bb[0], fields[id].length, 90 - fields[id].angle);
	bb[4] = makePosition(bb[1], endzone, 90 - fields[id].angle);
	bb[5] = makePosition(bb[0], endzone, 90 - fields[id].angle);
	bb[6] = makePosition(bb[1], length + endzone, 90 - fields[id].angle);
	bb[7] = makePosition(bb[0], length + endzone, 90 - fields[id].angle);
	return bb;
}

function drawField(id)
{
	var position = new google.maps.LatLng(fields[id].latitude, fields[id].longitude);
	fields[id].marker = createMarker(position, fields[id].num);

	var bb = positions(id);

	fields[id].field_outline = new google.maps.Polygon({
		'map':map,
		'path':[bb[0], bb[1], bb[2], bb[3], bb[0]],
		'strokeColor':'#ffffff',
		'strokeWeight':2,
		'strokeOpacity':1.0,
		'fillColor':'#ff6060',
		'fillOpacity':0.4
	});
	fields[id].endzone_outline = new google.maps.Polyline({
		'map':map,
		'path':[bb[4], bb[5], bb[7], bb[6]],
		'strokeColor':'#ffffff',
		'strokeWeight':2
	});
}

function createMarker(position, title)
{
	var icon = new google.maps.MarkerImage(
		zuluru_path + 'blank-marker.png',
		new google.maps.Size(20, 34),
		new google.maps.Point(0, 0),
		new google.maps.Point(9, 34)
	);
	var shadow = new google.maps.MarkerImage(
		'http://www.google.com/mapfiles/shadow50.png',
		new google.maps.Size(37, 34),
		new google.maps.Point(0, 0),
		new google.maps.Point(9, 34)
	);

	var marker = new google.maps.Marker({
		'map':map,
		'position':position,
		'title':title.toString(),
		'icon':icon,
		'shadow':shadow
	});
	return marker;
}

function showParking(position)
{
	var icon = new google.maps.MarkerImage(
		zuluru_path + 'parking_pin.png',
		new google.maps.Size(12, 20),
		new google.maps.Point(0, 0),
		new google.maps.Point(6, 20)
	);
	var shadow = new google.maps.MarkerImage(
		zuluru_path + 'mm_20_shadow.png',
		new google.maps.Size(22, 20),
		new google.maps.Point(0, 0),
		new google.maps.Point(6, 20)
	);

	return new google.maps.Marker({
		'map':map,
		'position':position,
		'icon':icon,
		'shadow':shadow,
	});
}
