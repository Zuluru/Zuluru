<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('Approve Account', true));
$this->Html->addCrumb ($person['Person']['full_name']);
?>

<div class="people approve">
<h2><?php echo __('Approve Account') . ': ' . $person['Person']['full_name'];?></h2>

<?php
$dispositions = array(
		'approved_player' => 'Approved as player',
		'approved_visitor' => 'Approved as visitor',
		'delete' => 'Deleted silently',
);

$rows = array(
	'full_name' => array('name' => 'Name'),
	'user_name' => array('name' => 'System Username'),
	'id' => array('name' => 'Website User ID'),
	'email',
);

if (Configure::read('profile.home_phone')) {
	$rows[] = 'home_phone';
}

if (Configure::read('profile.work_phone')) {
	$rows[] = 'work_phone';
	$rows[] = 'work_ext';
}

if (Configure::read('profile.mobile_phone')) {
	$rows[] = 'mobile_phone';
}

if (Configure::read('profile.addr_street')) {
	$rows['addr_street'] = array('name' => 'Address');
}

if (Configure::read('profile.addr_city')) {
	$rows['addr_city'] = array('name' => 'City');
}

if (Configure::read('profile.addr_prov')) {
	$rows['addr_prov'] = array('name' => 'Province');
}

if (Configure::read('profile.addr_postalcode')) {
	$rows['addr_postalcode'] = array('name' => 'Postal Code');
}

if (Configure::read('profile.birthdate')) {
	$rows['birthdate'] = array('func' => 'date');
}

if (Configure::read('profile.height')) {
	$rows['height'] = array('func' => 'height');
}

$rows[] = 'gender';

if (Configure::read('profile.shirt_size')) {
	$rows[] = 'shirt_size';
}

if (Configure::read('profile.skill_level')) {
	$rows[] = 'skill_level';
}

$rows[] = 'year_started';

if (Configure::read('profile.birthdate')) {
	$rows['status'] = array('name' => 'Account Status');
}

$cols = array('name' => array(), 'person' => array());
foreach ($rows as $key => $data) {
	$name = null;
	if (is_numeric ($key)) {
		$field = $data;
		$val = $person['Person'][$field];
	} else {
		$field = $key;
		if (array_key_exists ('name', $data)) {
			$name = $data['name'];
		}
		$val = $person['Person'][$field];
		if (array_key_exists ('func', $data)) {
			$func = "format_{$data['func']}";
			$val = $func($val, $this);
		}
	}
	if ($name == null) {
		$name = Inflector::humanize ($field);
	}
	$cols['name'][] = $name;
	$cols['person'][] = $val;
}

if (!empty ($duplicates)) {
	echo $this->Html->para('warning-message', __('The following users may be duplicates of this account (click to compare):', true));

	$compare = array();
	foreach ($duplicates as $duplicate) {
		$dispositions["delete_duplicate:{$duplicate['Person']['id']}"] = "Deleted as duplicate of {$duplicate['Person']['full_name']} ({$duplicate['Person']['id']})";
		$dispositions["merge_duplicate:{$duplicate['Person']['id']}"] = "Merged backwards into {$duplicate['Person']['full_name']} ({$duplicate['Person']['id']})";
		$compare[] = $this->Html->link ("{$duplicate['Person']['full_name']} ({$duplicate['Person']['id']})", '#',
			array('onclick' => "return compare({$duplicate['Person']['id']})"));

		foreach ($rows as $key => $data) {
			if (is_numeric ($key)) {
				$user_val = $person['Person'][$data];
				$val = $duplicate['Person'][$data];
			} else {
				$user_val = $person['Person'][$key];
				$val = $duplicate['Person'][$key];
				if (array_key_exists ('func', $data)) {
					$func = "format_{$data['func']}";
					$user_val = $func($user_val, $this);
					$val = $func($val, $this);
				}
			}
			$class = "player_id_{$duplicate['Person']['id']}";
			if (low($val) == low($user_val)) {
				$class .= ' warning-message';
			}
			$cols[$duplicate['Person']['id']][] = array($val, array('class' => $class));
		}
	}
	echo $this->Html->nestedList ($compare);

	// TODO: Make this generic, via function in the Auth model
	if (array_key_exists ('UserZikula', $auth) && !$auth['UserZikula']['pn_activated']) {
		echo $this->Html->para('warning-message', 'This user has not yet activated their account. If the user record is merged backwards, they WILL NOT be able to activate their account.');
	}
}

echo '<br>';

echo $this->Html->tag ('table', $this->Html->tableCells (array_transpose ($cols), array(), array('class' => 'altrow')), array('class' => 'list'));

echo $this->Form->create();
echo $this->Form->input ('id', array(
		'value' => $person['Person']['id'],
));
echo $this->Form->input ('disposition', array(
		'label' => __('This user should be:', true),
		'options' => $dispositions,
		'empty' => '---',
));
echo $this->Form->end(__('Submit', true));
?>
</div>

<?php
echo $this->Html->scriptBlock ('
function compare(id) {
	jQuery("td[class^=player_id_]").css("display", "none");
	jQuery("td[class^=player_id_" + id + "]").css("display", "");
	return false;
}
jQuery(document).ready(function() {
	compare(0);
});
');

// Helper functions for formatting data
function format_date($data, $ths) {
	return $ths->ZuluruTime->date ($data);
}
function format_height($data, $ths) {
	return "$data inches";
}

?>
