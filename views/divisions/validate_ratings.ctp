<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Validate Ratings', true));
?>

<div class="divisions validate">
<h2><?php  echo __('Validate Ratings', true) . ': ' . $division['Division']['full_league_name'];?></h2>
<?php if ($ratings_obj->per_game_ratings): ?>
<table class="list">
	<tr>
		<th colspan="3">Game</th>
		<th colspan="3">Home Team</th>
		<th colspan="3">Away Team</th>
		<th colspan="2">Transfer</th>
	</tr>
	<tr>
		<th>ID</th>
		<th>Date</th>
		<th>Status</th>
		<th>Name</th>
		<th>Score</th>
		<th>Rating</th>
		<th>Name</th>
		<th>Score</th>
		<th>Rating</th>
		<th>Saved</th>
		<th>Calc</th>
	</tr>
<?php foreach ($division['Game'] as $game): ?>
	<tr>
		<td><?php echo $this->Html->link ($game['Game']['id'], array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id'])); ?></td>
		<td><?php echo $this->ZuluruTime->date ($game['GameSlot']['game_date']); ?></td>
		<td><?php echo Inflector::humanize ($game['Game']['status']); ?></td>
		<td><?php echo $this->element('teams/block', array('team' => $game['HomeTeam'], 'show_shirt' => false)); ?></td>
		<td><?php echo $game['Game']['home_score']; ?></td>
		<td><?php echo $game['Game']['calc_rating_home']; ?></td>
		<td><?php echo $this->element('teams/block', array('team' => $game['AwayTeam'], 'show_shirt' => false)); ?></td>
		<td><?php echo $game['Game']['away_score']; ?></td>
		<td><?php echo $game['Game']['calc_rating_away']; ?></td>
		<?php $class = ($game['Game']['rating_points'] == $game['Game']['calc_rating_points'] ? '' : ' class="warning-message"'); ?>
		<td<?php echo $class; ?>><?php echo $game['Game']['rating_points']; ?></td>
		<td<?php echo $class; ?>><?php echo $game['Game']['calc_rating_points']; ?></td>
	</tr>
<?php endforeach; ?>

</table>
<?php endif; ?>

<table class="list">
	<tr>
		<th>Team</th>
		<th>Saved</th>
		<th>Calc</th>
		<th>Old Rank</th>
		<th>New Rank</th>
	</tr>
<?php foreach ($division['Team'] as $key => $team): ?>
	<tr>
		<td><?php echo $this->element('teams/block', array('team' => $team, 'show_shirt' => false)); ?></td>
		<?php $class = ($team['rating'] == $team['current_rating'] ? '' : ' class="warning-message"'); ?>
		<td<?php echo $class; ?>><?php echo $team['rating']; ?></td>
		<td<?php echo $class; ?>><?php echo $team['current_rating']; ?></td>
		<?php $class = ($team['rank'] == $key + 1 ? '' : ' class="warning-message"'); ?>
		<td<?php echo $class; ?>><?php echo $key + 1; ?></td>
		<td<?php echo $class; ?>><?php echo $team['rank']; ?></td>
	</tr>
<?php endforeach; ?>

</table>
</div>

<?php if (!$correct): ?>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag ('li', $this->Html->link(__('Make Noted Corrections', true), array('action' => 'validate_ratings', 'division' => $division['Division']['id'], 'correct' => true)));
		?>
	</ul>
</div>
<?php endif; ?>
