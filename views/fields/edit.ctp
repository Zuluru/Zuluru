<div class="fields form">
<?php echo $this->Form->create('Field', array('url' => $this->here));?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __('Field', true)); ?></legend>
		<?php
		echo $this->Form->input('id');

		if (empty ($this->data['Field']['parent_id'])) {
			echo $this->Form->input('name', array(
					'after' => ' ' . $this->ZuluruHtml->help(array('action' => 'fields', 'edit', 'name')),
			));
			echo $this->Form->input('code');
			echo $this->Form->hidden('parent_id', array('value' => null));
		} else {
			echo $this->Form->input('parent_id');
		}
		echo $this->Form->input('num', array('label' => 'Number'));
		echo $this->Form->input('is_open');
		echo $this->Form->input('rating', array(
				'options' => Configure::read('options.field_rating'),
				'empty' => '---',
		));
		if (empty ($this->data['Field']['parent_id'])) {
			echo $this->Form->input('location_street', array('label' => 'Address'));
			echo $this->Form->input('location_city', array('label' => 'City'));
			echo $this->Form->input('location_province', array(
					'label' => 'Province',
					'options' => $provinces,
					'empty' => '---',
			));
			echo $this->Form->input('region_id');
			echo $this->Form->input('driving_directions', array('cols' => 70));
			echo $this->Form->input('parking_details', array('cols' => 70));
			echo $this->Form->input('transit_directions', array('cols' => 70));
			echo $this->Form->input('biking_directions', array('cols' => 70));
			echo $this->Form->input('washrooms', array('cols' => 70));
			echo $this->Form->input('public_instructions', array('cols' => 70));
			echo $this->Form->input('site_instructions', array('cols' => 70));
			echo $this->Form->input('sponsor', array('cols' => 70));
			echo $this->Form->input('location_url');
			echo $this->Form->input('layout_url');
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
