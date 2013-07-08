<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Create Pools', true));
?>

<div class="schedules add">

<p><?php
if ($stage > 1):
?>
You have scheduled games for all of the existing team pools, up to stage <?php echo $stage - 1; ?> of the tournament. To proceed, you will need to define new pools.
<?php else: ?>
To schedule a tournament, you must first define how the teams are broken into pools for the first round.
<?php endif; ?> Options below reflect your choices for creating these pools.
<?php echo $this->ZuluruHtml->help(array('action' => 'schedules', 'add', 'tournament', 'pools')); ?>
</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'pools';
?>

<fieldset>
<legend>Create a ...</legend>
<?php
echo $this->Form->input('pools', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $types,
));
?>

<p>Select the number of pools to create. You will then be given options for setting the details of these pools.</p>

</fieldset>

<?php
echo $this->element('hidden', array('fields' => $this->data));
echo $this->Form->end(__('Next step', true));
?>

</div>
