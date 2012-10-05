<?php
$this->Html->addCrumb (sprintf(__('All %s', true), Configure::read('ui.fields_cap')));
?>

<?php
$map_vars = array('id', 'name', 'code', 'location_street');

$zuluru_base = Configure::read('urls.zuluru_base');
$gmaps_key = Configure::read('site.gmaps_key');

// Build the list of variables to set for the JS.
// The blank line before END_OF_VARIABLES is required.
$variables = <<<END_OF_VARIABLES
zuluru_path = "$zuluru_base/";

END_OF_VARIABLES;

$affiliate_id = null;
foreach ($regions as $region) {
	if (empty($region['Facility'])) {
		continue;
	}
	if (count($affiliates) > 1 && $region['Region']['affiliate_id'] != $affiliate_id) {
		$affiliate_id = $region['Region']['affiliate_id'];
		echo $this->Html->tag('h3', $region['Affiliate']['name'], array('class' => 'affiliate'));
	}

	echo $this->Html->tag('h4', $region['Region']['name']);

	foreach ($region['Facility'] as $facility) {
		if (empty($facility['Field'])) {
			continue;
		}
		$vals = array();
		foreach ($map_vars as $var) {
			$val = $facility[$var];
			if (!is_numeric($val)) {
				$val = "\"$val\"";
			}
			$vals[] = "'$var': $val";
		}

		$lats = Set::extract('/Field/latitude', $facility);
		$lngs = Set::extract('/Field/longitude', $facility);
		$vals[] = "'latitude': " . array_sum($lats) / count($lats);
		$vals[] = "'longitude': " . array_sum($lngs) / count($lngs);

		$surfaces = array_unique(Set::extract('/Field/surface', $facility));
		$surfaces = array_map(array('Inflector', 'humanize'), $surfaces);
		$vals[] = "'surface': \"" . implode('/', $surfaces) . '"';

		$variables .= "fields[{$facility['id']}] = { " . implode(', ', $vals) . " };\n";

		echo $this->Html->para(null, $this->Html->link($facility['name'], '#', array(
				'onClick' => "openField({$facility['id']}); return false;",
		)));
	}
}

if ($is_admin) {
	echo $this->Html->tag('br');
	if ($closed) {
		echo $this->Html->link(sprintf(__('Show only open %s', true), Configure::read('ui.fields')), array('action' => 'index'));
	} else {
		echo $this->Html->link(sprintf(__('Show all %s', true), Configure::read('ui.fields')), array('closed' => 1));
	}
}

// TODO: Handle more than one sport in a site
$sport = array_shift(array_keys(Configure::read('options.sport')));
$this->ZuluruHtml->script (array(
		"http://maps.googleapis.com/maps/api/js?key=$gmaps_key&libraries=geometry&sensor=false",
		'map_common.js',
		'map_overview.js',
		"sport_$sport.js",
), false);
$this->Html->scriptBlock ($variables, array('inline' => false));
?>

<?php
$this->Js->buffer("initializeOverview();");
?>
