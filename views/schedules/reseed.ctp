<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
if ($type == 'crossover') {
	$this->Html->addCrumb (__('Crossover Details', true));
} else {
	$this->Html->addCrumb (__('Re-seeding Details', true));
}
?>

<div class="schedules add">

<p><?php
if ($type == 'crossover') {
	__('You are defining crossover games. Select which pool positions feed into these games below.');
} else {
	__('You are re-seeding teams into power pools. Select which pool positions feed into these pools below.');
}
echo ' ';
__('For example, selecting the "1st" option in the "Pool B" sub-group of options will place the team with the best record in Pool B in that slot.');
echo ' ';
__('Selecting the "2nd" option in the "1st place teams" sub-group of options will find the team with the second-best record among all of the teams that finished 1st in their pool.');
?></p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'reseed';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend><?php printf(__('%s Details', true), $type == 'crossover' ? __('Crossover', true) : __('Re-seeding', true)); ?></legend>
<table class="list">
	<tr>
		<th><?php __('Pool'); ?></th>
		<th><?php __('Seed'); ?></th>
		<th><?php __('Qualifier'); ?></th>
	</tr>
<?php
foreach ($this->data['Game']['name'] as $key => $name):
	$display_name = $name;
	$teams = $this->data['Game']['count'][$key];
	for ($team = 1; $team <= $teams; ++ $team):
?>
	<tr>
		<td><?php echo $display_name; ?></td>
		<td><?php echo $team; ?></td>
		<td><?php
		echo $this->ZuluruForm->input("Game.$name.$team", array(
				'label' => false,
				'options' => $options,
				'empty' => 'Select:',
		)); ?></td>
	</tr>
<?php
		$display_name = '';
	endfor;
endforeach;
?>
</table>
</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>

</div>