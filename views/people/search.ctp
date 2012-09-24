<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('Search', true));
?>

<div class="people search">
<h2><?php printf(__('Search %s', true), __('Players', true)); ?></h2>

<?php echo $this->element('people/search_form'); ?>

<?php endif; ?>

<?php echo $this->element('people/search_results'); ?>

<?php if (!$this->params['isAjax']): ?>

</div>
<?php endif; ?>
