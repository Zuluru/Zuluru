<?php
$this->Html->addCrumb (__(Configure::read('ui.fields_cap'), true));
$this->Html->addCrumb ($this->Form->value('Facility.name'));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="fields form">
<?php echo $this->Form->create('Field', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __(Configure::read('ui.field_cap'), true)); ?></legend>
		<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->hidden('Facility.name');
		echo $this->ZuluruForm->hidden('Field.facility_id');

		echo $this->ZuluruForm->input('num', array('label' => 'Number'));
		echo $this->ZuluruForm->input('is_open');
		echo $this->ZuluruForm->input('indoor');
		echo $this->ZuluruForm->input('surface', array(
				'options' => Configure::read('options.surface'),
				'empty' => '---',
		));
		echo $this->ZuluruForm->input('rating', array(
				'options' => Configure::read('options.field_rating'),
				'empty' => '---',
		));
		echo $this->ZuluruForm->input('layout_url');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php if (!isset ($add)): ?>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Facility', true)), array('controller' => 'facilities', 'action' => 'edit', 'facility' => $this->Form->value('Field.facility_id'), 'return' => true));?></li>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Layout', true)), array('controller' => 'maps', 'action' => 'edit', 'field' => $this->Form->value('Field.id'), 'return' => true));?></li>
	</ul>
</div>
<?php endif; ?>
