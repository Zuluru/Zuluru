<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('Approve Account', true));
$this->Html->addCrumb ($person['Person']['full_name']);
?>

<div class="people approve">
<h2><?php echo __('Approve Account') . ': ' . $person['Person']['full_name'];?></h2>

<?php
$dispositions = array(
		'approved' => 'Approved',
		'delete' => 'Deleted silently',
);

$this_is_player = (!empty($cached['Group']) && Set::extract('/GroupsPerson[group_id=' . GROUP_PLAYER . ']', $cached['Group']));
$this_is_player = (!empty($this_is_player));
$this_is_coach = (!empty($cached['Group']) && Set::extract('/GroupsPerson[group_id=' . GROUP_COACH . ']', $cached['Group']));
$this_is_coach = (!empty($this_is_coach));

$use_shirt_size = Configure::read('profile.shirt_size');
if ($use_shirt_size == PROFILE_REGISTRATION) {
	$use_shirt_size = ($this_is_player || $this_is_coach);
}

$rows = array(
	'full_name' => array('name' => 'Name'),
);

$rows['name'] = array('name' => 'Group', 'model' => 'Group', 'func' => 'groups');

if (!empty($person['Person']['user_name'])) {
	$rows['user_name'] = array('name' => 'User Name');
}

if (!empty($person['Person']['user_id']) && !Configure::read('feature.manage_accounts')) {
	$rows['user_id'] = array('name' => sprintf(__('%s User Id', true), Configure::read('feature.manage_name')));
}

$rows['id'] = array('name' => 'Zuluru User ID');
$rows[] = 'email';

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

if ($use_shirt_size) {
	$rows[] = 'shirt_size';
}

$sports = array_keys(Configure::read('options.sport'));
foreach ($sports as $sport) {
	$skill = reset(Set::extract("/Skill[sport=$sport]/.", $person));
	if (!empty($skill['enabled'])) {
		$person['Person']["skill_level_$sport"] = $skill['skill_level'];
		$person['Person']["year_started_$sport"] = $skill['year_started'];
	} else {
		$person['Person']["skill_level_$sport"] = $person['Person']["year_started_$sport"] = null;
	}

	if (Configure::read('profile.skill_level')) {
		if (count($sports) > 1) {
			$rows["skill_level_$sport"] = array('name' => "Skill Level ($sport)");
		} else {
			$rows["skill_level_$sport"] = array('name' => 'Skill Level');
		}
	}

	if (Configure::read('profile.year_started')) {
		if (count($sports) > 1) {
			$rows["year_started_$sport"] = array('name' => "Year Started ($sport)");
		} else {
			$rows["year_started_$sport"] = array('name' => 'Year Started');
		}
	}
}

$rows['status'] = array('name' => 'Account Status');

$cols = array('name' => array(), 'person' => array());
$i = 0;
$has_data = array();
foreach ($rows as $key => $data) {
	$name = null;
	if (is_numeric ($key)) {
		$field = $data;
		if (array_key_exists($field, $person['Person'])) {
			$val = $person['Person'][$field];
		} else {
			$val = null;
		}
	} else {
		$field = $key;
		if (array_key_exists ('name', $data)) {
			$name = $data['name'];
		}
		if (array_key_exists ('model', $data)) {
			$model = $data['model'];
		} else {
			$model = 'Person';
		}
		if (array_key_exists ('func', $data) && !array_key_exists($field, $person[$model])) {
			$val = $person[$model];
		} else {
			$val = $person[$model][$field];
		}
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
	if (!empty($val)) {
		$has_data[$i] = true;
	}
	++ $i;
}

if (!empty ($duplicates)) {
	echo $this->Html->para('warning-message', __('The following users may be duplicates of this account (click to compare):', true));

	$compare = array();
	foreach ($duplicates as $duplicate) {
		foreach ($sports as $sport) {
			$skill = reset(Set::extract("/Skill[sport=$sport]/.", $duplicate));
			if (!empty($skill['enabled'])) {
				$duplicate['Person']["skill_level_$sport"] = $skill['skill_level'];
				$duplicate['Person']["year_started_$sport"] = $skill['year_started'];
			} else {
				$duplicate['Person']["skill_level_$sport"] = $duplicate['Person']["year_started_$sport"] = null;
			}
		}

		$dispositions["delete_duplicate:{$duplicate['Person']['id']}"] = sprintf(__('Deleted as duplicate of %s (%d)', true), $duplicate['Person']['full_name'], $duplicate['Person']['id']);
		$dispositions["merge_duplicate:{$duplicate['Person']['id']}"] = sprintf(__('Merged backwards into %s (%d)', true), $duplicate['Person']['full_name'], $duplicate['Person']['id']);
		$compare[] = $this->Html->link ("{$duplicate['Person']['full_name']} ({$duplicate['Person']['id']})", '#',
			array('onclick' => "return compare({$duplicate['Person']['id']})"));

		$i = 0;
		foreach ($rows as $key => $data) {
			if (is_numeric ($key)) {
				if (array_key_exists($data, $person['Person'])) {
					$user_val = $person['Person'][$data];
				} else {
					$user_val = null;
				}
				if (array_key_exists($data, $duplicate['Person'])) {
					$val = $duplicate['Person'][$data];
				} else {
					$val = null;
				}
			} else {
				if (array_key_exists ('model', $data)) {
					$model = $data['model'];
				} else {
					$model = 'Person';
				}
				if (array_key_exists ('func', $data) && !array_key_exists($key, $person[$model])) {
					$user_val = $person[$model];
					$val = $duplicate[$model];
				} else {
					if (!empty($person[$model][$key])) {
						$user_val = $person[$model][$key];
					} else {
						$user_val = null;
					}
					if (!empty($duplicate[$model][$key])) {
						$val = $duplicate[$model][$key];
					} else {
						$val = null;
					}
				}
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
			if (!empty($val)) {
				$has_data[$i] = true;
			}
			++ $i;
		}
	}
	echo $this->Html->nestedList ($compare);
}

echo '<br>';

$table_data = array_transpose($cols);
foreach (array_keys($table_data) as $key) {
	if (!array_key_exists($key, $has_data)) {
		unset($table_data[$key]);
	}
}

echo $this->Html->tag ('table', $this->Html->tableCells ($table_data, array(), array('class' => 'altrow')), array('class' => 'list'));

if (!empty($duplicates) && !$activated) {
	echo $this->Html->para('warning-message', __('This user has not yet activated their account. If the user record is merged backwards, they WILL NOT be able to activate their account.', true));
}

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
	if (Configure::read('feature.birth_year_only')) {
		if (empty($data) || substr($data, 0, 4) == '0000') {
			return __('unknown', true);
		} else {
			return substr($data, 0, 4);
		}
	} else {
		return $ths->ZuluruTime->date($data);
	}
}
function format_height($data) {
	if (!empty($data)) {
		return $data . ' ' . (Configure::read('feature.units') == 'Metric' ? __('cm', true) : __('inches', true));
	}
}
function format_groups($data) {
	$groups = Set::extract('/name', $data);
	if (empty($groups)) {
		return __('None', true);
	} else {
		return implode(', ', $groups);
	}
}

?>
