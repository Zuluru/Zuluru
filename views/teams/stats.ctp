<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Stats', true));
?>

<div class="teams stats">
<h2><?php echo $team['Team']['name'];?></h2>
</div>
<div class="actions">
	<?php echo $this->element('teams/actions', array('team' => $team['Team'], 'division' => $team['Division'], 'league' => $team['Division']['League'], 'format' => 'list')); ?>
</div>

<div class="related">
<?php
$na = __('N/A', true);

$has_numbers = false;
$numbers = array_unique(Set::extract('/Person/TeamsPerson/number', $team));
if (Configure::read('feature.shirt_numbers') && count($numbers) > 1 && $numbers[0] !== null) {
	$has_numbers = true;
}

$headers = array(
	$this->Html->tag('th', __('Name', true)),
	$this->Html->tag('th', __('Gender', true)),
);
$totals = array(__('Total', true), '');
if ($has_numbers) {
	array_unshift($headers, $this->Html->tag('th', '#'));
	array_unshift($totals, '');
}

// Sort the stats into groups for display
$tables = array();
foreach ($team['Division']['League']['StatType'] as $stat_type) {
	if (!array_key_exists($stat_type['positions'], $tables)) {
		$tables[$stat_type['positions']] = array(
			'headers' => $headers,
			'rows' => array(),
			'totals' => $totals,
		);
	}

	$tables[$stat_type['positions']]['headers'][] = $this->Html->tag('th',
		$this->Html->tag('span', __($stat_type['abbr'], true), array('title' => $stat_type['name'])),
		array('class' => $stat_type['class'])
	);
	$total = array();

	foreach ($team['Person'] as $person) {
		if (!array_key_exists($person['id'], $tables[$stat_type['positions']]['rows'])) {
			$tables[$stat_type['positions']]['rows'][$person['id']] = array(
				$this->element('people/block', compact('person')),
				__($person['gender'], true),
			);
			if ($has_numbers) {
				array_unshift($tables[$stat_type['positions']]['rows'][$person['id']], $person['TeamsPerson']['number']);
			}
		}
		if (array_key_exists('Calculated', $team) &&
			array_key_exists($person['id'], $team['Calculated']) &&
			array_key_exists($stat_type['id'], $team['Calculated'][$person['id']]))
		{
			$value = $team['Calculated'][$person['id']][$stat_type['id']];
			if ($stat_type['type'] == 'season_total') {
				$total[] = $team['Calculated'][$person['id']][$stat_type['id']];
			}
		} else {
			if ($stat_type['type'] == 'season_calc') {
				$value = $na;
			} else {
				$value = 0;
			}
		}
		if (!empty($stat_type['formatter_function'])) {
			$value = $sport_obj->{$stat_type['formatter_function']}($value);
		}
		$tables[$stat_type['positions']]['rows'][$person['id']][] = array($value, array('class' => $stat_type['class']));
	}

	if ($stat_type['type'] == 'season_total') {
		if (empty($stat_type['sum_function'])) {
			$total = array_sum($total);
		} else {
			$total = $sport_obj->{$stat_type['sum_function']}($total);
		}
		if (!empty($stat_type['formatter_function'])) {
			$total = $sport_obj->{$stat_type['formatter_function']}($total);
		}
	} else {
		$total = '';
	}
	$tables[$stat_type['positions']]['totals'][] = $total;
}

foreach ($tables as $positions => $table):
	// Maybe prune out rows that are all zeroes; don't do it for the main stats block for all positions
	if (!empty($positions)) {
		foreach ($table['rows'] as $key => $row) {
			$remove = true;

			// Skip name and gender columns
			array_shift($row);
			array_shift($row);

			while (!empty($row)) {
				$value = array_shift($row);
				if ($value[0] != 0 && $value[0] != $na) {
					$remove = false;
					break;
				}
			}
			if ($remove) {
				unset($table['rows'][$key]);
			}
		}
	}

	if (empty($table['rows'])) {
		continue;
	}
?>
	<table class="list tablesorter">
		<thead>
		<tr>
			<?php echo implode('', $table['headers']); ?>
		</tr>
		</thead>
		<tbody>
			<?php echo $this->Html->tableCells(array_values($table['rows'])); ?>
		</tbody>
		<tfoot>
		<tr>
			<?php echo $this->Html->tableHeaders($table['totals']); ?>
		</tr>
		</tfoot>
	</table>
<?php endforeach; ?>

</div>

<?php
// Make the table sortable
$this->ZuluruHtml->script (array('jquery.tablesorter.min'), array('inline' => false));
$this->ZuluruHtml->css('jquery.tablesorter', null, array('inline' => false));
$this->Js->buffer ("
	jQuery('.tablesorter').tablesorter({sortInitialOrder: 'desc'});
");
?>
