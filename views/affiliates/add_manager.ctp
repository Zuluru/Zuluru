<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Affiliates', true));
$this->Html->addCrumb ($affiliate['Affiliate']['name']);
$this->Html->addCrumb (__('Add Manager', true));
?>

<div class="affiliates add_manager">
<h2><?php echo sprintf(__('Add %s', true), __('Manager', true)) . ': ' . $affiliate['Affiliate']['name'];?></h2>

<?php
if (!empty ($affiliate['Person'])) {
	echo $this->Html->tag ('h3', __('Current Managers:', true));
	$managers = array();
	foreach ($affiliate['Person'] as $person) {
		$managers[] = $this->element('people/block', compact('person'));
	}
	echo $this->Html->nestedList ($managers);
}
?>

<?php echo $this->element('people/search_form', array('affiliate_id' => $affiliate['Affiliate']['id'])); ?>

<?php endif; ?>

<?php echo $this->element('people/search_results', array('extra_url' => array('Add as manager' => array('controller' => 'affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate['Affiliate']['id'])))); ?>

<?php if (!$this->params['isAjax']): ?>

</div>
<?php endif; ?>
