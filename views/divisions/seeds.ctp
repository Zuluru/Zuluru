<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Adjust Initial Seeds', true));
?>

<div class="divisions seeds">
<h2><?php  echo __('Adjust Initial Seeds', true) . ': ' . $division['Division']['full_league_name'];?></h2>

<p>Use the links below to adjust a team's initial seeds for 'better' or for 'worse'. Alternatively, you can enter a new seed into the box beside each team. Changes are <strong>not</strong> saved until you click 'Save Changes' below. Multiple teams cannot have the same seed.</p>
<p>Note that this adjusts a team's <strong>initial</strong> seed; their <strong>current</strong> seed will be unaffected, as it is determined by game results. Initial seeds are typically unimportant for standard leagues, but are required for non-playoff tournaments.</p>
<p>For the seed values, a <strong>LOWER</strong> numbered seed is <strong>BETTER</strong>, and a <strong>HIGHER</strong> numbered seed is <strong>WORSE</strong>.</p>

<?php echo $this->Form->create ('Team', array('url' => Router::normalize($this->here))); ?>

<table class="list">
	<tr>
		<th><?php __('Team Name'); ?></th>
		<th><?php __('Avg. Skill'); ?></th>
		<th><?php __('Initial Seed'); ?></th>
		<th colspan="2"><?php __('New Initial Seed'); ?></th>
	</tr>
<?php foreach ($division['Team'] as $key => $team): ?>
	<tr>
		<td><?php echo $this->element('teams/block', array('team' => $team, 'show_shirt' => false)); ?></td>
		<td><?php
		Team::consolidateRoster ($team);
		echo $team['average_skill'];
		?></td>
		<td><?php echo $team['initial_seed']; ?></td>
		<td><?php
		echo $this->Form->input ("Team.$key.id", array(
				'value' => $team['id'],
		));
		echo $this->Form->input ("Team.$key.initial_seed", array(
				'div' => false,
				'label' => false,
				'tabindex' => 1,
				'size' => 3,
				'default' => $team['initial_seed'],
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
	var element = jQuery("#Team" + id + "InitialSeed");
	var val = parseInt (element.val()) + add;
	if (val < 0) val = 0;
	element.val(val);
	return false;
}
');
?>