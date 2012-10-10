<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Adjust Ratings', true));
?>

<div class="divisions ratings">
<h2><?php  echo __('Adjust Ratings', true) . ': ' . $division['Division']['full_league_name'];?></h2>

<p>Use the links below to adjust a team's initial ratings for 'better' or for 'worse'. Alternatively, you can enter a new rating into the box beside each team. Changes are <strong>not</strong> saved until you click 'Save Changes' below. Multiple teams can have the same ratings, and likely will at the start of the season.</p>
<p>Note that this adjusts a team's <strong>initial</strong> rating; their <strong>current</strong> rating will be recalculated in the next daily run. Such adjustments are not typically needed mid-season, as ladder systems take care of mis-seedings eventually, but this can speed the process if you belatedly realize that a team was grossly mis-seeded to start the season.</p>
<p>For the rating values, a <strong>HIGHER</strong> numbered rating is <strong>BETTER</strong>, and a <strong>LOWER</strong> numbered rating is <strong>WORSE</strong>.</p>

<?php echo $this->Form->create ('Team', array('url' => Router::normalize($this->here))); ?>

<table class="list">
	<tr>
		<th><?php __('Team Name'); ?></th>
		<th><?php __('Avg. Skill'); ?></th>
		<th><?php __('Current Rating'); ?></th>
		<th><?php __('Initial Rating'); ?></th>
		<th colspan="2"><?php __('New Initial Rating'); ?></th>
	</tr>
<?php foreach ($division['Team'] as $key => $team): ?>
	<tr>
		<td><?php echo $this->element('teams/block', array('team' => $team, 'show_shirt' => false)); ?></td>
		<td><?php
		Team::consolidateRoster ($team);
		echo $team['average_skill'];
		?></td>
		<td><?php echo $team['rating']; ?></td>
		<td><?php echo $team['initial_rating']; ?></td>
		<td><?php
		echo $this->Form->input ("Team.$key.id", array(
				'value' => $team['id'],
		));
		echo $this->Form->input ("Team.$key.initial_rating", array(
				'div' => false,
				'label' => false,
				'size' => 3,
				'value' => $team['initial_rating'],
		));
		?></td>
		<td class="actions"><?php
		echo $this->Html->link ('+', '#', array('onclick' => "return adjust($key, 1);"));
		echo $this->Html->link ('-', '#', array('onclick' => "return adjust($key, -1);"));
		?></td>
	</tr>
<?php endforeach; ?>
</table>

<?php
echo $this->Form->button(__('Save Changes', true));
echo $this->Form->button(__('Reset', true), array('type'=>'reset'));
echo $this->Form->end();
?>

</div>

<?php
echo $this->Html->scriptBlock('
function adjust(id, add)
{
	var element = jQuery("#Team" + id + "InitialRating");
	var val = parseInt (element.val()) + add;
	if (val < 0) val = 0;
	element.val(val);
	return false;
}
');
?>