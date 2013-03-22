<?php
$this->Html->addCrumb (__('Franchises', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Franchise.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="franchises form">
<?php echo $this->Form->create('Franchise', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php __('Franchise Details'); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array(
			'after' => $this->Html->para (null, __('The full name of your franchise.', true)),
		));
		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		}
		if (Configure::read('feature.urls')) {
			echo $this->ZuluruForm->input('website', array(
				'after' => $this->Html->para (null, __('Your franchise\'s website, if you have one.', true)),
			));
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
