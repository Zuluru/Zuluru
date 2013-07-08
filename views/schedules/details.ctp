<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Set Pool Details', true));
?>

<div class="schedules add">

<p>You are scheduling a tournament with multiple pools. Please provide the details for each pool.</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'details';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Pool Details</legend>
<table class="list">
	<tr>
		<th></th>
		<th><?php __('Name'); ?></th>
<?php if ($type != 'snake'): ?>
		<th><?php __('Number of Teams'); ?></th>
<?php endif; ?>
	</tr>
<?php for ($i = 1; $i <= $pools; ++ $i): ?>
	<tr>
		<td><?php echo $i; ?>.</td>
		<td><?php
		echo $this->ZuluruForm->input("Game.name.$i", array(
				'label' => false,
				'maxlength' => 2,
				'size' => 5,
				'default' => $name ++,
		)); ?></td>
<?php if ($type != 'snake'): ?>
		<td><?php
		echo $this->ZuluruForm->input("Game.count.$i", array(
				'label' => false,
				'type' => 'number',
				'maxlength' => 2,
				'size' => 5,
				'default' => $sizes[$i],
		)); ?></td>
<?php endif; ?>
	</tr>
<?php endfor; ?>
</table>
</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>

</div>