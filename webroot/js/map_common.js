//
// Common functions and variables shared among various map pages
//

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

function drawField(id)
{
	var position = new google.maps.LatLng(fields[id].latitude, fields[id].longitude);
	fields[id].marker = createMarker(position, fields[id].num);

	fields[id].field_outline = new google.maps.Polygon({
		'map':map,
		'path':outlinePositions(id),
		'strokeColor':'#ffffff',
		'strokeWeight':2,
		'strokeOpacity':1.0,
		'fillColor':'#ff6060',
		'fillOpacity':0.4
	});
	fields[id].field_inline = new google.maps.Polyline({
		'map':map,
		'path':inlinePositions(id),
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
		'shadow':shadow
	});
}
