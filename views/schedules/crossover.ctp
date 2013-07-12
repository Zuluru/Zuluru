<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Number of Crossovers', true));
?>

<div class="schedules add">
<?php echo $this->element('schedules/exclude'); ?>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'crosscount';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Select number of crossover games</legend>

<?php
$options = array();
for ($i = 1; $i <= floor($teams / 2); ++ $i) {
	$options["crossover_$i"] = $i;
}
echo $this->ZuluruForm->input('Game.pools', array(
		'label' => __('How many crossover games do you want?', true),
		'options' => $options,
		'after' => $this->Html->para(null, 'This is the total number of crossover games for all pools in this division.'),
));
?>

</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>
</div>
