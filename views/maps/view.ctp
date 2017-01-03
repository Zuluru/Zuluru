<?php
$this->Html->addCrumb (sprintf(__('%s Layout', true), Configure::read('ui.field_cap')));
$this->Html->addCrumb ("{$field['Facility']['name']} ({$field['Facility']['code']}) {$field['Field']['num']}");
?>

<?php
$map_vars = array('id', 'num', 'sport', 'latitude', 'longitude', 'angle', 'width', 'length', 'zoom', 'surface');

$zuluru_base = Configure::read('urls.zuluru_img');
$gmaps_key = Configure::read('site.gmaps_key');
$address = "{$field['Facility']['location_street']}, {$field['Facility']['location_city']}";
$full_address = "{$field['Facility']['location_street']}, {$field['Facility']['location_city']}, {$field['Facility']['location_province']}";

// Build the list of variables to set for the JS.
// The blank line before END_OF_VARIABLES is required.
$variables = <<<END_OF_VARIABLES
zuluru_path = "$zuluru_base";
name = "{$field['Facility']['name']}";
address = "$address";
full_address = "$full_address";

END_OF_VARIABLES;

$vals = array();
foreach ($map_vars as $var) {
	$val = $field['Field'][$var];
	if (!is_numeric($val)) {
		$val = "\"$val\"";
	}
	$vals[] = "'$var': $val";
}
$variables .= "fields[{$field['Field']['id']}] = { " . implode(', ', $vals) . " };\n";

// Handle other fields at this facility
foreach ($field['Facility']['Field'] as $related) {
	$vals = array();
	foreach ($map_vars as $var) {
		$val = $related[$var];
		if (!is_numeric($val)) {
			$val = "\"$val\"";
		}
		$vals[] = "'$var': $val";
	}
	$variables .= "fields[{$related['id']}] = { " . implode(', ', $vals) . " };\n";
}

// Handle parking
if ($field['Facility']['parking']) {
	$parking = explode ('/', $field['Facility']['parking']);
	foreach ($parking as $i => $pt) {
		list($lat,$lng) = explode(',', $pt);
		$variables .= "parking[$i] = { 'position': new google.maps.LatLng($lat, $lng) };\n";
	}
}

// Handle entrances
if ($field['Facility']['entrances']) {
	$entrances = explode ('/', $field['Facility']['entrances']);
	foreach ($entrances as $i => $pt) {
		list($lat,$lng) = explode(',', $pt);
		$variables .= "entrances[$i] = { 'position': new google.maps.LatLng($lat, $lng) };\n";
	}
}

$this->ZuluruHtml->script (array(
		"https://maps.googleapis.com/maps/api/js?key=$gmaps_key&libraries=geometry&sensor=true",
		'map_common.js',
		'map_view.js',
), false);
$sports = Set::extract('/Facility/Field/sport', $field);
$sports[] = $field['Field']['sport'];
$sports = array_unique($sports);
foreach ($sports as $sport) {
	$this->ZuluruHtml->script ("sport_$sport.js", false);
}
$this->Html->scriptBlock ($variables, array('inline' => false));
?>

<h3><?php echo $field['Field']['long_name']; ?></h3>
<p><?php echo $address; ?></p>

<p><?php printf(__('Get directions to this %s from:', true), Configure::read('ui.field')); ?></p>
<form action="javascript:getDirections()">
<input type="text" size=30 maxlength=50 name="saddr" id="saddr" value="<?php echo $home_addr; ?>" /><br>
<input value="<?php __('Get Directions'); ?>" type="submit"><br>
<?php __('Walking'); ?> <input type="checkbox" name="walk" id="walk" /><br>
<?php __('Avoid highways'); ?> <input type="checkbox" name="highways" id="highways" />
<div id="directions">
</div>

<?php
$this->Js->buffer("initializeView({$field['Field']['id']});");
?>
