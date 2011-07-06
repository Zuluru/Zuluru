// Variables that must be set by the calling page
var name = '';
var address = '';
var full_address = '';
var id = 0;
var latitude = 0.0;
var longitude = 0.0;

// Variables with defaults that may be overridden by the calling page
var zuluru_path = '/zuluru';
var zoom = 18;
var angle = 0;
var width = 40;
var length = 120;

// Storage for other fields at the same site 
var other_id = Array();
var other_latitude = Array();
var other_longitude = Array();
var other_zoom = Array();
var other_angle = Array();
var other_width = Array();
var other_length = Array();

// Parking locations
var parking = Array();

// Various overlays
var marker;
var field_outline;
var endzone_outline;
var other_field_outline = Array();
var other_endzone_outline = Array();

// Object for handling directions
var gdir;

// Array for decoding the failure codes
var reasons=[];
reasons[G_GEO_SUCCESS]            = "Success";
reasons[G_GEO_MISSING_ADDRESS]    = "Missing Address: The address was either missing or had no value.";
reasons[G_GEO_UNKNOWN_ADDRESS]    = "Unknown Address: No corresponding geographic location could be found for the specified address. Make sure you include the city name.";
reasons[G_GEO_UNAVAILABLE_ADDRESS]= "Unavailable Address: The geocode for the given address cannot be returned due to legal or contractual reasons.";
reasons[G_GEO_BAD_KEY]            = "Bad Key: The API key is either invalid or does not match the domain for which it was given";
reasons[G_GEO_TOO_MANY_QUERIES]   = "Too Many Queries: The daily geocoding quota for this site has been exceeded.";
reasons[G_GEO_SERVER_ERROR]       = "Server error: The geocoding request could not be successfully processed.";
reasons[G_GEO_BAD_REQUEST]        = "A directions request could not be successfully parsed.";
reasons[G_GEO_MISSING_QUERY]      = "No query was specified in the input.";
reasons[G_GEO_UNKNOWN_DIRECTIONS] = "The GDirections object could not compute directions between the points.";

function initialize_view()
{
	initialize();

	var point = new GLatLng(latitude, longitude);
	map.setCenter(point, zoom, G_HYBRID_MAP);
	createMarker(point, false);
	show();
	show_others();

	// The div with the width is here to keep the tabs from being wider than the
	// bubble itself, which looks really weird.
	var address_tab_text = '<div style="width: 17em;"><b>' + name + '</b>' +
							'<br>' + address + '</div>';
	var layout_tab_text = '<b>Suggested field layout:</b>' +
							'<br>Field width: ' + width + ' yards' +
							'<br>Field length: ' + field_length(length) + ' yards' +
							'<br>End zone length: ' + endzone_length(length) + ' yards';
	var location_tab_text = 'Latitude: ' + Math.round(latitude * 100000) / 100000  +
							'<br>Longitude: ' + Math.round(longitude * 100000) / 100000;

	var infoTabs = [
	  new GInfoWindowTab("Address", address_tab_text),
	  new GInfoWindowTab("Layout", layout_tab_text),
	  new GInfoWindowTab("Location", location_tab_text)
	];
	GEvent.addListener(marker, "click", function()
	{
		marker.openInfoWindowTabsHtml(infoTabs);
	});
	marker.openInfoWindowTabsHtml(infoTabs);

	gdir = new GDirections(map, document.getElementById("directions"));

	// catch Directions errors
	GEvent.addListener(gdir, "error", function() {
		var code = gdir.getStatus().code;
		var reason = "Code " + code;
		if (reasons[code]) {
			reason = reasons[code];
		} 
		alert("Failed to obtain directions.\n\n" + reason);
	});
}

function createMarker(point, draggable)
{
	var ic = new GIcon(baseIcon);
	ic.image = zuluru_path + "blank-marker.png";
	marker = new GMarker(point, {'draggable':draggable, 'title':'field', 'icon':ic});
	map.addOverlay(marker);
	if (draggable) {
		GEvent.addListener(marker, 'drag', function() { dragged(); });
	}
}

function getDirections()
{
	// Set up the walk and avoid highways options
	var opts = {};
	opts.preserveViewport = true;
	if (document.getElementById("walk").checked) {
		opts.travelMode = G_TRAVEL_MODE_WALKING;
	}
	if (document.getElementById("highways").checked) {
		opts.avoidHighways = true;
	}

	// Set the start locations
	var saddr = document.getElementById("saddr").value;

	gdir.load("from: "+saddr+" to: "+full_address, opts);
}

function field_length(length)
{
	return length - endzone_length(length) * 2;
}

function endzone_length(length)
{
	return Math.floor(length * 5 / 12 / 2);
}

function show()
{
	var point = new GLatLng(latitude, longitude);

	var bb = new Array;
	for (i = 0; i < 8; i++) {
		bb[i] = new GPoint;
	}

	var field = field_length(length);
	var endzone = endzone_length(length);

	make_point(point, bb[1], width / 2, 180 - angle);
	make_point(bb[1], bb[0], length / 2, 270 - angle);
	make_point(bb[0], bb[1], width, 0 - angle);
	make_point(bb[1], bb[2], length, 90 - angle);
	make_point(bb[0], bb[3], length, 90 - angle);
	make_point(bb[1], bb[4], endzone, 90 - angle);
	make_point(bb[0], bb[5], endzone, 90 - angle);
	make_point(bb[1], bb[6], field + endzone, 90 - angle);
	make_point(bb[0], bb[7], field + endzone, 90 - angle);

	field_outline = new GPolygon([bb[0], bb[1], bb[2], bb[3], bb[0]], "#ffffff", 2, 1.0, "#60ff60", 0.4);
	endzone_outline = new GPolyline([bb[4], bb[5], bb[7], bb[6]], "#ffffff", 2);
	map.addOverlay(field_outline);
	map.addOverlay(endzone_outline);

	for (var p in parking) {
		add_parking (parking[p]);
	}
}

function show_others()
{
	for (var f in other_id)
	{
		var point = new GLatLng(other_latitude[f], other_longitude[f]);

		var bb = new Array;
		for (i = 0; i < 8; i++) {
			bb[i] = new GPoint;
		}

		var field = field_length(other_length[f]);
		var endzone = endzone_length(other_length[f]);

		make_point(point, bb[1], other_width[f] / 2, 180 - other_angle[f]);
		make_point(bb[1], bb[0], other_length[f] / 2, 270 - other_angle[f]);
		make_point(bb[0], bb[1], other_width[f], 0 - other_angle[f]);
		make_point(bb[1], bb[2], other_length[f], 90 - other_angle[f]);
		make_point(bb[0], bb[3], other_length[f], 90 - other_angle[f]);
		make_point(bb[1], bb[4], endzone, 90 - other_angle[f]);
		make_point(bb[0], bb[5], endzone, 90 - other_angle[f]);
		make_point(bb[1], bb[6], field + endzone, 90 - other_angle[f]);
		make_point(bb[0], bb[7], field + endzone, 90 - other_angle[f]);

		other_field_outline[f] = new GPolygon([bb[0], bb[1], bb[2], bb[3], bb[0]], "#ffffff", 2, 1.0, "#ff6060", 0.4);
		other_endzone_outline[f] = new GPolyline([bb[4], bb[5], bb[7], bb[6]], "#ffffff", 2);
		map.addOverlay(other_field_outline[f]);
		map.addOverlay(other_endzone_outline[f]);
	}
}

// TODO: Are there special values in here that only work for specific lat/lng?
//       xv and yv constants look "magical".
function make_point(start, end, distance, angle)
{
	distance *= 0.9144;	// convert from yards to metres
	var rad = Math.PI/180;
	angle *= rad;
	var dx = distance * Math.cos(angle);
	var dy = distance * Math.sin(angle);
	var yv = .000009014;
	var xv = rad * 6367449 * Math.cos(start.y * rad);
	end.x = start.x + dx / xv;
	end.y = start.y + dy * yv;
}

function add_parking(point)
{
	var icon = new GIcon();
	icon.image = zuluru_path + "parking_pin.png";
	icon.shadow = zuluru_path + "mm_20_shadow.png";
	icon.iconSize = new GSize(12, 20);
	icon.shadowSize = new GSize(22, 20);
	icon.iconAnchor = new GPoint(6, 20);
	icon.infoWindowAnchor = new GPoint(5, 1);

	map.addOverlay(new GMarker(point, icon));
}
