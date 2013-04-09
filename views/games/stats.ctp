<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game', true) . ' ' . $game['Game']['id']);
if (isset($team_id)) {
	$this->Html->addCrumb ($team['name']);
}
$this->Html->addCrumb (__('Stats', true));
?>

<div class="games stats">
<h2><?php __('Game Stats');?></h2>

<dl><?php $i = 0; $class = ' class="altrow"';?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('League', true) . '/' . __('Division', true); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->element('divisions/block', array('division' => $game['Division'], 'field' => 'full_league_name')); ?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Home Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->element('teams/block', array('team' => $game['HomeTeam']));
		if (array_key_exists ('home_dependency', $game['Game'])) {
			echo " ({$game['Game']['home_dependency']})";
		}
		?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Away Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->element('teams/block', array('team' => $game['AwayTeam']));
		if (array_key_exists ('away_dependency', $game['Game'])) {
			echo " ({$game['Game']['away_dependency']})";
		}
		?>

	</dd>
<?php if (Game::_is_finalized($game)): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Score');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->ZuluruGame->displayScore($game, $game['Division']['League']); ?>

	</dd>
<?php endif; ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Date and Time');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ', ' .
			$this->ZuluruTime->time ($game['GameSlot']['game_start']) . '-' .
			$this->ZuluruTime->time ($game['GameSlot']['display_game_end']);
		?>
	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Location');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'], 'display_field' => 'long_name')); ?>

	</dd>
</dl>
</div>

<div class="related">
<?php
$na = __('N/A', true);

if (isset($team_id)) {
	$teams = array($team);
} else {
	$teams = array($team, $opponent);
}
foreach ($teams as $team):
	if (!isset($team_id)) {
		$header = $this->Html->tag('h3', $team['name']);
	} else {
		$header = '';
	}

	// Sort the stats into groups for display
	$tables = array();
	foreach ($game['Division']['League']['StatType'] as $stat_type) {
		if (!array_key_exists($stat_type['positions'], $tables)) {
			$tables[$stat_type['positions']] = array(
				'headers' => array(
					$this->Html->tag('th', __('Name', true)),
					$this->Html->tag('th', __('Gender', true)),
				),
				'rows' => array(),
				'totals' => array(__('Total', true), ''),
			);
		}

		$tables[$stat_type['positions']]['headers'][] = $this->Html->tag('th',
			$this->Html->tag('span', __($stat_type['abbr'], true), array('title' => $stat_type['name'])),
			array('class' => $stat_type['class'])
		);
		$total = array();

		foreach ($team['Person'] as $person) {
			$person_stats = Set::extract("/Stat[person_id={$person['Person']['id']}][team_id={$team['id']}]", $game);
			if (empty($person_stats)) {
				continue;
			}

			if (!array_key_exists($person['Person']['id'], $tables[$stat_type['positions']]['rows'])) {
				$tables[$stat_type['positions']]['rows'][$person['Person']['id']] = array(
					$this->element('people/block', compact('person')),
					__($person['Person']['gender'], true),
				);
			}
			$value = Set::extract("/Stat[stat_type_id={$stat_type['id']}]/value", $person_stats);
			if (!empty($value)) {
				$value = $value[0];
				$total[] = $value;
			} else {
				$value = 0;
			}
			if (!empty($stat_type['formatter_function'])) {
				$value = $sport_obj->{$stat_type['formatter_function']}($value);
			}
			$tables[$stat_type['positions']]['rows'][$person['Person']['id']][] = array($value, array('class' => $stat_type['class']));
		}

		$person_stats = Set::extract("/Stat[person_id=0][team_id={$team['id']}]", $game);
		if (!empty($person_stats)) {
			if (!array_key_exists(0, $tables[$stat_type['positions']]['rows'])) {
				$tables[$stat_type['positions']]['rows'][0] = array(
					__('Subs', true),
					'',
				);
			}
			$value = Set::extract("/Stat[stat_type_id={$stat_type['id']}]/value", $person_stats);
			if (!empty($value)) {
				$value = $value[0];
				$total[] = $value;
			} else {
				$value = 0;
			}
			$tables[$stat_type['positions']]['rows'][0][] = array($value, array('class' => $stat_type['class']));
		}

		if (empty($stat_type['sum_function'])) {
			$total = array_sum($total);
		} else {
			$total = $sport_obj->{$stat_type['sum_function']}($total);
		}
		if (!empty($stat_type['formatter_function'])) {
			$total = $sport_obj->{$stat_type['formatter_function']}($total);
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
		echo $header;
		$header = '';
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
