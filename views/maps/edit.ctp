<?php
$this->Html->addCrumb (__('Field Editor', true));
$this->Html->addCrumb ("{$field['Field']['name']} ({$field['Field']['code']}) {$field['Field']['num']}");
?>

<?php
$map_vars = array('id', 'latitude', 'longitude', 'angle', 'width', 'length', 'zoom');

$zuluru_base = Configure::read('urls.zuluru_base');
$gmaps_key = Configure::read('site.gmaps_key');
$address = "{$field['Field']['location_street']}, {$field['Field']['location_city']}";
$full_address = "{$field['Field']['location_street']}, {$field['Field']['location_city']}, {$field['Field']['location_province']}";

// We use these as last-ditch emergency values, if the field has neither
// a valid lat/long or an address that Google can find.
$leaguelat = Configure::read('organization.latitude');
$leaguelng = Configure::read('organization.longitude');

// Build the list of variables to set for the JS.
// The blank line before END_OF_VARIABLES is required.
$variables = <<<END_OF_VARIABLES
zuluru_path = "$zuluru_base/";
leaguelat = $leaguelat;
leaguelng = $leaguelng;
drag = true;
name = "{$field['Field']['long_name']}";
address = "$address";
full_address = "$full_address";

END_OF_VARIABLES;

echo $this->Form->create(false, array('url' => Router::normalize($this->here), 'name' => 'layout'));

foreach ($map_vars as $var) {
	if (!empty ($field['Field'][$var])) {
		$variables .= "$var = {$field['Field'][$var]};\n";
	}
	echo $this->Form->hidden($var, array('value' => $field['Field'][$var]));
}

// Handle parking
if ($field['Field']['parking']) {
	$parking = explode ('/', $field['Field']['parking']);
	foreach ($parking as $i => $pt) {
		list($lat,$lng) = explode(',', $pt);
		$variables .= "parking[$i] = new GLatLng($lat, $lng);\n";
	}
}

// Handle other fields at this site
foreach ($field['SiteFields'] as $related) {
	foreach ($map_vars as $var) {
		if (!empty ($related['Field'][$var])) {
			$variables .= "other_{$var}[{$related['Field']['id']}] = {$related['Field'][$var]};\n";
		}
	}
}

echo $this->ZuluruHtml->script (array(
		"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$gmaps_key",
		"map_common.js",
		"map_view.js",
		"map_edit.js",
), false);
$this->Html->scriptBlock ($variables, array('inline' => false));
?>

<h3><?php echo $field['Field']['long_name']; ?></h3>
<p><?php echo $address; ?></p>

<p>Angle:
<span id="show_angle"></span>
<input type="submit" onclick="return update_angle(10)" value="+10">
<input type="submit" onclick="return update_angle(1)" value="+">
<input type="submit" onclick="return update_angle(-1)" value="-">
<input type="submit" onclick="return update_angle(-10)" value="-10">
</p>

<p>Width:
<span id="show_width"></span>
<input type="submit" onclick="return update_width(1)" value="+">
<input type="submit" onclick="return update_width(-1)" value="-">
</p>

<p>Length:
<span id="show_length"></span>
<input type="submit" onclick="return update_length(2)" value="+">
<input type="submit" onclick="return update_length(-2)" value="-">
</p>

<p>Playing Field Proper:
<span id="show_field"></span>
</p>

<p>End zone:
<span id="show_endzone">25</span>
</p>

<?php
echo $this->Form->end(array(
		'label' => __('Save Changes', true),
		'onclick' => 'return check();',
));
?>

<?php
$this->Js->buffer('initialize_edit();');
?>
