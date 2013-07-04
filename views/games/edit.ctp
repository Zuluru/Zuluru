<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game', true) . ' ' . $game['Game']['id']);
$this->Html->addCrumb (__('Edit', true));
?>
<?php
$preliminary = ($game['Game']['home_team'] === null || $game['Game']['away_team'] === null);
?>

<div class="games form">
<h2><?php  __('Edit Game'); ?></h2>
<?php echo $this->Form->create('Game', array('url' => Router::normalize($this->here)));?>
<?php
	echo $this->Form->input('id');
	$division_count = $this->requestAction(array('controller' => 'leagues', 'action' => 'division_count'),
			array('named' => array('league' => $game['Division']['League']['id'])));
?>
<dl><?php $i = 0; $class = ' class="altrow"';?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('League', true); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>><?php echo $this->element('leagues/block', array('league' => $game['Division']['League'])); ?>

	</dd>
	<?php if ($division_count != 1): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Division', true); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>><?php echo $this->element('divisions/block', array('league' => $game['Division']['League'], 'division' => $game['Division'])); ?>

	</dd>
	<?php endif; ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Home Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		if ($game['Game']['home_team'] === null) {
			echo $game['Game']['home_dependency'];
			$game['HomeTeam']['Person'] = array();
		} else {
			echo $this->element('teams/block', array('team' => $game['HomeTeam']));
			if (array_key_exists ('home_dependency', $game['Game'])) {
				echo " ({$game['Game']['home_dependency']})";
			}
			if ($game['Division']['schedule_type'] != 'tournament') {
				echo ' (' . __('currently rated', true) . ": {$game['HomeTeam']['rating']})";
			}
		}
		?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Away Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		if ($game['Game']['away_team'] === null) {
			echo $game['Game']['away_dependency'];
			$game['AwayTeam']['Person'] = array();
		} else {
			echo $this->element('teams/block', array('team' => $game['AwayTeam']));
			if (array_key_exists ('away_dependency', $game['Game'])) {
				echo " ({$game['Game']['away_dependency']})";
			}
			if ($game['Division']['schedule_type'] != 'tournament') {
				echo ' (' . __('currently rated', true) . ": {$game['AwayTeam']['rating']})";
			}
		}
		?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Date and Time');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ', ' .
			$this->ZuluruTime->time ($game['GameSlot']['game_start']) . '-' .
			$this->ZuluruTime->time ($game['GameSlot']['display_game_end']);
		?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Location');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'], 'display_field' => 'long_name')); ?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game Status'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->ZuluruForm->input('status', array(
			'id' => 'Status',
			'div' => false,
			'label' => false,
			'type' => 'select',
			'options' => Configure::read('options.game_status'),
			'empty' => '---',
		));
		?>

	</dd>
	<?php if ($game['Division']['schedule_type'] == 'round_robin' && $game['Game']['round']): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Round'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $game['Game']['round']; ?>

	</dd>
	<?php endif; ?>

	<?php
	$players = array_merge ($game['HomeTeam']['Person'], $game['AwayTeam']['Person']);
	$captains = array();
	foreach ($players as $player) {
		if (in_array($player['TeamsPerson']['role'], Configure::read('privileged_roster_roles')) && $player['TeamsPerson']['status'] == ROSTER_APPROVED) {
			$captains[] = $player;
		}
	}
	if (!empty ($captains)):
	?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Captain Emails'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link(__('Email all captains', true), 'mailto:' . implode (';', Set::extract ('/email_formatted', $captains))); ?>

	</dd>
	<?php endif; ?>
</dl>

<fieldset id="Scores" class="wide_labels">
	<legend><?php __('Scoring'); ?></legend>
	<?php if (Game::_is_finalized($game)): ?>

	<?php echo $this->element("leagues/game/{$league_obj->render_element}/score", compact('game')); ?>
	<dl>
		<dt><?php __('Score Approved By'); ?></dt>
		<dd>
			<?php
			if ($game['Game']['approved_by'] < 0) {
				$approved = Configure::read('approved_by');
				__($approved[$game['Game']['approved_by']]);
			} else {
				echo $this->element('people/block', array('person' => $game['ApprovedBy']));
			}
			?>
		</dd>
	</dl>

	<?php else: ?>

	<p><?php __('Score not yet finalized'); ?></p>
	<?php if (!array_key_exists (null, $game['ScoreEntry'])): ?>
	<h3><?php __('Score as entered'); ?></h3>
	<?php
	if (array_key_exists ($game['Game']['home_team'], $game['ScoreEntry'])) {
		$homeScoreEntry = $game['ScoreEntry'][$game['Game']['home_team']];
	}
	if (array_key_exists ($game['Game']['away_team'], $game['ScoreEntry'])) {
		$awayScoreEntry = $game['ScoreEntry'][$game['Game']['away_team']];
	}
	?>
	<table class="list">
	<tr>
		<th></th>
		<th><?php echo $this->Text->truncate ($game['HomeTeam']['name'], 23) . ' (' . __('home', true) . ')'; ?></th>
		<th><?php echo $this->Text->truncate ($game['AwayTeam']['name'], 23) . ' (' . __('away', true) . ')'; ?></th>
	</tr>
	<tr>
		<td><?php __('Home Score'); ?></td>
		<td><?php if (isset ($homeScoreEntry)) echo $homeScoreEntry['score_for']; else echo __('not entered'); ?></td>
		<td><?php if (isset ($awayScoreEntry)) echo $awayScoreEntry['score_against']; else echo __('not entered'); ?></td>
	</tr>
	<tr>
		<td><?php __('Away Score'); ?></td>
		<td><?php if (isset ($homeScoreEntry)) echo $homeScoreEntry['score_against']; else echo __('not entered'); ?></td>
		<td><?php if (isset ($awayScoreEntry)) echo $awayScoreEntry['score_for']; else echo __('not entered'); ?></td>
	</tr>
	<tr>
		<td><?php __('Defaulted?'); ?></td>
		<td><?php if (isset ($homeScoreEntry)) __($homeScoreEntry['status'] == 'home_default' ? 'us' : ($homeScoreEntry['status'] == 'away_default' ? 'them' : 'no')); ?></td>
		<td><?php if (isset ($awayScoreEntry)) __($awayScoreEntry['status'] == 'away_default' ? 'us' : ($awayScoreEntry['status'] == 'home_default' ? 'them' : 'no')); ?></td>
	</tr>
	<tr>
		<td><?php __('Entered By'); ?></td>
		<td><?php if (isset ($homeScoreEntry))
					echo $this->element('people/block', array('person' => $homeScoreEntry));
		?></td>
		<td><?php if (isset ($awayScoreEntry))
					echo $this->element('people/block', array('person' => $awayScoreEntry));
		?></td>
	</tr>
	<tr>
		<td><?php __('Entry Time'); ?></td>
		<td><?php
		if (isset ($homeScoreEntry)) {
			echo $this->ZuluruTime->datetime($homeScoreEntry['updated']);
		}
		?></td>
		<td><?php
		if (isset ($awayScoreEntry)) {
			echo $this->ZuluruTime->datetime($awayScoreEntry['updated']);
		}
		?></td>
	</tr>
	<?php if (League::hasSpirit($game)): ?>
	<tr>
		<td><?php __('Spirit Assigned'); ?></td>
		<td><?php
		if (array_key_exists ($game['Game']['home_team'], $game['SpiritEntry'])) {
			echo $game['SpiritEntry'][$game['Game']['home_team']]['entered_sotg'];
		}
		?></td>
		<td><?php
		if (array_key_exists ($game['Game']['away_team'], $game['SpiritEntry'])) {
			echo $game['SpiritEntry'][$game['Game']['away_team']]['entered_sotg'];
		}
		?></td>
	</tr>
	<?php endif; ?>
	</table>
	<?php endif; ?>

	<?php endif; ?>

	<?php if (!$preliminary): ?>
	<dl>
		<dt><?php echo $this->Text->truncate ($game['HomeTeam']['name'], 28); ?></dt>
		<dd>
			<?php echo $this->ZuluruForm->input('home_score', array(
					'id' => 'ScoreHome',
					'label' => false,
					'size' => 2,
					'default' => (array_key_exists(null, $game['ScoreEntry']) ? $game['ScoreEntry'][null]['score_for'] : null),
			)); ?>

		</dd>
		<dt><?php echo $this->Text->truncate ($game['AwayTeam']['name'], 28); ?></dt>
		<dd>
			<?php echo $this->ZuluruForm->input('away_score', array(
					'id' => 'ScoreAway',
					'label' => false,
					'size' => 2,
					'default' => (array_key_exists(null, $game['ScoreEntry']) ? $game['ScoreEntry'][null]['score_against'] : null),
			)); ?>

		</dd>
	</dl>
	<?php endif; ?>
</fieldset>

<?php
echo $this->element ('spirit/input', array(
		'team_id' => $game['HomeTeam']['id'],
		'created_team_id' => $game['AwayTeam']['id'],
		'game' => $game,
		'spirit_obj' => $spirit_obj,
));
?>

<?php
if ($game['Division']['allstars'] != 'never'):
?>
<fieldset class="AllstarDetails">
<legend>Allstar Nominations: <?php echo $game['HomeTeam']['name']; ?></legend>

<?php
if (array_key_exists ('Allstar', $this->data)) {
	$allstars = Set::extract ('/Allstar/person_id', $this->data);
} else {
	$allstars = Set::combine ($game['Allstar'], '{n}.Person.id', '{n}.Person.full_name');
}

// Build list of allstar options
$players = Set::combine ($game['HomeTeam']['Person'], '{n}.id', '{n}.full_name');
echo $this->Form->input('Allstar.0.person_id', array(
		'label' => false,
		'options' => $players,
		'multiple' => true,
		'selected' => $allstars,
));
?>

</fieldset>
<?php endif; ?>

<?php
echo $this->element ('spirit/input', array(
		'team_id' => $game['AwayTeam']['id'],
		'created_team_id' => $game['HomeTeam']['id'],
		'game' => $game,
		'spirit_obj' => $spirit_obj,
));
?>
<?php
if ($game['Division']['allstars'] != 'never'):
?>
<fieldset class="AllstarDetails">
<legend>Allstar Nominations: <?php echo $game['AwayTeam']['name']; ?></legend>

<?php
// Build list of allstar options
$players = Set::combine ($game['AwayTeam']['Person'], '{n}.id', '{n}.full_name');
echo $this->Form->input('Allstar.1.person_id', array(
		'label' => false,
		'options' => $players,
		'multiple' => true,
		'selected' => $allstars,
));
?>

</fieldset>
<?php endif; ?>

<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
// Note that the spirit scoring objects must implement the enableSpirit and
// disableSpirit JavaScript functions to handle any non-text input fields.
$win = Configure::read('scoring.default_winning_score');
$lose = Configure::read('scoring.default_losing_score');
echo $this->Html->scriptBlock("
function statusChanged() {
	if (jQuery('#Status').val() == 'home_default') {
		jQuery('#ScoreHome').val($lose);
		jQuery('#ScoreAway').val($win);
		disableCommon();
		enableScores();
	} else if (jQuery('#Status').val() == 'away_default') {
		jQuery('#ScoreHome').val($win);
		jQuery('#ScoreAway').val($lose);
		disableCommon();
		enableScores();
	} else if (jQuery('#Status').val() == 'normal') {
		enableCommon();
		enableScores();
	} else {
		jQuery('#ScoreHome').val(0);
		jQuery('#ScoreAway').val(0);
		disableCommon();
		disableScores();
	}
}

function disableScores() {
	jQuery('#Scores').css('display', 'none');
}

function enableScores() {
	jQuery('#Scores').css('display', '');
}

function disableCommon() {
	jQuery('input:text').attr('disabled', 'disabled');
	jQuery('.AllstarDetails').css('display', 'none');
	if (typeof window.disableSpirit == 'function') {
		disableSpirit();
	}
}

function enableCommon() {
	jQuery('input:text').removeAttr('disabled');
	jQuery('.AllstarDetails').css('display', '');
	if (typeof window.enableSpirit == 'function') {
		enableSpirit();
	}
}
");

// Make sure things are set up correctly, in the case that
// invalid data was detected and the form re-displayed.
// Not sure what might be invalid if a "defaulted" status is
// selected, since pretty much everything else is disabled,
// but maybe something in the future. Cost to do this is
// extremely minimal.
$this->Js->buffer('
jQuery("#Status").change(function(){statusChanged();});
statusChanged();
');

?>
