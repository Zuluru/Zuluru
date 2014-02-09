<?php
$this->Html->addCrumb (__('Facilities', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Facility.name'));
	$this->Html->addCrumb (__('Edit', true));
}
$collapse = !empty($this->data['Field']['id']);
?>

<div class="facilities form">
<?php echo $this->Form->create('Facility', array('url' => Router::normalize($this->here)));?>
	<fieldset>
		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Facility', true)); ?></legend>
		<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
			if ($collapse) {
				echo $this->Form->input('Field.id');
			}
		}

		echo $this->ZuluruForm->input('name');
		echo $this->ZuluruForm->input('code');
		echo $this->ZuluruForm->input('is_open');
		echo $this->ZuluruForm->input('location_street', array('label' => 'Address'));
		echo $this->ZuluruForm->input('location_city', array(
				'label' => 'City',
				'default' => Configure::read('organization.city'),
		));
		echo $this->ZuluruForm->input('location_province', array(
				'label' => 'Province',
				'options' => $provinces,
				'default' => Configure::read('organization.province'),
				'empty' => '---',
		));
		echo $this->ZuluruForm->input('region_id', array('hide_single' => true, 'default' => $region));
		echo $this->ZuluruForm->input('driving_directions', array('cols' => 70, 'class' => 'mceSimple'));
		echo $this->ZuluruForm->input('parking_details', array('cols' => 70, 'class' => 'mceSimple'));
		echo $this->ZuluruForm->input('transit_directions', array('cols' => 70, 'class' => 'mceSimple'));
		echo $this->ZuluruForm->input('biking_directions', array('cols' => 70, 'class' => 'mceSimple'));
		echo $this->ZuluruForm->input('washrooms', array('cols' => 70, 'class' => 'mceSimple'));
		echo $this->ZuluruForm->input('public_instructions', array('cols' => 70, 'class' => 'mceSimple'));
		echo $this->ZuluruForm->input('site_instructions', array('cols' => 70, 'class' => 'mceSimple'));
		echo $this->ZuluruForm->input('sponsor', array('cols' => 70, 'class' => 'mceAdvanced'));

		if ($collapse || isset ($add)):
		?>
		<fieldset>
			<legend><?php __(Configure::read('ui.field_cap')); ?></legend>
		<?php
			echo $this->ZuluruForm->input('Field.num', array('label' => 'Number'));
			echo $this->ZuluruForm->input('Field.is_open');
			echo $this->ZuluruForm->input('Field.indoor');
			echo $this->ZuluruForm->input('Field.surface', array(
					'options' => Configure::read('options.surface'),
					'empty' => '---',
					'hide_single' => true,
			));
			echo $this->ZuluruForm->input('Field.rating', array(
					'options' => Configure::read('options.field_rating'),
					'empty' => '---',
					'hide_single' => true,
			));
			echo $this->ZuluruForm->input('Field.layout_url');
		?>
		</fieldset>
		<?php
		endif;
		?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php if (!isset ($add)): ?>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('Add %s', true), __(Configure::read('ui.field_cap'), true)), array('controller' => 'fields', 'action' => 'add', 'facility' => $this->Form->value('Facility.id')));?></li>
	</ul>
</div>
<?php endif; ?>
<?php
if (Configure::read('feature.tiny_mce')) {
	$this->TinyMce->editor('simple');
	$this->TinyMce->editor('advanced');
}
?>
