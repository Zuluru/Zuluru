<?php
$this->Html->addCrumb (__('Badges', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Badge.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="badges form">
<?php echo $this->Form->create('Badge', array('url' => Router::normalize($this->here))); ?>
	<fieldset>
 		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Badge', true)); ?></legend>
		<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array(
			'size' => 70,
			'after' => $this->Html->para (null, __('The full name of the badge, to be used as title text on the icon.', true)),
		));
		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		}
		echo $this->Form->input('description', array(
			'cols' => 70,
			'after' => $this->Html->para (null, __('A detailed description of this badge, which should explain how to earn it, what it denotes, and/or what the benefits are.', true)),
		));
		echo $this->Form->input('category', array(
			'options' => Configure::read('options.category'),
			'hide_single' => true,
			'empty' => '---',
			'after' => $this->Html->para (null, __('The category determines the timing for when the badge may be awarded. Don\'t change this unless you know what you are doing.', true)),
		));
		echo $this->Form->input('handler', array(
			'after' => $this->Html->para (null, __('The handler sets which algorithm is used to determine whether a badge should be awarded. Don\'t change this unless you REALLY know what you are doing.', true)),
		));
		if (isset ($add)) {
			echo $this->Form->hidden('active', array('value' => true));
		} else {
			echo $this->Form->input('active');
		}
		echo $this->Form->input('visibility', array(
			'options' => Configure::read('options.visibility'),
			'hide_single' => true,
			'empty' => '---',
			'after' => $this->Html->para (null, __('Select where this badge will be visible.', true)),
		));
		// TODO: Icon upload option?
		echo $this->Form->input('icon', array(
			'after' => $this->Html->para (null, __('Include only the base name of the file; _24.png or _32.png will be appended as required.', true)),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Badges', true)), array('action' => 'index'));?></li>
<?php if (!isset ($add)): ?>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'badge' => $this->Form->value('Badge.id')),
				array('alt' => __('Delete', true), 'title' => __('Delete Badge', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Badge.id')))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('add_32.png',
					array('action' => 'add'),
					array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
<?php endif; ?>
	</ul>
</div>
