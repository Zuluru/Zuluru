<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Franchise', true));
$this->Html->addCrumb (__('Add an Owner', true));
$this->Html->addCrumb ($franchise['Franchise']['name']);
?>

<div class="franchises add_owner">
<h2><?php echo __('Add an Owner', true) . ': ' . $franchise['Franchise']['name'];?></h2>

<?php echo $this->element('people/search_form', array('affiliate_id' => $franchise['Franchise']['affiliate_id'])); ?>

<?php endif; ?>

<?php
echo $this->element('people/search_results', array('extra_url' => array('Make owner' => array('controller' => 'franchises', 'action' => 'add_owner', 'franchise' => $franchise['Franchise']['id']))));
?>

<?php if (!$this->params['isAjax']): ?>

</div>
<?php endif; ?>
