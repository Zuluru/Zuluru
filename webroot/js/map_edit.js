var leaguelat = 0.0;
var leaguelng = 0.0;
var lastParkingId = 0;

function initializeEdit(id)
{
	initialize();

	var field = fields[id];

	if (field.latitude != undefined) {
		position = new google.maps.LatLng(field.latitude, field.longitude);
		map.setCenter(position);
		map.setZoom(field.zoom);
		map.setMapTypeId(google.maps.MapTypeId.HYBRID);

		drawFields();
		selectField(id);
	} else {
		var gc = new google.maps.Geocoder();
		gc.geocode({
			'address':full_address,
			'location':new google.maps.LatLng(leaguelat, leaguelng)
		}, function(result, status) { centerByAddress(id, result, status); });
	}

	for (lastParkingId in parking)
	{
		parking[lastParkingId].marker = showParking (parking[lastParkingId].position);
		parking[lastParkingId].marker.setOptions({'draggable':true});
		deleteOnClick(parking[lastParkingId].marker, lastParkingId);
	}
}

function deleteOnClick(marker, id)
{
	google.maps.event.addListener(marker, 'click', function() {
		deleteParking(id);
	});
}

function drawFields()
{
	for (var id in fields)
	{
		drawField(id);
		// This needs to go in a separate function for closures to work correctly
		selectOnClick(fields[id].marker, id);
	}
}

function selectOnClick(marker, id)
{
	google.maps.event.addListener(marker, 'click', function() {
		selectField(id);
	});
}

function saveField()
{
	if (current != 0) {
		fields[current].angle = parseInt ($('#show_angle').html());
		fields[current].width = parseInt ($('#show_width').html());
		fields[current].length = parseInt ($('#show_length').html());
	}
}

var drag_listener = null;

function selectField(id)
{
	if (current != 0)
	{
		// Remove selection colouring and listener from the old field
		fields[current].field_outline.setOptions({'fillColor':'#ff6060'});
		fields[current].marker.setOptions({'draggable':false});
		google.maps.event.removeListener(drag_listener);

		// Save any layout changes into the form
		saveField();
	}

	// Update the display with data about the selected field
	$('#show_num').html(fields[id].num + ' (' + id + ')');
	$('#show_angle').html(fields[id].angle);
	$('#show_width').html(fields[id].width);
	$('#show_length').html(fields[id].length);
	$('#show_field').html(fieldLength(fields[id].length));
	$('#show_endzone').html(endzoneLength(fields[id].length));

	// Add selection colouring and listener to the new field
	fields[id].field_outline.setOptions({'fillColor':'#60ff60'});
	fields[id].marker.setOptions({'draggable':true});
	drag_listener = google.maps.event.addListener(fields[id].marker, 'drag', redraw);

	current = id;
}

// Array for decoding the failure codes
var reasons=[];
reasons[google.maps.GeocoderStatus.ERROR] = "There was a problem contacting the Google servers.";
reasons[google.maps.GeocoderStatus.INVALID_REQUEST] = "This GeocoderRequest was invalid.";
reasons[google.maps.GeocoderStatus.OVER_QUERY_LIMIT] = "The webpage has gone over the requests limit in too short a period of time.";
reasons[google.maps.GeocoderStatus.REQUEST_DENIED] = "The webpage is not allowed to use the geocoder.";
reasons[google.maps.GeocoderStatus.UNKNOWN_ERROR] = "A geocoding request could not be processed due to a server error. The request may succeed if you try again.";
reasons[google.maps.GeocoderStatus.ZERO_RESULTS] = "No result was found for this GeocoderRequest.";

function centerByAddress(id, result, status)
{
	var position;
	if (status == google.maps.GeocoderStatus.OK) {
		position = result[0].geometry.location;
	} else {
		position = new google.maps.LatLng(leaguelat, leaguelng);
		alert('Field has no lat/long yet, and the street address was not found by Google.\nUsing default lat/long from "global settings" as a fallback.\nReason given by Google was "' + reasons[status] + '"');
	}

	fields[id].latitude = position.lat();
	fields[id].longitude = position.lng();
	fields[id].length = 120;
	fields[id].width = 40;
	fields[id].angle = 0;

	map.setCenter(position);
	map.setZoom(15);
	map.setMapTypeId(google.maps.MapTypeId.HYBRID);

	drawFields();
	selectField(id);
}

function redraw()
{
	var bb = positions(current);
	fields[current].field_outline.setPath([bb[0], bb[1], bb[2], bb[3], bb[0]]);
	fields[current].endzone_outline.setPath([bb[4], bb[5], bb[7], bb[6]]);
}

function updateAngle(val)
{
	fields[current].angle += val;
	if (fields[current].angle <= -90)
		fields[current].angle += 180;
	if (fields[current].angle > 90)
		fields[current].angle -= 180;
	redraw();

	$('#show_angle').html(fields[current].angle);

	// Avoid form submission
	return false;
}

function updateWidth(val)
{
	fields[current].width += val;
	if (fields[current].width < 25)
		fields[current].width = 25;
	if (fields[current].width > 40)
		fields[current].width = 40;
	redraw();

	$('#show_width').html(fields[current].width);

	// Avoid form submission
	return false;
}

function updateLength(val)
{
	fields[current].length += val;
	if (fields[current].length < 50)
		fields[current].length = 50;
	if (fields[current].length > 120)
		fields[current].length = 120;
	redraw();

	$('#show_length').html(fields[current].length);
	$('#show_field').html(fieldLength(fields[current].length));
	$('#show_endzone').html(endzoneLength(fields[current].length));

	// Avoid form submission
	return false;
}

var parking_listener = null;

function addParking()
{
	if (parking_listener != null)
	{
		google.maps.event.removeListener(parking_listener);
	}

	//$('#map').css('cursor','crosshair');
	parking_listener = google.maps.event.addListener(map, 'click', function(event) {
		addParkingClick(event);
	});

	// Avoid form submission
	return false;
}

function addParkingClick(event)
{
	google.maps.event.removeListener(parking_listener);
	parking_listener = null;
	//$('#map').css('cursor','auto');

	++ lastParkingId;
	var marker = showParking (event.latLng);
	marker.setOptions({'draggable':true});
	deleteOnClick(marker, lastParkingId);
	parking[lastParkingId] = { 'marker': marker };
}

function deleteParking(id)
{
	if (confirm('Are you sure you want to delete this parking label?'))
	{
		parking[id].marker.setMap(null);
		delete(parking[id]);
	}

	// Avoid form submission
	return false;
}

function check()
{
	if (!confirm('Is the zoom level set properly for this view?'))
	{
		return false;
	}

	// Save any layout changes into the form
	saveField();
	for (var id in fields)
	{
		$('#Field' + id + 'Zoom').val(map.getZoom());
		$('#Field' + id + 'Angle').val(fields[id].angle);
		$('#Field' + id + 'Width').val(fields[id].width);
		$('#Field' + id + 'Length').val(fields[id].length);
		$('#Field' + id + 'Latitude').val(fields[id].marker.getPosition().lat());
		$('#Field' + id + 'Longitude').val(fields[id].marker.getPosition().lng());
	}

	// Combine the parking details
	var parkingString = '';
	for (var p in parking)
	{
		if (parking[p] != undefined)
		{
			var position = parking[p].marker.getPosition();
			parkingString += position.lat() + ',' + position.lng() + '/';
		}
	}
	parkingString = parkingString.substring(0, parkingString.length - 1);
	$('#FacilityParking').val(parkingString);
}
