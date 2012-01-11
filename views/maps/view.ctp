<?php
$this->Html->addCrumb (__('Field Layout', true));
$this->Html->addCrumb ("{$field['Facility']['name']} ({$field['Facility']['code']}) {$field['Field']['num']}");
?>

<?php
$map_vars = array('id', 'latitude', 'longitude', 'angle', 'width', 'length', 'zoom');

$zuluru_base = Configure::read('urls.zuluru_base');
$gmaps_key = Configure::read('site.gmaps_key');
$address = "{$field['Facility']['location_street']}, {$field['Facility']['location_city']}";
$full_address = "{$field['Facility']['location_street']}, {$field['Facility']['location_city']}, {$field['Facility']['location_province']}";

// Build the list of variables to set for the JS.
// The blank line before END_OF_VARIABLES is required.
$variables = <<<END_OF_VARIABLES
zuluru_path = "$zuluru_base/";
name = "{$field['Field']['long_name']}";
address = "$address";
full_address = "$full_address";

END_OF_VARIABLES;

foreach ($map_vars as $var) {
	$variables .= "$var = {$field['Field'][$var]};\n";
}

// Handle parking
if ($field['Facility']['parking']) {
	$parking = explode ('/', $field['Facility']['parking']);
	foreach ($parking as $i => $pt) {
		list($lat,$lng) = explode(',', $pt);
		$variables .= "parking[$i] = new GLatLng($lat, $lng);\n";
	}
}

// Handle other fields at this site
foreach ($field['Facility']['Field'] as $related) {
	foreach ($map_vars as $var) {
		$variables .= "other_{$var}[{$related['id']}] = {$related[$var]};\n";
	}
}

echo $this->ZuluruHtml->script (array(
		"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$gmaps_key",
		"map_common.js",
		"map_view.js",
), false);
$this->Html->scriptBlock ($variables, array('inline' => false));
?>

<h3><?php echo $field['Field']['long_name']; ?></h3>
<p><?php echo $address; ?></p>

<p>Get directions to this field from:
<form action="javascript:getDirections()">
<input type="text" size=30 maxlength=50 name="saddr" id="saddr" value="<?php echo $home_addr; ?>" /><br>
<input value="Get Directions" type="submit"><br>
Walking <input type="checkbox" name="walk" id="walk" /><br>
Biking <input type="checkbox" name="highways" id="highways" />
<div id="directions">
</div>

<?php
$this->Js->buffer('initialize_view();');
?>
