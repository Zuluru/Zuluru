<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game', true) . ' ' . $game['Game']['id']);
$this->Html->addCrumb (__('Edit', true));
?>

<div class="games form">
<h2><?php  __('Edit Game'); ?></h2>
<?php echo $this->Form->create('Game', array('url' => $this->here));?>
<?php
	echo $this->Form->input('id');
?>
<dl><?php $i = 0; $class = ' class="altrow"';?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('League', true) . '/' . __('Division', true); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link($game['League']['long_name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $game['League']['id'])); ?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Home Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		$rating = $game['Game']['rating_home'];
		if ($rating === null) {
			$rating = $game['HomeTeam']['rating'];
		}
		echo $this->Html->link($game['HomeTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['HomeTeam']['id'])) .
				' (' . __('rated', true) . ": $rating)";
		?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Away Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		$rating = $game['Game']['rating_away'];
		if ($rating === null) {
			$rating = $game['AwayTeam']['rating'];
		}
		echo $this->Html->link($game['AwayTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['AwayTeam']['id'])) .
				' (' . __('rated', true) . ": $rating)"; ?>

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
		<?php echo $this->Html->link("{$game['GameSlot']['Field']['code']} {$game['GameSlot']['Field']['num']}",
				array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['field_id'])); ?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game Status'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->ZuluruForm->input('status', array(
			'div' => false,
			'label' => false,
			'type' => 'select',
			'options' => Configure::read('options.game_status'),
			'empty' => '---',
		));
		?>

	</dd>
	<?php if ($game['Game']['round']): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Round'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $game['Game']['round']; ?>

	</dd>
	<?php endif; ?>
</dl>

<fieldset class="wide_labels">
	<legend><?php __('Scoring'); ?></legend>
	<?php if (Game::_is_finalized($game)): ?>

	<?php echo $this->element("league/game/{$league_obj->render_element}/score", compact('game')); ?>
	<dl>
		<dt><?php __('Score Approved By'); ?></dt>
		<dd>
			<?php
			if ($game['Game']['approved_by'] < 0) {
				$approved = Configure::read('approved_by');
				__($approved[$game['Game']['approved_by']]);
			} else {
				echo $this->Html->link($game['ApprovedBy']['full_name'],
						array('controller' => 'people', 'action' => 'view', 'person' => $game['ApprovedBy']['id']));
			}
			?>
		</dd>
	</dl>

	<?php else: ?>

	<p><?php __('Score not yet finalized'); ?></p>
	<h3><?php __('Score as entered'); ?></h3>
	<?php
	if (array_key_exists ($game['Game']['home_team'], $game['ScoreEntry'])) {
		$homeScoreEntry = $game['ScoreEntry'][$game['Game']['home_team']];
	}
	if (array_key_exists ($game['Game']['away_team'], $game['ScoreEntry'])) {
		$awayScoreEntry = $game['ScoreEntry'][$game['Game']['away_team']];
	}
	?>
	<table>
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
		<td><?php if (isset ($homeScoreEntry)) echo $homeScoreEntry['defaulted']; ?></td>
		<td><?php if (isset ($awayScoreEntry)) echo $awayScoreEntry['defaulted']; ?></td>
	</tr>
	<tr>
		<td><?php __('Entered By'); ?></td>
		<td><?php if (isset ($homeScoreEntry))
					echo $this->Html->link($homeScoreEntry['Person']['full_name'],
						array('controller' => 'people', 'action' => 'view', 'person' => $homeScoreEntry['person_id']));
		?></td>
		<td><?php if (isset ($awayScoreEntry))
					echo $this->Html->link($awayScoreEntry['Person']['full_name'],
						array('controller' => 'people', 'action' => 'view', 'person' => $awayScoreEntry['person_id']));
		?></td>
	</tr>
	<tr>
		<td><?php __('Entry Time'); ?></td>
		<td><?php
		if (isset ($homeScoreEntry)) {
			echo $this->ZuluruTime->datetime($homeScoreEntry['created']);
		}
		?></td>
		<td><?php
		if (isset ($awayScoreEntry)) {
			echo $this->ZuluruTime->datetime($awayScoreEntry['created']);
		}
		?></td>
	</tr>
	<?php if ($game['League']['numeric_sotg'] || $game['League']['sotg_questions'] != 'none'): ?>
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

	<dl>
		<dt><?php echo $this->Text->truncate ($game['HomeTeam']['name'], 28); ?></dt>
		<dd>
			<?php echo $this->ZuluruForm->input('home_score', array('label' => false, 'size' => 2)); ?>

		</dd>
		<dt><?php echo $this->Text->truncate ($game['AwayTeam']['name'], 28); ?></dt>
		<dd>
			<?php echo $this->ZuluruForm->input('away_score', array('label' => false, 'size' => 2)); ?>

		</dd>
	</dl>
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
if ($game['League']['allstars'] != 'never'):
?>
<fieldset id="AllstarDetails">
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
if ($game['League']['allstars'] != 'never'):
?>
<fieldset id="AllstarDetails">
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
