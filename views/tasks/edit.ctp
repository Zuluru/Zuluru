<?php
$this->Html->addCrumb (__('Tasks', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Task.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="tasks form">
<?php echo $this->Form->create('Task', array('url' => Router::normalize($this->here))); ?>
	<fieldset>
		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Task', true)); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->Form->input('name', array(
				'size' => 100,
		));
		echo $this->Form->input('category_id');
		echo $this->Form->input('description', array(
				'after' => $this->Html->para (null, __('This description will be visible to people assigned to the task.', true)),
		));
		echo $this->Form->input('notes', array(
				'after' => $this->Html->para (null, __('Notes will only be visible administrators.', true)),
		));
		echo $this->Form->input('auto_approve', array(
				'after' => $this->Html->para (null, __('If checked, assignments will not require separate admin approval.', true)),
		));
		echo $this->Form->input('allow_signup', array(
				'after' => $this->Html->para (null, __('If checked, volunteers will be able to sign themselves up; if not, an admin will have to assign people.', true)),
		));
		echo $this->Form->input('person_id', array(
				'label' => __('Reporting To', true),
				'empty' => '---',
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List Tasks', true), array('action' => 'index'));?></li>
<?php if (!isset ($add)): ?>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'task' => $this->Form->value('Task.id')),
				array('alt' => __('Delete', true), 'title' => __('Delete Task', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Task.id')))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('add_32.png',
				array('action' => 'add'),
				array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
<?php endif;?>
	</ul>
</div>
