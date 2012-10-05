<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Preregistrations', true));
$this->Html->addCrumb (__('Add', true));
if (count($affiliates) > 1) {
	$this->Html->addCrumb ($event['Affiliate']['name']);
}
$this->Html->addCrumb ($event['Event']['name']);
?>

<div class="preregistrations form">
	<fieldset>
 		<legend><?php
		__('Add Preregistration');
		echo ': ';
		if (count($affiliates) > 1) {
			echo "{$event['Affiliate']['name']} ";
		}
		echo $event['Event']['name'];
		?></legend>

<?php echo $this->element('people/search_form', array('affiliate_id' => $event['Event']['affiliate_id'])); ?>

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