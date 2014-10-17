<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Coordinator', true));
?>

<div class="divisions add_coordinator">
<h2><?php echo sprintf(__('Add %s', true), __('Coordinator', true)) . ': ' . $division['Division']['full_league_name'];?></h2>

<?php
if (!empty ($division['Person'])) {
	echo $this->Html->tag ('h3', __('Current Coordinators:', true));
	$coordinators = array();
	foreach ($division['Person'] as $person) {
		$coordinators[] = $this->element('people/block', compact('person'));
	}
	echo $this->Html->nestedList ($coordinators);
}
?>
<p class="highlight"><?php __('Note that only people whose accounts are set as "volunteer" (or higher) can be made coordinators.'); ?></p>

<?php echo $this->element('people/search_form', array('affiliate_id' => $division['League']['affiliate_id'])); ?>

<div id="SearchResults">
<?php endif; ?>

<?php echo $this->element('people/search_results', array('extra_url' => array(__('Add as coordinator', true) => array('controller' => 'divisions', 'action' => 'add_coordinator', 'division' => $division['Division']['id'], 'return' => false)))); ?>

<?php if (!$this->params['isAjax']): ?>

</div>
</div>
<?php endif; ?>
