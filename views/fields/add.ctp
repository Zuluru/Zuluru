<div class="fields form">
<?php echo $this->Form->create('Field', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__('Add %s', true), __('Field', true)); ?></legend>
		<?php
		echo $this->Html->para('error-message', __('Either select a parent OR enter a new name and code.', true));
		echo $this->Form->input('parent_id', array(
				'empty' => '---',
		));
		echo $this->Form->input('name');
		echo $this->Form->input('code');

		echo $this->Html->para('error-message', __('The following fields are always required.', true));
		echo $this->Form->input('num', array('label' => 'Number'));
		echo $this->Form->input('is_open');
		echo $this->Form->input('indoor');
		echo $this->Form->input('rating', array(
				'options' => Configure::read('options.field_rating'),
				'empty' => '---',
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
