<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Preregistrations', true));
$this->Html->addCrumb (__('Add', true));
$this->Html->addCrumb ($event['Event']['name']);
?>

<div class="preregistrations form">
	<fieldset>
 		<legend><?php __('Add Preregistration'); ?></legend>

<?php echo $this->element('people/search_form'); ?>

<?php endif; ?>

<?php
echo $this->element('people/search_results', array('extra_url' => array('Add preregistration' => array('controller' => 'preregistrations', 'action' => 'add', 'event' => $event['Event']['id']))));
?>

<?php if (!$this->params['isAjax']): ?>

	</fieldset>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List Preregistrations', true), array('action' => 'index'));?></li>
	</ul>
</div>

<?php endif; ?>