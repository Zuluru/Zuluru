<?php
$this->Html->addCrumb (__('Affiliates', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Affiliate.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="affiliates form">
<?php echo $this->Form->create('Affiliate', array('url' => Router::normalize($this->here))); ?>
	<fieldset>
 		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Affiliate', true)); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->Form->input('name');
		if (!isset ($add)) {
			echo $this->Form->input('active');
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List Affiliates', true), array('action' => 'index'));?></li>
<?php if (!isset ($add)): ?>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'affiliate' => $this->Form->value('Affiliate.id')),
				array('alt' => __('Delete', true), 'title' => __('Delete Affiliate', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Affiliate.id')))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('add_32.png',
					array('action' => 'add'),
					array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
<?php endif; ?>
	</ul>
</div>
