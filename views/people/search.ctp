<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('Search', true));
?>

<div class="people search">
<h2><?php printf(__('Search %s', true), __('Players', true)); ?></h2>

<?php echo $this->element('people/search_form'); ?>

<?php if ($is_admin || $is_manager): ?>
<p>Alternately, you may <?php echo $this->Html->link(__('enter a rule and find people who match', true), array('action' => 'rule_search')); ?>.
<?php endif; ?>

<?php endif; ?>

<?php echo $this->element('people/search_results'); ?>

<?php if (!$this->params['isAjax']): ?>

</div>
<?php endif; ?>
