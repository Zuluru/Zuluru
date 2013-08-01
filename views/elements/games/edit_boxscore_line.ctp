<tr id="detail_<?php echo $detail['id']; ?>">
	<td><?php
	echo $this->Form->hidden("ScoreDetail.{$detail['id']}.ScoreDetail.id", array('value' => $detail['id']));
	echo $this->Form->hidden("ScoreDetail.{$detail['id']}.ScoreDetail.created.year", array('value' => $year));
	echo $this->Form->hidden("ScoreDetail.{$detail['id']}.ScoreDetail.created.month", array('value' => $month));
	echo $this->Form->hidden("ScoreDetail.{$detail['id']}.ScoreDetail.created.day", array('value' => $day));
	echo $this->Form->input("ScoreDetail.{$detail['id']}.ScoreDetail.team_id", array(
			'type' => 'select',
			'options' => $team_names,
			'value' => $detail['team_id'],
			'label' => false,
	));
	?></td>
	<td><?php
	echo $this->Form->input("ScoreDetail.{$detail['id']}.ScoreDetail.created", array(
			'type' => 'time',
			'value' => $detail['created'],
			'label' => false,
	));
	?></td>
	<td><?php
	$stats = false;
	if ($detail['play'] == 'Start') {
		__('Game started');
	} else if ($detail['play'] == 'Timeout') {
		__('Timeout');
	} else if (Configure::read("sport.other_options.{$detail['play']}")) {
		__(Configure::read("sport.other_options.{$detail['play']}"));
	} else {
		__($detail['play']);
		$stats = true;
	}
	?></td>
	<?php foreach($game['Division']['League']['StatType'] as $i => $stat): ?>
	<td><?php
	if ($stats) {
		$person = Set::extract("/ScoreDetailStat[stat_type_id={$stat['id']}]/person_id", $detail);
		echo $this->Form->input("ScoreDetail.{$detail['id']}.ScoreDetailStat.$i.person_id", array(
				'label' => false,
				'options' => $roster[$detail['team_id']],
				'empty' => '---',
				'default' => array_shift($person),
		));
		echo $this->Form->hidden("ScoreDetail.{$detail['id']}.ScoreDetailStat.$i.stat_type_id", array('value' => $stat['id']));
	}
	?></td>
	<?php endforeach; ?>
	<td><?php if (isset($scores)) echo implode(' - ', $scores); ?></td>
	<td><?php
	echo $this->Js->link($this->ZuluruHtml->icon('delete_24.png',
				array('alt' => __('Delete Score Detail', true), 'title' => __('Delete Score Detail', true))),
			array('action' => 'delete_score', 'game' => $game['Game']['id'], 'detail' => $detail['id']),
			array('update' => "#temp_update", 'confirm' => __('Are you sure you want to delete this?', true), 'escape' => false)
	);
	?></td>
</tr>
