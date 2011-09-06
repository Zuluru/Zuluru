<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Franchise', true));
$this->Html->addCrumb (__('Transfer Ownership', true));
$this->Html->addCrumb ($franchise['Franchise']['name']);
?>

<div class="franchises add_player">
<h2><?php echo __('Transfer Ownership', true) . ': ' . $franchise['Franchise']['name'];?></h2>

<?php echo $this->element('people/search_form'); ?>

<?php endif; ?>

<?php
echo $this->element('people/search_results', array('extra_url' => array('Make owner' => array('controller' => 'franchises', 'action' => 'transfer', 'franchise' => $franchise['Franchise']['id']))));
?>

<?php if (!$this->params['isAjax']): ?>

</div>
<?php endif; ?>
