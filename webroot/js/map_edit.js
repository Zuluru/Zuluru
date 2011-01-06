var leaguelat = 0.0;
var leaguelng = 0.0;

function initialize_edit()
{
	initialize();

	if ((latitude) != 0.0) {
		point = new GLatLng(latitude, longitude);
		map.setCenter(point, zoom, G_HYBRID_MAP);
		createMarker(point, true);
		redraw();
	} else {
		var gc = new GClientGeocoder();
		gc.getLatLng(full_address, center_by_address);
	}

	show_others();
}

function center_by_address(point)
{
	if (!point) {
		point = new GLatLng(leaguelat, leaguelng);
		alert('Field has no lat/long yet, and the street address was not found by Google.\nUsing default lat/long from "global settings" as a fallback.');
	}
	map.setCenter(point, zoom - 2, G_HYBRID_MAP);
	createMarker(point, true);

	// Initialize everything that a drag does
	dragged();
}

function dragged()
{
	var point = marker.getPoint();
	latitude = point.y;
	longitude = point.x;

	document.layout.latitude.value = latitude;
	document.layout.longitude.value = longitude;

	redraw();
}

function redraw()
{
	if (field_outline) {
		map.removeOverlay(field_outline);
		map.removeOverlay(endzone_outline);
	}

	show();

	document.getElementById("show_angle").innerHTML = angle;
	document.getElementById("show_width").innerHTML = width;
	document.getElementById("show_length").innerHTML = length;
	document.getElementById("show_field").innerHTML = field_length(length);
	document.getElementById("show_endzone").innerHTML = endzone_length(length);

	document.layout.angle.value = angle;
	document.layout.width.value = width;
	document.layout.length.value = length;
}

function update_angle(val)
{
	angle += val;
	if (angle <= -90)
		angle += 180;
	if (angle > 90)
		angle -= 180;
	redraw();

	// Avoid form submission
	return false;
}

function update_width(val)
{
	width += val;
	if (width < 25)
		width = 25;
	if (width > 40)
		width = 40;
	redraw();

	// Avoid form submission
	return false;
}

function update_length(val)
{
	length += val;
	if (length < 50)
		length = 50;
	if (length > 120)
		length = 120;
	redraw();

	// Avoid form submission
	return false;
}

function check()
{
	point = new GLatLng(latitude, longitude);
	zoom = map.getZoom();
	map.setCenter(point, zoom, G_HYBRID_MAP);
	document.layout.zoom.value = zoom;

	return confirm('Is the zoom level set properly for this view?');
}
