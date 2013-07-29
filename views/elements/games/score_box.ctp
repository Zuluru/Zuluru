<div class="score_box" id="score_team_<?php echo $team['id']; ?>">
<table>
	<tr>
		<td class="actions down" rowspan="2"><?php echo $this->Html->link('&ndash;', '#', array('escape' => false)); ?></td>
		<td class="team_name" colspan="2"><?php echo $team['name']; ?></td>
		<td class="actions up" rowspan="2"><?php echo $this->Html->link('+', '#'); ?></td>
	</tr>
	<tr><td class="score" colspan="2"><?php echo $score; ?></td></tr>
	<tr>
		<td class="actions timeout" colspan="2"><?php echo $this->Html->link('Timeout', '#'); ?> (<span class="timeout_count"><?php echo $timeouts; ?></span> taken)</td>
		<td class="actions other" colspan="2"><?php echo $this->Html->link('Other', '#'); ?></td>
	</tr>
</table>
</div>
<?php
$url_up = array('controller' => 'games', 'action' => 'score_up', 'game' => $game['Game']['id'], 'team' => $submitter);
$url_down = array('controller' => 'games', 'action' => 'score_down', 'game' => $game['Game']['id'], 'team' => $submitter);
$url_timeout = array('controller' => 'games', 'action' => 'timeout', 'game' => $game['Game']['id'], 'team' => $submitter);
$url_other = array('controller' => 'games', 'action' => 'play', 'game' => $game['Game']['id'], 'team' => $submitter);
$score_options = Configure::read('sport.score_options');
$other_options = Configure::read('sport.other_options');
$spinner = $this->ZuluruHtml->icon('spinner.gif');

if (($has_stats && ($submitter == $team['id'] || $submitter === null)) || count($score_options) > 1):
?>
<div id="ScoreDetails<?php echo $team['id']; ?>" title="Scoring Play Details" class="form">
<div id="zuluru">
<?php
echo $this->Form->create(false, array(
		'id' => "ScoreForm{$team['id']}",
		'url' => $url_up,
));

echo $this->Form->hidden('team_id', array('value' => $team['id']));
echo $this->Form->hidden('score_from');
echo $this->ZuluruForm->input('play', array(
		'options' => make_options(array_keys($score_options)),
		'empty' => '---',
		'hide_single' => true,
));
echo $this->ZuluruForm->input('created', array(
		'type' => 'datetime',
		'label' => __('Time', true),
));

echo $this->Html->scriptBlock("
function openDialog(id) {
	var d = new Date();
	var h = d.getHours();
	var m = d.getMinutes();
	var mer = 'am';
	if (h >= 12) {
		mer = 'pm';
	}
	if (h == 0) {
		h = 12;
	} else if (h > 12) {
		h = h - 12;
	}
	if (h < 10) {
		h = '0' + h;
	}
	if (m < 10) {
		m = '0' + m;
	}
	jQuery(id + ' #createdHour').val(h);
	jQuery(id + ' #createdMin').val(m);
	jQuery(id + ' #createdMeridian').val(mer);
	jQuery(id).dialog('open');
}
");

if ($has_stats) {
	// Build the roster options
	$roster = array();
	$has_numbers = false;
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
		$roster[$person['id']] = $option;
	}
	asort($roster);

	foreach($game['Division']['League']['StatType'] as $stat) {
		echo $this->Form->input("Stat.{$stat['id']}", array(
				'label' => Inflector::singularize($stat['name']),
				'options' => $roster,
				'empty' => '---',
		));
	}
}

echo $this->Form->end();
?>
</div>
</div>
<?php
echo $this->Html->scriptBlock ("
		jQuery('#ScoreDetails{$team['id']}').dialog({
			autoOpen: false,
			buttons: {
				'Cancel': function() { jQuery(this).dialog('close'); },
				'Submit': function() {
					jQuery(this).dialog('close');
					jQuery('#ScoreForm{$team['id']} #score_from').val(jQuery('#score_team_{$team['id']} td.score').html());
					jQuery('#ScoreForm{$team['id']}').ajaxSubmit({
						type: 'POST',
						target: '#temp_update',
						error: function(message, status, error){
							alert('Error ' + status + ': ' + message.statusText);
						}
					});
					// Reset the form for the next time
					jQuery('#ScoreForm{$team['id']}').each(function(){
						this.reset();
					});
				}
			},
			modal: true,
			resizable: false,
			width: 500
		});
	");
echo $this->Js->get("#score_team_{$team['id']} td.up a")->event('click', "openDialog('#ScoreDetails{$team['id']}');");
else:
	$url_up = Router::url($url_up);
	$play = array_shift(array_keys($score_options));
	echo $this->Js->get("#score_team_{$team['id']} td.up a")->event('click', "
		var score_from = jQuery('#score_team_{$team['id']} td.score').html();
		jQuery('#score_team_{$team['id']} td.score').html('$spinner');
		jQuery.ajax({
			dataType: 'html',
			type: 'POST',
			data: {
				'data[team_id]': {$team['id']},
				'data[score_from]': score_from,
				'data[play]': '$play'
			},
			success: function (data, textStatus) {
				jQuery('#temp_update').html(data);
			},
			url: '$url_up'
		});
	");
endif;

$url_down = Router::url($url_down);
echo $this->Js->get("#score_team_{$team['id']} td.down a")->event('click', "
	var score_from = jQuery('#score_team_{$team['id']} td.score').html();
	jQuery('#score_team_{$team['id']} td.score').html('$spinner');
	jQuery.ajax({
		dataType: 'html',
		type: 'POST',
		data: {
			'data[team_id]': {$team['id']},
			'data[score_from]': score_from
		},
		success: function (data, textStatus) {
			jQuery('#temp_update').html(data);
		},
		url: '$url_down'
	});
");

$url_timeout = Router::url($url_timeout);
echo $this->Js->get("#score_team_{$team['id']} td.timeout a")->event('click', "
	if (confirm('Timeout called?')) {
		jQuery.ajax({
			dataType: 'html',
			type: 'POST',
			data: {
				'data[team_id]': {$team['id']},
				'data[score_from]': jQuery('#score_team_{$team['id']} td.score').html(),
			},
			success: function (data, textStatus) {
				jQuery('#temp_update').html(data);
			},
			url: '$url_timeout'
		});
	}
");

if (count($other_options) > 1):
?>
<div id="OtherDetails<?php echo $team['id']; ?>" title="Other Details" class="form">
<div id="zuluru">
<?php
	echo $this->Form->create(false, array(
		'id' => "OtherForm{$team['id']}",
		'url' => $url_other,
	));

	echo $this->Form->hidden('team_id', array('value' => $team['id']));
	echo $this->Form->hidden('score_from');
	// TODO: Add in non-scoring stats that are being tracked for this division
	echo $this->ZuluruForm->input('play', array(
			'options' => $other_options,
			'empty' => '---',
	));
	echo $this->ZuluruForm->input('created', array(
			'type' => 'datetime',
			'label' => __('Time', true),
	));
	echo $this->Form->end();
?>
</div>
</div>

<?php
	$url_other = Router::url($url_other);
	echo $this->Html->scriptBlock ("
		jQuery('#OtherDetails{$team['id']}').dialog({
			autoOpen: false,
			buttons: {
				'Cancel': function() { jQuery(this).dialog('close'); },
				'Submit': function() {
					jQuery(this).dialog('close');
					jQuery('#OtherForm{$team['id']} #score_from').val(jQuery('#score_team_{$team['id']} td.score').html());
					jQuery('#OtherForm{$team['id']}').ajaxSubmit({
						type: 'POST',
						target: '#temp_update',
						error: function(message, status, error){
							alert('Error ' + status + ': ' + message.statusText);
						}
					});
					// Reset the form for the next time
					jQuery('#OtherForm{$team['id']}').each(function(){
						this.reset();
					});
				}
			},
			modal: true,
			resizable: false,
			width: 500
		});
	");
	echo $this->Js->get("#score_team_{$team['id']} td.other a")->event('click', "openDialog('#OtherDetails{$team['id']}');");
endif;
?>
