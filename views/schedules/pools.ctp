<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Create Pools', true));
?>

<div class="schedules add">

<p><?php
if ($stage > 1) {
	printf(__('You have scheduled games for all of the existing team pools, up to stage %d of the tournament. To proceed, you will need to define new pools.', true), $stage - 1);
} else {
	__('To schedule a tournament, you must first define how the teams are broken into pools for the first round.');
}
echo ' ';
__('Options below reflect your choices for creating these pools.');

echo $this->ZuluruHtml->help(array('action' => 'schedules', 'add', 'tournament', 'pools')); ?>
</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'pools';
?>

<fieldset>
<legend><?php __('Create a ...'); ?></legend>
<?php
echo $this->Form->input('pools', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $types,
));
?>

<p><?php __('Select the number of pools to create. You will then be given options for setting the details of these pools.'); ?></p>

</fieldset>

<?php
echo $this->element('hidden', array('fields' => $this->data));
echo $this->Form->end(__('Next step', true));
?>

</div>
