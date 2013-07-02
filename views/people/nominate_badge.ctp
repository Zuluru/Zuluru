<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Badges', true));
if ($badge['Badge']['category'] == 'assigned') {
	$this->Html->addCrumb (__('Assign', true));
} else {
	$this->Html->addCrumb (__('Nominate', true));
}
if (count($affiliates) > 1) {
	$this->Html->addCrumb ($badge['Affiliate']['name']);
}
$this->Html->addCrumb ($badge['Badge']['name']);
?>

<div class="badges form">
	<fieldset>
 		<legend><?php
 		if ($badge['Badge']['category'] == 'assigned') {
 			__('Assign a Badge');
 		} else {
 			__('Nominate for a Badge');
 		}
		echo ': ';
		if (count($affiliates) > 1) {
			echo "{$badge['Affiliate']['name']} ";
		}
		echo $badge['Badge']['name'];
 		?></legend>
		<p><?php echo $this->ZuluruHtml->icon($badge['Badge']['icon'] . '_64.png') . ' ' . $badge['Badge']['description']; ?></p>

<?php echo $this->element('people/search_form', array('affiliate_id' => $badge['Badge']['affiliate_id'])); ?>

<?php endif; ?>

<?php
$extra_url = array('controller' => 'people', 'action' => 'nominate_badge_reason', 'badge' => $badge['Badge']['id']);
if ($badge['Badge']['category'] == 'assigned') {
	$extra_url = array('Assign badge' => $extra_url);
} else {
	$extra_url = array('Nominate for badge' => $extra_url);
}
echo $this->element('people/search_results', compact('extra_url'));
?>

<?php if (!$this->params['isAjax']): ?>

	</fieldset>
</div>

<?php endif; ?>