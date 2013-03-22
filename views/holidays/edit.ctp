<?php
$this->Html->addCrumb (__('Holiday', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Holiday.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="holidays form">
<?php echo $this->Form->create('Holiday');?>
	<fieldset>
 		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Holiday', true)); ?></legend>
	<?php
		if (!isset($add)) {
			echo $this->Form->input('id');
		}
		echo $this->Form->input('name');
		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		}
		echo $this->Form->input('date');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
<?php if (!isset ($add)): ?>
		<li><?php echo $this->Html->link(__('List Holidays', true), array('action' => 'index'));?></li>
<?php endif; ?>
		<li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'holiday' => $this->Form->value('Holiday.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Holiday.id'))); ?></li>
	</ul>
</div>

<?php echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));
