<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb ("{$game['HomeTeam']['name']} vs {$game['AwayTeam']['name']}");
$this->Html->addCrumb (__('Edit Box Score', true));

$date = strtotime($game['GameSlot']['game_date']);
$year = date('Y', $date);
$month = date('m', $date);
$day = date('d', $date);

if (!empty($game['Division']['League']['StatType'])) {
	// Build the roster options
	$roster = array();
	$has_numbers = false;
	foreach (array('HomeTeam', 'AwayTeam') as $key) {
		$team = $game[$key];
		$numbers = array_unique(Set::extract('/Person/TeamsPerson/number', $team));
		if (Configure::read('feature.shirt_numbers') && count($numbers) > 1 && $numbers[0] !== null) {
			$has_numbers = true;
		}
		foreach ($team['Person'] as $person) {
			$option = $person['full_name'];
			if ($has_numbers && $person['TeamsPerson']['number'] !== null && $person['TeamsPerson']['number'] !== '') {
				$option = "{$person['TeamsPerson']['number']} $option";
				if ($person['TeamsPerson']['number'] < 10) {
					$option = " $option";
				}
			}
			$roster[$team['id']][$person['id']] = $option;
		}
		asort($roster[$team['id']]);
	}
}
?>

<div class="games form">
<h2><?php  __('Edit Box Score'); ?></h2>
<?php echo $this->Form->create('ScoreDetail', array('url' => Router::normalize($this->here)));?>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Time'); ?></th>
			<th><?php __('Play'); ?></th>
			<?php foreach($game['Division']['League']['StatType'] as $stat): ?>
			<th><?php echo Inflector::singularize($stat['name']); ?></th>
			<?php endforeach; ?>
			<th><?php __('Score'); ?></th>
			<th><?php __('Actions'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$team_names = array(
	$game['HomeTeam']['id'] => $game['HomeTeam']['name'],
	$game['AwayTeam']['id'] => $game['AwayTeam']['name']
);
$scores = array($game['HomeTeam']['id'] => 0, $game['AwayTeam']['id'] => 0);

foreach ($game['ScoreDetail'] as $detail):
	if ($detail['points']) {
		$scores[$detail['team_id']] += $detail['points'];
	}
	echo $this->element('games/edit_boxscore_line', compact('detail', 'scores', 'year', 'month', 'day', 'team_names', 'roster'));
?>
<?php endforeach; ?>
		<tr id="add_row">
			<td><?php
			echo $this->Form->hidden('AddDetail.created.year', array('value' => $year));
			echo $this->Form->hidden('AddDetail.created.month', array('value' => $month));
			echo $this->Form->hidden('AddDetail.created.day', array('value' => $day));
			echo $this->Form->input('AddDetail.team_id', array(
					'type' => 'select',
					'options' => $team_names,
					'empty' => '---',
					'label' => false,
			));
			?></td>
			<td><?php
			echo $this->Form->input('AddDetail.created', array(
					'type' => 'time',
					// This will use the time of the previous detail as the default
					'value' => $detail['created'],
					'empty' => '---',
					'label' => false,
			));
			?></td>
			<td><?php
			echo $this->Form->input('AddDetail.play', array(
					'options' => array_merge(make_options(array_merge(array_keys(Configure::read('sport.score_options')), array('Start', 'Timeout'))), Configure::read('sport.other_options')),
					'empty' => '---',
					'label' => false,
			));
			?></td>
			<?php foreach($game['Division']['League']['StatType'] as $stat): ?>
			<td></td>
			<?php endforeach; ?>
			<td></td>
			<td><?php
			$url_add = array('action' => 'add_score', 'game' => $game['Game']['id']);
			echo $this->ZuluruHtml->iconLink('add_24.png',
					$url_add,
					array('alt' => __('Add Score Detail', true), 'title' => __('Add Score Detail', true)),
					array('id' => 'add_score')
			);
			?></td>
		</tr>
	</tbody>
</table>
<?php
echo $this->Form->end(__('Submit', true));

$url_add = Router::url($url_add);
echo $this->Html->scriptBlock("
	function add_score() {
		var data = {};
		jQuery('#add_row :input').each(function() { data[jQuery(this).attr('name')] = jQuery(this).val(); });
		jQuery.ajax({
			dataType: 'html',
			type: 'POST',
			data: data,
			success: function (data, textStatus) {
				jQuery('#temp_update').html(data);
			},
			url: '$url_add'
		});
	}
");
echo $this->Js->get('#add_score')->event('click', 'add_score();');
?>
</div>
