<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Coordinator', true));
?>

<div class="divisions add_coordinator">
<h2><?php echo sprintf(__('Add %s', true), __('Coordinator', true)) . ': ' . $division['Division']['full_league_name'];?></h2>

<?php
if (!empty ($league['Person'])) {
	echo $this->Html->tag ('h3', __('Current Coordinators:', true));
	$coordinators = array();
	foreach ($league['Person'] as $person) {
		$coordinators[] = $this->Html->link ($person['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $person['id']));
	}
	echo $this->Html->nestedList ($coordinators);
}
?>

<?php echo $this->element('people/search_form'); ?>

<?php endif; ?>

<?php echo $this->element('people/search_results', array('extra_url' => array('Add as coordinator' => array('controller' => 'divisions', 'action' => 'add_coordinator', 'division' => $division['Division']['id'])))); ?>

<?php if (!$this->params['isAjax']): ?>

</div>
<?php endif; ?>
