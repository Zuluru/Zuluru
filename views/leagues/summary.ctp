<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb (__('Summary', true));
?>

<div class="leagues index">
<h2><?php __('League Summary');?></h2>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo __('Name');?></th>
	<th><?php echo __('Schedule Type');?></th>
	<th><?php echo __('Games Before Repeat');?></th>
	<th><?php echo __('First Game');?></th>
	<th><?php echo __('Last Game');?></th>
	<th><?php echo __('Roster Deadline');?></th>
	<th><?php echo __('Spirit Display');?></th>
	<th><?php echo __('Spirit Questionnaire');?></th>
	<th><?php echo __('Numeric Spirit?');?></th>
	<th><?php echo __('Max Score');?></th>
	<th><?php echo __('Allstars');?></th>
	<th><?php echo __('Coordinator Email');?></th>
	<th><?php echo __('Remind After');?></th>
	<th><?php echo __('Finalize After');?></th>
	<th><?php echo __('Roster Rule');?></th>
</tr>
<?php
$i = 0;
foreach ($leagues as $league):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link($league['League']['long_name'], array('action' => 'edit', 'league' => $league['League']['id'])); ?>
		</td>
		<td><?php __(Inflector::humanize($league['League']['schedule_type'])); ?></td>
		<td><?php echo $league['League']['games_before_repeat']; ?></td>
		<td><?php echo $this->ZuluruTime->date($league['League']['open']); ?></td>
		<td><?php echo $this->ZuluruTime->date($league['League']['close']); ?></td>
		<td><?php echo $this->ZuluruTime->date($league['League']['roster_deadline']); ?></td>
		<td><?php __(Inflector::humanize($league['League']['display_sotg'])); ?></td>
		<td><?php echo $league['League']['sotg_questions']; ?></td>
		<td><?php __($league['League']['numeric_sotg'] ? 'Yes' : 'No'); ?></td>
		<td><?php echo $league['League']['expected_max_score']; ?></td>
		<td><?php __(Inflector::humanize($league['League']['allstars'])); ?></td>
		<td><?php echo $league['League']['coord_list']; ?></td>
		<td><?php echo $league['League']['email_after']; ?></td>
		<td><?php echo $league['League']['finalize_after']; ?></td>
		<td><?php echo $league['League']['roster_rule']; ?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>
