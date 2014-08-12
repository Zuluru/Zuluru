<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Franchise', true));
$this->Html->addCrumb (__('Add an Owner', true));
$this->Html->addCrumb ($franchise['Franchise']['name']);
?>

<div class="franchises add_owner">
<h2><?php echo __('Add an Owner', true) . ': ' . $franchise['Franchise']['name'];?></h2>

<?php echo $this->element('people/search_form', array('affiliate_id' => $franchise['Franchise']['affiliate_id'])); ?>

<div id="SearchResults">
<?php endif; ?>

<?php
echo $this->element('people/search_results', array('extra_url' => array(__('Make owner', true) => array('controller' => 'franchises', 'action' => 'add_owner', 'franchise' => $franchise['Franchise']['id']))));
?>

<?php if (!$this->params['isAjax']): ?>

</div>
</div>
<?php endif; ?>
