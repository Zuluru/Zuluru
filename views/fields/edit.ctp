<div class="fields form">
<?php echo $this->Form->create('Field', array('url' => $this->here));?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __('Field', true)); ?></legend>
		<?php
		echo $this->Form->input('id');

		if (empty ($this->data['Field']['parent_id'])) {
			echo $this->ZuluruForm->input('name');
			echo $this->ZuluruForm->input('code');
			echo $this->Form->hidden('parent_id', array('value' => null));
		} else {
			echo $this->ZuluruForm->input('parent_id');
		}
		echo $this->ZuluruForm->input('num', array('label' => 'Number'));
		echo $this->ZuluruForm->input('is_open');
		echo $this->ZuluruForm->input('indoor');
		echo $this->ZuluruForm->input('rating', array(
				'options' => Configure::read('options.field_rating'),
				'empty' => '---',
		));
		if (empty ($this->data['Field']['parent_id'])) {
			echo $this->ZuluruForm->input('location_street', array('label' => 'Address'));
			echo $this->ZuluruForm->input('location_city', array('label' => 'City'));
			echo $this->ZuluruForm->input('location_province', array(
					'label' => 'Province',
					'options' => $provinces,
					'empty' => '---',
			));
			echo $this->ZuluruForm->input('region_id');
			echo $this->ZuluruForm->input('driving_directions', array('cols' => 70));
			echo $this->ZuluruForm->input('parking_details', array('cols' => 70));
			echo $this->ZuluruForm->input('transit_directions', array('cols' => 70));
			echo $this->ZuluruForm->input('biking_directions', array('cols' => 70));
			echo $this->ZuluruForm->input('washrooms', array('cols' => 70));
			echo $this->ZuluruForm->input('public_instructions', array('cols' => 70));
			echo $this->ZuluruForm->input('site_instructions', array('cols' => 70));
			echo $this->ZuluruForm->input('sponsor', array('cols' => 70));
			echo $this->ZuluruForm->input('location_url');
			echo $this->ZuluruForm->input('layout_url');
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
<?php if (!empty ($this->data['Field']['parent_id'])): ?>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Parent', true)), array('action' => 'edit', 'field' => $this->data['Field']['parent_id']));?></li>
<?php endif; ?>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Layout', true)), array('controller' => 'maps', 'action' => 'edit', 'field' => $this->data['Field']['id']));?></li>
	</ul>
</div>
